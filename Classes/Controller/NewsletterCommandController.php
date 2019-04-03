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

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
     * newsletterRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\NewsletterRepository
     * @inject
     */
    protected $newsletterRepository;

    /**
     * issueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;


    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;


    /**
     * pagesRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository;


    /**
     * queueMailRepository
     *
     * @var \RKW\RkwMailer\Domain\Repository\QueueMailRepository
     * @inject
     */
    protected $queueMailRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * function processIssuesCommand
     * create issues
     *
     * @param int $tolerance Tolance for creating next issue according to last time an issue was built (in seconds)
     * @param int $dayOfMonth Day of month the newsletter are planned to be sent
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException*
     */
    public function processIssuesCommand($tolerance = 0, $dayOfMonth = 15)
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

            // Create new issues for newsletter-configurations if needed
            /** @var \RKW\RkwNewsletter\Helper\Issue $issue */
            $issue = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Issue');
            $issue->buildIssue($tolerance, $dayOfMonth);

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An unexpected error occurred while trying to process issues: %s', $e->getMessage()));
        }
    }


    /**
     * function processApprovalsCommand
     * check for approvals
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function processApprovalsCommand()
    {

        try {

            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

            /** @var \RKW\RkwNewsletter\Helper\Approval $approval */
            $approval = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Approval');
            $approval->doAutomaticApprovalsByTime();
            $approval->doAutomaticApprovalsByAdminsMissing();
            $approval->sendInfoAndReminderMailsForApprovals();

            /** @var \RKW\RkwNewsletter\Helper\Release $release */
            $release = $objectManager->get('RKW\\RkwNewsletter\\Helper\\Release');
            $release->sendInfoAndReminderMailsForReleases();

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An unexpected error occurred while trying to process approvals: %s', $e->getMessage()));

        }

    }


    /**
     * function buildNewsletter
     * builds final newsletter-emails and prepares them for sending
     *
     * @param int $newsletterLimit
     * @param int $recipientsPerNewsletterLimit
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function buildNewslettersCommand($newsletterLimit = 5, $recipientsPerNewsletterLimit = 10)
    {

        try {

            $issues = $this->issueRepository->findAllToSend($newsletterLimit);
            $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
            $settingsDefault = $this->getSettings();

            // if there is only one topic-page included, show all contents
            $itemsPerTopic = ($settings['settings']['maxItemsPerTopic'] ? intval($settings['settings']['maxItemsPerTopic']) : 5);

            if (count($issues)) {

                /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
                foreach ($issues as $issue) {

                    // get newsletter
                    $newsletter = $issue->getNewsletter();

                    // 1. initialize mail service and check for an existing queue mail or set one
                    /** @var \RKW\RkwMailer\Service\MailService $mailService */
                    $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

                    // if queueMail exists...
                    if ($queueMail = $issue->getQueueMail()) {

                        // load queueMail into MailService
                        $mailService->setQueueMail($issue->getQueueMail());

                        // get special pages
                        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $specialPages */
                        $specialPages = $this->pagesRepository->findAllByIssueAndSpecialTopic($issue, true);

                        //  Go through recipients
                        if (count($issue->getRecipients())) {

                            /** @var \RKW\RkwNewsletter\Domain\Model\FrontendUser $frontendUser */
                            $cnt = 0;
                            foreach ($issue->getRecipients()->toArray() as $frontendUser) {

                                // check if hash-value exists - may be relevant for imports via MySQL
                                if (!$frontendUser->getTxRkwnewsletterHash()) {
                                    $hash = sha1($frontendUser->getEmail() . rand());
                                    $frontendUser->setTxRkwnewsletterHash($hash);
                                    $this->frontendUserRepository->update($frontendUser);

                                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Set new newsletter-hash for frontendUser with uid=%s.', $frontendUser->getUid()));
                                }

                                // get all pages of user by his subscriptions
                                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $issuePages */
                                $pages = $this->pagesRepository->findAllByIssueAndSubscriptionAndSpecialTopic($issue, $frontendUser->getTxRkwnewsletterSubscription());

                                /** @var \RKW\RkwNewsletter\Domain\Model\Pages $page */
                                $pagesOrderArray = array();
                                foreach ($pages->toArray() as $page) {
                                    $pagesOrderArray[] = $page->getUid();
                                }

                                // add to final list if there are some pages!
                                if (count($pages) > 0) {

                                    // override itemsPerTopic
                                    if (count($pages) == 1) {
                                        $itemsPerTopic = 9999;
                                    }

                                    // add it to final list
                                    $mailService->setTo(
                                        $frontendUser,
                                        array(
                                            'marker'  => array(
                                                'issue'            => $issue,
                                                'pages'            => $pages,
                                                'specialPages'     => $specialPages,
                                                 // 'includeEditorials'    => (((count($pages->toArray()) + count($specialPages->toArray())) > 1) ? false : true),
                                                'includeEditorials' => ((count($pages->toArray()) > 1) ? false : true),
                                                'pagesOrder'       => $pagesOrderArray,
                                                'maxItemsPerTopic' => $itemsPerTopic,
                                                'pageTypeMore'     => $settings['settings']['webViewPageNum'],
                                                'webView'          => false,
                                                'settings'         => $settingsDefault,
                                                'hash'             => $frontendUser->getTxRkwnewsletterHash(),
                                            ),
                                            'subject' => $issue->getTitle(),
                                        ),
                                        true
                                    );

                                    //  remove recipient from temporary list!
                                    $issue->removeRecipients($frontendUser);
                                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Prepared newsletter-mails for recipient with uid=%s for issue with uid=%s of newsletter-configuration with id=%s.', $frontendUser->getUid(), $issue->getUid(), $newsletter->getUid()));
                                }

                                $cnt++;
                                if ($cnt >= $recipientsPerNewsletterLimit) {
                                    break;
                                    //===
                                }
                            }

                            // if sending has already been started, this only adds the new users
                            $mailService->send();
                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Prepared newsletter-mails for %s recipients for issue with id=%s of newsletter-configuration with id=%s.', $cnt, $issue->getUid(), $newsletter->getUid()));

                        } else {

                            // newsletter has been completely submitted to rkw_mailer
                            $issue->setSentTstamp(time());
                            $issue->setStatus(4);

                            // remove pipeline flag
                            $queueMail = $mailService->getQueueMail();
                            $queueMail->setPipeline(false);
                            $this->queueMailRepository->update($queueMail);

                            // set timestamp
                            $newsletter->setLastSentTstamp($issue->getSentTstamp());
                            $this->newsletterRepository->update($newsletter);

                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Finished preparing newsletter-mail for issue with id=%s of newsletter-configuration with id=%s.', $issue->getUid(), $newsletter->getUid()));
                        }

                        // if queueMail does not exist we have to build it first!
                    } else {

                        // set properties for queueMail
                        /** @var \RKW\RkwMailer\Domain\Model\QueueMail $queueMail */
                        $queueMail = $mailService->getQueueMail();
                        $queueMail->setType(1);
                        $queueMail->setSettingsPid($newsletter->getSettingsPage()->getUid());
                        $queueMail->setSubject($issue->getTitle());

                        // use as pipeline, so sending may start before all recipients are set
                        $queueMail->setPipeline(true);
                        $queueMail->setCategory('rkwNewsletter');

                        $queueMail->addLayoutPaths($settings['view']['newsletter']['layoutRootPaths']);
                        $queueMail->addTemplatePaths($settings['view']['newsletter']['templateRootPaths']);
                        $queueMail->addPartialPaths($settings['view']['newsletter']['partialRootPaths']);

                        $queueMail->setPlaintextTemplate($issue->getNewsletter()->getTemplate());
                        $queueMail->setHtmlTemplate($issue->getNewsletter()->getTemplate());

                        $this->queueMailRepository->update($queueMail);
                        $issue->setQueueMail($queueMail);

                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Set queueMail properties and markers for issue with id=%s of newsletter-configuration with id=%s.', $issue->getUid(), $newsletter->getUid()));
                    }

                    $this->issueRepository->update($issue);
                }

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No issues to sent.'));
            }


        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('An unexpected error occurred while trying to process newsletters: %s', $e->getMessage()));
        }
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
     * Debugs a SQL query from a QueryResult
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $queryResult
     * @param boolean $explainOutput
     * @return void
     */
    public function debugQuery(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $queryResult, $explainOutput = false)
    {
        $GLOBALS['TYPO3_DB']->debugOutput = 2;
        if ($explainOutput) {
            $GLOBALS['TYPO3_DB']->explainOutput = true;
        }
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
        $queryResult->toArray();
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);

        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = false;
        $GLOBALS['TYPO3_DB']->explainOutput = false;
        $GLOBALS['TYPO3_DB']->debugOutput = false;
    }
}