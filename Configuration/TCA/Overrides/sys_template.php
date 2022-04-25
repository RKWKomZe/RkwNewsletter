<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        //=================================================================
        // Add TypoScript
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'rkw_newsletter',
            'Configuration/TypoScript',
            'RKW Newsletter'
        );

        //=================================================================
        // Add TsConfig
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
            'rkw_newsletter',
            'Configuration/TsConfig/setup.typoscript',
            'RKW Newsletter'
        );
    }
);