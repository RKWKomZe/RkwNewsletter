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
 * Release
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwNewsletter
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Release implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Signal Slot Dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * IssueRepository
     *
     * @var \RKW\RkwNewsletter\Domain\Repository\IssueRepository
     * @inject
     */
    protected $issueRepository;


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
    const SIGNAL_FOR_SENDING_MAIL_RELEASE = 'sendMailRelease';


    /**
     * sendInfoAndReminderMailsForReleases
     * Send info-mails or reminder-mails for outstanding approvals
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendInfoAndReminderMailsForReleases()
    {
        // get settings
        $settings = $this->getSettings();

        $issuesToRelease = $this->issueRepository->findAllToReleaseByTime(intval($settings['reminderApprovalStage3']));
        if (count($issuesToRelease)) {

            /** @var \RKW\RkwNewsletter\Domain\Model\Issue $issue */
            foreach ($issuesToRelease as $issue) {

                // check if all approvals are set until stage 2!
                /** @var \RKW\RkwNewsletter\Domain\Model\Approval $approval */
                foreach ($issue->getApprovals() as $approval) {
                    if ($approval->getAllowedTstampStage2() < 1) {
                        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Skipping info mail for release because of missing approvals: issue id=%s, newsletter-configuration id=%s.', $issue->getUid(), $issue->getNewsletter()->getUid()));
                        continue 2;
                        //===
                    }
                }

                // set status
                $issue->setStatus(2);

                // Case 1: Infomail
                $isReminder = false;
                if ($issue->getInfoTstamp() < 1) {
                    $issue->setInfoTstamp(time());

                // Case 2: Reminder
                } else {

                    $issue->setReminderTstamp(time());
                    $isReminder = true;
                }

                // Signal for e.g. E-Mails
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_FOR_SENDING_MAIL_RELEASE, array($issue->getNewsletter()->getApproval(), $issue, $isReminder));
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Sending info mail for release: issue id=%s, newsletter-configuration id=%s.', $issue->getUid(), $issue->getNewsletter()->getUid()));

                // Update
                $this->issueRepository->update($issue);
            }

            // Persist to keep the following database-requests up-to-date
            $this->persistenceManager->persistAll();

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('No info mails/reminder mails for release needed.'));
        }
    }

    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
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
