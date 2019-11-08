<?php

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
        ],
    ],
    'tx_rkwnewsletter_hash' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'tx_rkwnewsletter_priority' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:fe_user.tx_rkwnewsletter_priority',
        'config' => [
            'type' => 'check',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users', $tmpColsUser
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_subscription,tx_rkwnewsletter_priority'
);



