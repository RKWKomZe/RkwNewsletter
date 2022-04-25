<?php

namespace RKW\RkwNewsletter\Controller;
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

use RKW\RkwNewsletter\Manager\ApprovalManager;
use RKW\RkwNewsletter\Manager\IssueManager;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * ReleaseCommandController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class NewsletterCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{

    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;


    /**
     * MailProcessor
     *
     * @var \RKW\RkwNewsletter\Mailing\MailProcessor
     * @inject
     */
    protected $mailProcessor;
    
    
    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * create issues for all newsletters
     *
     * @param int $tolerance Tolerance for creating next issue according to last time an issue was built (in seconds)
     * @param int $dayOfMonth Day of month the newsletter are planned to be sent
     * @return void
     */
    public function processIssuesCommand(int $tolerance = 0, int $dayOfMonth = 15): void
    {
        try {
            
// $dayOfMonth = 1;
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            
            /** @var \RKW\RkwNewsletter\Manager\IssueManager $issueManager */
            $issueManager = $objectManager->get(IssueManager::class);
            $issueManager->buildAllIssues($tolerance, $dayOfMonth);

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR, 
                sprintf(
                    'An unexpected error occurred while trying to process issues: %s', 
                    $e->getMessage()
                )
            );
        }
    }


    /**
     * processes confirmations of approvals and issues
     *
     * @return void
     */
    public function processConfirmationsCommand()
    {

        try {
            
            $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
$settings['reminderApprovalStage1'] = 600;
$settings['reminderApprovalStage2'] = 600;
$settings['automaticApprovalStage1'] = 1200;
$settings['automaticApprovalStage2'] = 1200;
$settings['reminderApprovalStage3'] = 600;

            /** @var  \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \RKW\RkwNewsletter\Manager\ApprovalManager $approvalManager */
            $approvalManager = $objectManager->get(ApprovalManager::class);
            $approvalManager->processAllConfirmations(
                intval($settings['reminderApprovalStage1']),
                intval($settings['reminderApprovalStage2']),
                intval($settings['automaticApprovalStage1']),
                intval($settings['automaticApprovalStage2'])
            );

            /** @var \RKW\RkwNewsletter\Manager\IssueManager $issueManager */
            $issueManager = $objectManager->get(IssueManager::class);
            $issueManager->processAllConfirmations($settings['reminderApprovalStage3']);

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR, 
                sprintf(
                    'An unexpected error occurred while trying to process confirmations: %s', 
                    $e->getMessage()
                )
            );
        }
    }

    
    /**
     * builds final newsletter-emails and prepares them for sending
     *
     * @param int $newsletterLimit
     * @param int $recipientsPerNewsletterLimit
     * @param float $sleep how many seconds the script should sleep after each run
     * @return void
     */
    public function buildNewslettersCommand(
        int $newsletterLimit = 5, 
        int $recipientsPerNewsletterLimit = 10, 
        float $sleep = 60.0
    ) {

        try {

            $issues = $this->issueRepository->findAllToSend($newsletterLimit);
            if (count($issues)) {

                /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
                foreach ($issues as $issue) {

                    $this->mailProcessor->setIssue($issue);
                    $this->mailProcessor->sendMails($recipientsPerNewsletterLimit);

                    usleep(intval($sleep * 1000000));
                }

            } else {
                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'No issues to sent.'
                    )
                );
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'An unexpected error occurred while trying to process confirmations: %s',
                    $e->getMessage()
                )
            );
        }
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return \RKW\RkwBasics\Utility\GeneralUtility::getTyposcriptConfiguration('Rkwnewsletter', $which);
    }
    
}