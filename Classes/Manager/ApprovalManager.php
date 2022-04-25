<?php
namespace RKW\RkwNewsletter\Manager;

use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\Issue;
use RKW\RkwNewsletter\Domain\Model\Pages;
use RKW\RkwNewsletter\Domain\Model\Topic;
use RKW\RkwNewsletter\Status\ApprovalStatus;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * ApprovalManager
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ApprovalManager implements \TYPO3\CMS\Core\SingletonInterface
{
    
   
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;

    
    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository;



    /**
     * @var \RKW\RkwNewsletter\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository;
    
    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * Signal Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * @var \RKW\RkwNewsletter\Permissions\PagePermissions
     * @inject
     */
    protected $pagePermissions;
    
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_FOR_SENDING_MAIL_APPROVAL = 'sendMailApproval';

    
    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_FOR_SENDING_MAIL_APPROVAL_AUTOMATIC = 'sendMailApprovalAutomatic';


    /**
     * Creates an approval for the given topic
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Topic $topic
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \RKW\RkwNewsletter\Domain\Model\Pages $page
     * @return \RKW\RkwNewsletter\Domain\Model\Approval
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createApproval (Topic $topic, Issue $issue, Pages $page): Approval
    {

        /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
        $approval = GeneralUtility::makeInstance(Approval::class);
        $approval->setTopic($topic);
        $approval->setPage($page);

        $this->approvalRepository->add($approval);
        $issue->addApprovals($approval);
        $this->issueRepository->update($issue);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Created approval for topic with id=%s of issue with id=%s.',
                $topic->getUid(),
                $issue->getUid()
            )
        );
        
        return $approval;
    }

    /**
     * Increases the level of the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval Approval $approval
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function increaseLevel (Approval $approval): bool
    {

        $stage = ApprovalStatus::getStage($approval);
        
        if (ApprovalStatus::increaseLevel($approval)) {
            $this->approvalRepository->update($approval);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Increased level for approval id=%s in approval-stage %s.',
                    $approval->getUid(),
                    $stage
                )
            );

            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Did not increase level for approval id=%s.',
                $approval->getUid()
            )
        );

        return false;
    }


    /**
     * Increases the current stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function increaseStage (Approval $approval): bool
    {

        $stage = ApprovalStatus::getStage($approval);
        $backendUser = null;

        // check if triggered via backend
        if (
            ($GLOBALS['BE_USER'] instanceof BackendUserAuthentication)
            && ($backendUserId = intval($GLOBALS['BE_USER']->user['uid']))
        ) {
            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $backendUser */
            $backendUser = $this->backendUserRepository->findByUid(intval($backendUserId));
        }

        if (ApprovalStatus::increaseStage($approval, $backendUser)) {
            $this->approvalRepository->update($approval);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Increased stage for approval id=%s in approval-stage %s.',
                    $approval->getUid(),
                    $stage
                )
            );

            return true;
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Did not increase stage for approval id=%s.',
                $approval->getUid()
            )
        );

        return false;
    }



    /**
     * Get the email-recipients for the approval based in the current stage
     * 
     * @param Approval $approval
     * @return array
     */
    public function getMailRecipients (Approval $approval): array 
    {

        $mailRecipients = [];
        $stage = ApprovalStatus::getStage($approval);

        if ($stage == ApprovalStatus::STAGE1) {
            if (count($approval->getTopic()->getApprovalStage1()) > 0) {
                
                /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $beUser */
                foreach ($approval->getTopic()->getApprovalStage1()->toArray() as $beUser) {
                    if (GeneralUtility::validEmail($beUser->getEmail())) {
                        $mailRecipients[] = $beUser;
                    }
                }
            }
        }
        
        if ($stage == ApprovalStatus::STAGE2) {
            if (count($approval->getTopic()->getApprovalStage2()) > 0) {
                
                /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $beUser */
                foreach ($approval->getTopic()->getApprovalStage2()->toArray() as $beUser) {
                    if (GeneralUtility::validEmail($beUser->getEmail())) {
                        $mailRecipients[] = $beUser;
                    }
                }            
            }
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Found %s recipients for approval id=%s in approval-stage %s.',
                count($mailRecipients),
                $approval->getUid(),
                $stage
            )
        );
        
        return $mailRecipients;
    }


    

    /**
     * Send info-mails or reminder-mails for outstanding confirmations
     * 
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return int
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function sendMails(Approval $approval): int
    {

        $stage = ApprovalStatus::getStage($approval);
        $level = ApprovalStatus::getLevel($approval);
        $isReminder = ($level == ApprovalStatus::LEVEL2);

        // get recipients - but only if stage and level match AND if valid recipients are found
        if (
            (count($recipients = $this->getMailRecipients($approval)))
            && ($stage != ApprovalStatus::STAGE_DONE)
        ){
            
            if ($level != ApprovalStatus::LEVEL_DONE) {

                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    self::SIGNAL_FOR_SENDING_MAIL_APPROVAL,
                    [$recipients, $approval, $stage, $isReminder]
                );

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Sending email for approval with id=%s in approval-stage %s and level %s.',
                        $approval->getUid(),
                        $stage,
                        $level
                    )
                );

                return 1;
                
            } else {

                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    self::SIGNAL_FOR_SENDING_MAIL_APPROVAL_AUTOMATIC,
                    [$recipients, $approval, $stage, $isReminder]
                );

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Sending email for automatic confirmation for approval with id=%s in approval-stage %s and level %s.',
                        $approval->getUid(),
                        $stage,
                        $level
                    )
                );

                return 2;
            }                        
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Approval with id=%s in approval-stage %s has no mail-recipients.',
                $approval->getUid(),
                $stage
            )
        );
        return 0;
    }


    /**
     * Send email for given approval or directly increase its stage
     *
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @return bool
     * @throws \RKW\RkwNewsletter\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function processConfirmation(Approval $approval): bool
    {

        // if the value 1 is returned, we simply increase the level
        if ($this->sendMails($approval) == 1) {

            $this->increaseLevel($approval);
            $this->pagePermissions->setPermissions($approval->getPage());
            
            return true;
        }

        // if we reach this point, the approval has timed-out ($this->sendMails returns 2)
        // OR the approval has no recipients ($this->sendMails returns 0)
        // in that case we increase the stage
        $this->increaseStage($approval);
        $this->pagePermissions->setPermissions($approval->getPage());
        
        return false;
    }


    /**
     * Processes all approvals
     *
     * @param int $toleranceLevel2
     * @param int $toleranceLevel1
     * @param int $toleranceStage1
     * @param int $toleranceStage2
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processAllConfirmations (
        int $toleranceLevel1,
        int $toleranceLevel2,
        int $toleranceStage1 = 0,
        int $toleranceStage2 = 0
    ): int {

        $approvalList = $this->approvalRepository->findAllForConfirmationByTolerance(
            $toleranceLevel1,
            $toleranceLevel2,
            $toleranceStage1,
            $toleranceStage2
        );

        if (count($approvalList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
            foreach ($approvalList as $approval) {
                $this->processConfirmation($approval);
            }
        }

        $this->getLogger()->log(
            LogLevel::DEBUG,
            sprintf(
                'Processed %s approvals.',
                count($approvalList)
            )
        );
        
        return count($approvalList);
    }
    

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }




}