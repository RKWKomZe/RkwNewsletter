<?php

namespace RKW\RkwNewsletter\Service;

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
 * RkwMailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Send mail to admin for proofing a release (respectively the topics of it)
     *
     * @param array $admins
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @param int $stage
     * @param bool $isReminder
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminApproval($admins, \RKW\RkwNewsletter\Domain\Model\Approval $approval, $stage = 1, $isReminder = false)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof \RKW\RkwNewsletter\Domain\Model\BackendUser)
                    && ($admin->getEmail())
                ) {

                    // send new user an email with token
                    $mailService->setTo($admin, array(
                        'marker'  => array(
                            'approval'    => $approval,
                            'backendUser' => $admin,
                            'stage'       => $stage,
                            'isReminder'  => $isReminder,
                        ),
                        'subject' => \RKW\RkwMailer\Helper\FrontendLocalization::translate(
                            ($isReminder ? 'rkwMailService.subject.adminApprovalReminder' : 'rkwMailService.subject.adminApproval'),
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    ($isReminder ? 'rkwMailService.subject.adminApprovalReminder' : 'rkwMailService.subject.adminApproval'),
                    'rkw_newsletter',
                    null,
                    'de'
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/AdminApproval');
            $mailService->getQueueMail()->setHtmlTemplate('Email/AdminApproval');

            if (count($mailService->getTo())) {
                $mailService->send();
            }
        }
    }


    /**
     * Send mail to admin for proofing a release (respectively the topics of it)
     *
     * @param array $admins
     * @param \RKW\RkwNewsletter\Domain\Model\Approval $approval
     * @param int $stage
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminApprovalAutomatic($admins, \RKW\RkwNewsletter\Domain\Model\Approval $approval, $stage = 1)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof \RKW\RkwNewsletter\Domain\Model\BackendUser)
                    && ($admin->getEmail())
                ) {

                    // send new user an email with token
                    $mailService->setTo($admin, array(
                        'marker'  => array(
                            'approval'    => $approval,
                            'backendUser' => $admin,
                            'stage'       => $stage,
                        ),
                        'subject' => \RKW\RkwMailer\Helper\FrontendLocalization::translate(
                            'rkwMailService.subject.adminApprovalAutomatic',
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'rkwMailService.subject.adminApprovalAutomatic',
                    'rkw_newsletter',
                    null,
                    'de'
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/AdminApprovalAutomatic');
            $mailService->getQueueMail()->setHtmlTemplate('Email/AdminApprovalAutomatic');

            if (count($mailService->getTo())) {
                $mailService->send();
            }
        }
    }


    /**
     * Send mail to admin for final permission of checked issue
     *
     * @param array $admins
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param bool $isReminder
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminRelease($admins, \RKW\RkwNewsletter\Domain\Model\Issue $issue, $isReminder = false)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof \RKW\RkwNewsletter\Domain\Model\BackendUser)
                    && ($admin->getEmail())
                ) {

                    // send new user an email with token
                    $mailService->setTo($admin, array(
                        'marker'  => array(
                            'issue'       => $issue,
                            'backendUser' => $admin,
                            'isReminder'  => $isReminder,
                        ),
                        'subject' => \RKW\RkwMailer\Helper\FrontendLocalization::translate(
                            ($isReminder ? 'rkwMailService.subject.adminReleaseReminder' : 'rkwMailService.subject.adminRelease'),
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    ($isReminder ? 'rkwMailService.subject.adminReleaseReminder' : 'rkwMailService.subject.adminRelease'),
                    'rkw_newsletter',
                    null,
                    'de'
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/AdminRelease');
            $mailService->getQueueMail()->setHtmlTemplate('Email/AdminRelease');

            if (count($mailService->getTo())) {
                $mailService->send();
            }
        }
    }


    /**
     * send opt-in
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendOptInRequest(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\Registration $registration = null)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();
        if ($settings['view']['templateRootPaths']) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'frontendUser' => $frontendUser,
                    'registration' => $registration,
                    'pageUid'      => intval($GLOBALS['TSFE']->id),
                    'loginPid'     => intval($settingsDefault['loginPid']),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                \RKW\RkwMailer\Helper\FrontendLocalization::translate(
                    'rkwMailService.subject.optInRequest',
                    'rkw_newsletter',
                    array(),
                    $frontendUser->getTxRkwregistrationLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/OptInRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/OptInRequest');

            if (count($mailService->getTo())) {
                $mailService->send();
            }
        }
    }


    /**
     * Send newsletter test mail to one backend user
     *
     * @param \RKW\RkwNewsletter\Domain\Model\BackendUser $admin
     * @param string $email
     * @param \RKW\RkwNewsletter\Domain\Model\Issue $issue
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $pages
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $specialPages
     * @param string $title
     * @return void
     * @throws \Exception
     * @throws \RKW\RkwMailer\Service\MailException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendTestMail(\RKW\RkwNewsletter\Domain\Model\BackendUser $admin, $email, \RKW\RkwNewsletter\Domain\Model\Issue $issue, \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $pages, \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $specialPages, $title = null)
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if (
            ($settings['view']['templateRootPaths'])
            && ($issue->getNewsletter()->getTemplate())
        ) {

            /** @var \RKW\RkwMailer\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwMailer\\Service\\MailService');

            // if there is only one topic-page included, show all contents
            $itemsPerTopic = ($settings['settings']['maxItemsPerTopic'] ? intval($settings['settings']['maxItemsPerTopic']) : 5);
            if (count($pages) == 1) {
                $itemsPerTopic = 999;
            }

            // send new user an email with token
            $mailService->setTo(
                array(
                    'salutation'   => 1,
                    'firstName'    => 'Sabine',
                    'lastName'     => 'Mustermann',
                    'title'        => 'Prof. Dr. Dr.',
                    'email'        => $email,
                    'languageCode' => $admin->getLang(),
                ),
                array(
                    'marker'  => array(
                        'issue'            => $issue,
                        'pages'            => $pages,
                        'specialPages'     => $specialPages,
                        'admin'            => $admin,
                        'pageTypeMore'     => $settings['settings']['webViewPageNum'],
                        'maxItemsPerTopic' => $itemsPerTopic,
                        'settings'         => $settingsDefault,
                    ),
                    'subject' => \RKW\RkwMailer\Helper\FrontendLocalization::translate(
                        'rkwMailService.subject.testMail',
                        'rkw_newsletter',
                        array('subject' => ($title ? $title : $issue->getTitle())),
                        $admin->getLang()
                    ),
                ));

            $mailService->getQueueMail()->setSettingsPid($issue->getNewsletter()->getSettingsPage()->getUid());
            $mailService->getQueueMail()->setSubject(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'rkwMailService.subject.testMail',
                    'rkw_newsletter',
                    array('subject' => ($title ? $title : $issue->getTitle()))
                )
            );
            $mailService->getQueueMail()->addLayoutPaths($settings['view']['newsletter']['layoutRootPaths']);
            $mailService->getQueueMail()->addTemplatePaths($settings['view']['newsletter']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['newsletter']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate($issue->getNewsletter()->getTemplate());
            $mailService->getQueueMail()->setHtmlTemplate($issue->getNewsletter()->getTemplate());

            if (count($mailService->getTo())) {
                $mailService->send();
            }
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
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        //===
    }


}
