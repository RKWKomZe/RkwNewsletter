<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
        
        $extensionKey = 'rkw_newsletter';
        
        //=================================================================
        // Register Plugins
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            $extensionKey,
            'Subscription',
            'RKW Newsletter: Anmeldung'
        );
        
        //=================================================================
        // Add Flexforms
        //=================================================================
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        $pluginSignature = str_replace('_','', $extensionKey) . '_subscription';
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        $fileName = 'FILE:EXT:rkw_newsletter/Configuration/FlexForms/Subscription.xml';

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            $fileName
        );
        
        //=================================================================
        // TCA Extension
        //=================================================================
        $tmpCols = [
            'tx_rkwnewsletter_is_editorial' => [
                'exclude' => true,
                'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rkwnewsletter_is_editorial',
                'config' => [
                    'type' => 'check',
                ],
            ],
        ];
        
        // Extend TCA when rkw_authors is available
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_authors')) {
        
            $tmpCols['tx_rkwnewsletter_authors'] = [
                'exclude' => 0,
                'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rkwnewsletter_authors',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'tx_rkwauthors_domain_model_authors',
                    'foreign_table_where' => 'AND tx_rkwauthors_domain_model_authors.internal = 1 AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwauthors_domain_model_authors.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwauthors_domain_model_authors.sys_language_uid = ###REC_FIELD_sys_language_uid### ORDER BY tx_rkwauthors_domain_model_authors.last_name ASC',
                    'maxitems'      => 1,
                    'minitems'      => 0,
                    'size'          => 5,
                ],
            ];
        }
        
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tmpCols);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tt_content',
            '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tt_content.tx_rkwnewsletter;,tx_rkwnewsletter_is_editorial, tx_rkwnewsletter_authors'
        );
        


    }
);