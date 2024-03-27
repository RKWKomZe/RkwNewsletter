<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugins
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Subscription',
            array(
                'Subscription' => 'new, create, edit, update, message, optIn, createSubscription, finalSaveSubscription, unsubscribe',
            ),

            // non-cacheable actions
            array(
                'Subscription' => 'new, create, edit, update, message, optIn, createSubscription, finalSaveSubscription, unsubscribe',
            )
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Webview',
            array(
                'WebView' => 'show',
            ),
            // non-cacheable actions
            array(
                'WebView' => 'show',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Archive',
            array(
                'WebView' => 'list, show',
            ),
            // non-cacheable actions
            array(
                'WebView' => 'list, show',
            )
        );

        //=================================================================
        // Register TCA evaluation to be available in 'eval' of TCA
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation'] = '';

        //=================================================================
        // Register Hooks
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] = 'RKW\\RkwNewsletter\\Hooks\\ResetNewsletterConfigOnPageCopyHook';

        //=================================================================
        // Register SignalSlots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            Madj2k\FeRegister\Registration\AbstractRegistration::class,
            \Madj2k\FeRegister\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN . 'RkwNewsletter',
            \RKW\RkwNewsletter\Service\RkwMailService::class,
            'sendOptInRequest'
        );

        $signalSlotDispatcher->connect(
            Madj2k\FeRegister\Registration\AbstractRegistration::class,
            \Madj2k\FeRegister\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED . 'RkwNewsletter',
            \RKW\RkwNewsletter\Controller\SubscriptionController::class,
            'saveSubscription'
        );

        // Slots for backend module release approval, test send and final send
        $signalSlotDispatcher->connect(
            \RKW\RkwNewsletter\Manager\ApprovalManager::class,
            \RKW\RkwNewsletter\Manager\ApprovalManager::SIGNAL_FOR_SENDING_MAIL_APPROVAL,
            \RKW\RkwNewsletter\Service\RkwMailService::class,
            'sendMailAdminApproval'
        );
        $signalSlotDispatcher->connect(
            \RKW\RkwNewsletter\Manager\ApprovalManager::class,
            \RKW\RkwNewsletter\Manager\ApprovalManager::SIGNAL_FOR_SENDING_MAIL_APPROVAL_AUTOMATIC,
            \RKW\RkwNewsletter\Service\RkwMailService::class,
            'sendMailAdminApprovalAutomatic'
        );
        $signalSlotDispatcher->connect(
            \RKW\RkwNewsletter\Manager\IssueManager::class,
            \RKW\RkwNewsletter\Manager\IssueManager::SIGNAL_FOR_SENDING_MAIL_RELEASE,
            \RKW\RkwNewsletter\Service\RkwMailService::class,
            'sendMailAdminRelease'
        );

        //=================================================================
        // Add XClasses for extending existing classes
        // ATTENTION: deactivated due to faulty mapping in TYPO3 9.5
        //=================================================================
        /*
        // for TYPO3 12+
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Madj2k\CoreExtended\Domain\Model\Pages::class] = [
            'className' => \RKW\RkwNewsletter\Domain\Model\Pages::class
        ];

        // for TYPO3 9.5 - 11.5 only, not required for TYPO3 12
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
            ->registerImplementation(
                \Madj2k\CoreExtended\Domain\Model\Pages::class,
                \RKW\RkwNewsletter\Domain\Model\Pages::class
            );

        // for TYPO3 12+
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Madj2k\FeRegister\Domain\Model\FrontendUser::class] = [
            'className' => \RKW\RkwNewsletter\Domain\Model\FrontendUser::class
        ];

        // for TYPO3 9.5 - 11.5 only, not required for TYPO3 12
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
            ->registerImplementation(
                \Madj2k\FeRegister\Domain\Model\FrontendUser::class,
                \RKW\RkwNewsletter\Domain\Model\FrontendUser::class
            );


        // for TYPO3 12+
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Madj2k\FeRegister\Domain\Model\BackendUser::class] = [
            'className' => \RKW\RkwNewsletter\Domain\Model\BackendUser::class
        ];

        // for TYPO3 9.5 - 11.5 only, not required for TYPO3 12
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
            ->registerImplementation(
                \Madj2k\FeRegister\Domain\Model\BackendUser::class,
                \RKW\RkwNewsletter\Domain\Model\BackendUser::class
            );
        */
        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwNewsletter']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath()  . '/log/tx_rkwnewsletter.log'
                )
            ),
        );
    },
    'rkw_newsletter'
);


