<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {
    
        // extend "fe_users" TCA
        $tmpColsUser = [
            'tx_rkwnewsletter_subscription' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:fe_user.tx_rkwnewsletter_domain_model_subscription',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'size' => 8,
                    'eval' => 'int',
                    'minitems' => 0,
                    'maxitems' => 9999,
                    'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
                    'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
                    'itemsProcFunc' => 'RKW\RkwNewsletter\TCA\OptionLabels->getNewsletterTopicTitlesWithRootByUid',
                ],
            ],
            'tx_rkwnewsletter_hash' => [
                'config' => [
                    'type' => 'passthrough',
                ],
            ]
        ];
        
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
            'fe_users', $tmpColsUser
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'fe_users',
            '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_subscription'
        );
    
    }
);

