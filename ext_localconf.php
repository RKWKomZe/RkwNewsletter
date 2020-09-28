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

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwNewsletter\\Controller\\NewsletterCommandController';

        //=================================================================
        // Register TCA evaluation to be available in 'eval' of TCA
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation'] = '';

        //=================================================================
        // Register Hooks
        //=================================================================
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$extKey] = 'RKW\\RkwNewsletter\\Hooks\\DeleteCollectionPageOfReleaseHook';
        // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] = 'RKW\\RkwNewsletter\\Hooks\\ReorderApprovalsOfChangedReleaseStatusHook';

        //=================================================================
        // Register SignalSlots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . 'RkwNewsletter',
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendOptInRequest'
        );
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_USER  . 'RkwNewsletter',
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendOptInRequest'
        );
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_USER_REGISTER_GRANT . 'RkwNewsletter',
            'RKW\\RkwNewsletter\\Controller\\SubscriptionController',
            'saveSubscription'
        );

        // Slots for backend module release approval, test send and final send
        $signalSlotDispatcher->connect(
            'RKW\\RkwNewsletter\\Helper\\Approval',
            \RKW\RkwNewsletter\Helper\Approval::SIGNAL_FOR_SENDING_MAIL_APPROVAL,
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendMailAdminApproval'
        );
        $signalSlotDispatcher->connect(
            'RKW\\RkwNewsletter\\Helper\\Approval',
            \RKW\RkwNewsletter\Helper\Approval::SIGNAL_FOR_SENDING_MAIL_APPROVAL_AUTOMATIC,
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendMailAdminApprovalAutomatic'
        );
        $signalSlotDispatcher->connect(
            'RKW\\RkwNewsletter\\Helper\\Release',
            \RKW\RkwNewsletter\Helper\Release::SIGNAL_FOR_SENDING_MAIL_RELEASE,
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendMailAdminRelease'
        );
        $signalSlotDispatcher->connect(
            'RKW\\RkwNewsletter\\Controller\\ReleaseController',
            \RKW\RkwNewsletter\Controller\ReleaseController::SIGNAL_FOR_SENDING_MAIL_TEST,
            'RKW\\RkwNewsletter\\Service\\RkwMailService',
            'sendTestMail'
        );


        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwNewsletter']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::INFO => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/var/logs/tx_rkwnewsletter.log'
                )
            ),
        );
    },
    $_EXTKEY
);


