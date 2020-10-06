<?php
defined('TYPO3_MODE') || die('Access denied.');


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
    'Configuration/TsConfig/setup.txt',
    'RKW Newsletter'
);
