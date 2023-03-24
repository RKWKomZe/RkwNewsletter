<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
        function (string $extKey) {

        //=================================================================
        // Add TypoScript
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extKey,
            'Configuration/TypoScript',
            'RKW Newsletter'
        );

        //=================================================================
        // Add TsConfig
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
            $extKey,
            'Configuration/TsConfig/setup.typoscript',
            'RKW Newsletter'
        );
    },
    'rkw_newsletter'
);
