<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Register BackendModule
        //=================================================================
        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'RKW.' . $extKey,
                'tools',	 		// Make module a submodule of 'tools'
                'management',		// Submodule key
                '',					// Position
                [
                    'Release' => 'confirmationList, approve, defer, testList, testSend, createIssueList, createIssue, sendList, sendConfirm, send',
                ],
                [
                    'access' => 'user,group',
                    'icon'   => 'EXT:' . $extKey . '/ext_icon.gif',
                    'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_management.xlf',
                ]
            );
        }


        //=================================================================
        // Add tables
        //=================================================================
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_rkwnewsletter_domain_model_approval'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_rkwnewsletter_domain_model_issue'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_rkwnewsletter_domain_model_newsletter'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
            'tx_rkwnewsletter_domain_model_topic'
        );


    },
    'rkw_newsletter'
);

