<?php
namespace RKW\RkwNewsletter\Service;

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

use Madj2k\Postmaster\Service\MailService;
use RKW\RkwNewsletter\Domain\Model\Approval;
use RKW\RkwNewsletter\Domain\Model\BackendUser;
use RKW\RkwNewsletter\Domain\Model\Issue;
use Madj2k\FeRegister\Domain\Model\FrontendUser;
use Madj2k\FeRegister\Domain\Model\OptIn;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use Madj2k\Postmaster\Utility\FrontendLocalizationUtility;

/**
 * RkwMailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwNewsletter
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
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminApproval(
        array $admins,
        Approval $approval,
        int $stage = 1,
        bool$isReminder = false
    ): void {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Service\MailService $mailService */
            $mailService = GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof BackendUser)
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
                        'subject' => FrontendLocalizationUtility::translate(
                            ($isReminder ? 'rkwMailService.subject.adminApprovalReminder' : 'rkwMailService.subject.adminApproval'),
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
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

            $mailService->send();
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
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminApprovalAutomatic(
        array $admins,
        Approval $approval,
        int $stage = 1
    ): void {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Service\MailService $mailService */
            $mailService = GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof BackendUser)
                    && ($admin->getEmail())
                ) {

                    // send new user an email with token
                    $mailService->setTo($admin, array(
                        'marker'  => array(
                            'approval'    => $approval,
                            'backendUser' => $admin,
                            'stage'       => $stage,
                        ),
                        'subject' => FrontendLocalizationUtility::translate(
                            'rkwMailService.subject.adminApprovalAutomatic',
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
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

            $mailService->send();
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
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendMailAdminRelease(
        array $admins,
        Issue $issue,
        bool $isReminder = false
    ): void {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Service\MailService $mailService */
            $mailService = GeneralUtility::makeInstance(MailService::class);

            /** @var \RKW\RkwNewsletter\Domain\Model\BackendUser $admin */
            foreach ($admins as $admin) {

                if (
                    ($admin instanceof BackendUser)
                    && ($admin->getEmail())
                ) {

                    // send new user an email with token
                    $mailService->setTo($admin, array(
                        'marker'  => array(
                            'issue'       => $issue,
                            'backendUser' => $admin,
                            'isReminder'  => $isReminder,
                        ),
                        'subject' => FrontendLocalizationUtility::translate(
                            ($isReminder ? 'rkwMailService.subject.adminReleaseReminder' : 'rkwMailService.subject.adminRelease'),
                            'rkw_newsletter',
                            null,
                            $admin->getLang()
                        ),
                    ));
                }
            }

            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
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

            $mailService->send();

        }
    }


    /**
     * send opt-in
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \Madj2k\FeRegister\Domain\Model\OptIn|null $optIn
     * @return void
     * @throws \Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendOptInRequest(
        FrontendUser $frontendUser,
        OptIn $optIn = null
    ): void {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();
        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Service\MailService $mailService */
            $mailService = GeneralUtility::makeInstance(MailService::class);

            // send new user an email with token
            $mailService->setTo($frontendUser, array(
                'marker' => array(
                    'frontendUser' => $frontendUser,
                    'optIn'        => $optIn,
                    'pageUid'      => intval($GLOBALS['TSFE']->id),
                    'loginPid'     => intval($settingsDefault['loginPid']),
                ),
            ));

            $mailService->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
                    'rkwMailService.subject.optInRequest',
                    'rkw_newsletter',
                    array(),
                    $frontendUser->getTxFeregisterLanguageKey()
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/OptInRequest');
            $mailService->getQueueMail()->setHtmlTemplate('Email/OptInRequest');

            $mailService->send();
        }
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
        return \Madj2k\CoreExtended\Utility\GeneralUtility::getTypoScriptConfiguration('Rkwnewsletter', $which);
    }

}
