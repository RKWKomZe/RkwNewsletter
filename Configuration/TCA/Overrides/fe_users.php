<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// extend "fe_users" TCA
$tmpColsUser = array(
    'tx_rkwnewsletter_subscription' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:fe_user.tx_rkwnewsletter_domain_model_subscription',
        'config' => array(
            'type' => 'select',
            'size' => 8,
            'eval' => 'int',
            'minitems' => 0,
            'maxitems' => 9999,
            'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
            'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
        )
    ),
    'tx_rkwnewsletter_hash' => array(
        'config' => array(
            'type' => 'passthrough',
        ),
    ),



);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users', $tmpColsUser
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_subscription'
);



