<?php

namespace RKW\RkwNewsletter\Helper;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Approval
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Approval implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Signal Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * ApprovalRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\ApprovalRepository
     * @inject
     */
    protected $approvalRepository;


    /**
     * PersistenceManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


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
     * doAutomaticApprovalsByTime
     * Sets automatic approvals by time passed
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function doAutomaticApprovalsByTime()
    {
        // get settings
        $settings = $this->getSettings();

        // Set automatic approvals by time passed
        if (
            (intval($settings['automaticApprovalStage1']))
            && (intval($settings['automaticApprovalStage2']))
        ) {

            $automaticApprovalList = $this->approvalRepository->findAllForAutomaticApproveByTime(intval($settings['automaticApprovalStage1']), intval($settings['automaticApprovalStage2']));
            if (count($automaticApprovalList)) {

                /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
                foreach ($automaticApprovalList as $approval) {

                    $approvalAdmins = [];
                    $stage = 1;
                    if ($approval->getAllowedTstampStage1() < 1) {
                        $approval->setAllowedTstampStage1(time());
                        $approvalAdmins = $approval->getTopic()->getApprovalStage1();

                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Automatic approval by time: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));

                    } else {
                        if ($approval->getAllowedTstampStage2() < 1) {
                            $approval->setAllowedTstampStage2(time());
                            $approvalAdmins = $approval->getTopic()->getApprovalStage2();
                            $stage = 2;

                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Automatic approval by time: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));
                        }
                    }

                    if (count($approvalAdmins) > 0) {

                        // Signal for e.g. E-Mails
                        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_FOR_SENDING_MAIL_APPROVAL_AUTOMATIC, array($approvalAdmins, $approval, $stage));
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Sending info mail for automatic approval: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));
                    }

                    $this->approvalRepository->update($approval);
                }

                // persist to keep the following database-requests up-to-date
                $this->persistenceManager->persistAll();

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No automatic approval by time needed.'));
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Automatic approval by time not configured.'));
        }
    }

    /**
     * doAutomaticApprovalsByAdminsMissing
     * Sets automatic approvals by time passed
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function doAutomaticApprovalsByAdminsMissing()
    {

        $automaticApprovalList = $this->approvalRepository->findAllForAutomaticApproveByAdminsMissing();
        if (count($automaticApprovalList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
            foreach ($automaticApprovalList as $approval) {

                $stage = 1;
                if ($approval->getAllowedTstampStage1() < 1) {
                    $approval->setAllowedTstampStage1(time());
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Automatic approval by missing admins: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));

                } else {
                    if ($approval->getAllowedTstampStage2() < 1) {
                        $approval->setAllowedTstampStage2(time());
                        $stage = 2;
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Automatic approval by missing admins: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));
                    }
                }

                $this->approvalRepository->update($approval);
            }

            // persist to keep the following database-requests up-to-date
            $this->persistenceManager->persistAll();

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No automatic approval by missing admins needed.'));
        }
    }


    /**
     * sendInfoAndReminderMailsForApprovals
     * Send info-mails or reminder-mails for outstanding approvals
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function sendInfoAndReminderMailsForApprovals()
    {

        // get settings
        $settings = $this->getSettings();

        $openApprovalList = $this->approvalRepository->findAllOpenApprovalsByTime(intval($settings['reminderApprovalStage1']), intval($settings['reminderApprovalStage2']));
        if (count($openApprovalList)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
            foreach ($openApprovalList as $approval) {

                // Case 1: infomail at stage 1
                $approvalAdmins = [];
                $stage = 1;
                $isReminder = false;
                if (
                    ($approval->getAllowedTstampStage1() < 1)
                    && ($approval->getSentInfoTstampStage1() < 1)
                ) {

                    if (count($approval->getTopic()->getApprovalStage1()) > 0) {
                        $approval->setSentInfoTstampStage1(time());
                        $approvalAdmins = $approval->getTopic()->getApprovalStage1();
                    }

                    // Case 2: reminder at stage 1
                } else {
                    if (
                        ($approval->getAllowedTstampStage1() < 1)
                        && ($approval->getSentInfoTstampStage1() > 0)
                        && ($approval->getSentReminderTstampStage1() < 1)
                    ) {

                        $isReminder = true;
                        if (count($approval->getTopic()->getApprovalStage1()) > 0) {
                            $approval->setSentReminderTstampStage1(time());
                            $approvalAdmins = $approval->getTopic()->getApprovalStage1();
                        }

                        // Case 3: infomail at stage 2
                    } else {
                        if (
                            ($approval->getAllowedTstampStage1() > 0)
                            && ($approval->getAllowedTstampStage2() < 1)
                            && ($approval->getSentInfoTstampStage1() > 0)
                            && ($approval->getSentInfoTstampStage2() < 1)
                        ) {

                            $stage = 2;
                            if (count($approval->getTopic()->getApprovalStage2()) > 0) {
                                $approval->setSentInfoTstampStage2(time());
                                $approvalAdmins = $approval->getTopic()->getApprovalStage2();
                            }

                            // Case 4: reminder at stage 2
                        } else {
                            if (
                                ($approval->getAllowedTstampStage1() > 0)
                                && ($approval->getAllowedTstampStage2() < 1)
                                && ($approval->getSentInfoTstampStage1() > 0)
                                && ($approval->getSentInfoTstampStage2() > 0)
                                && ($approval->getSentReminderTstampStage2() < 1)
                                && (count($approval->getTopic()->getApprovalStage2()) > 0)
                            ) {

                                $stage = 2;
                                $isReminder = true;
                                if (count($approval->getTopic()->getApprovalStage2()) > 0) {
                                    $approval->setSentReminderTstampStage2(time());
                                    $approvalAdmins = $approval->getTopic()->getApprovalStage2();
                                }
                            }
                        }
                    }
                }

                if (count($approvalAdmins) > 0) {

                    // Signal for e.g. E-Mails
                    $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_FOR_SENDING_MAIL_APPROVAL, array($approvalAdmins, $approval, $stage, $isReminder));
                    if ($isReminder) {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Sending reminder mail for approval: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));
                    } else {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Sending info mail for approval: stage %s, topic "%s", issue id=%s, newsletter-configuration id=%s.', $stage, $approval->getTopic()->getName(), $approval->getIssue()->getUid(), $approval->getIssue()->getNewsletter()->getUid()));
                    }

                    // Update
                    $this->approvalRepository->update($approval);
                }

            }

            // Persist to keep the following database-requests up-to-date
            $this->persistenceManager->persistAll();

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No info mails/reminder mails for approval needed.'));
        }
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwnewsletter', $which);
        //===
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }

}
