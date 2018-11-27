<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_approval', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_approval.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_approval');
$GLOBALS['TCA']['tx_rkwnewsletter_domain_model_approval'] = array(
	'ctrl' => array(
		'hideTable' => true,
		'title'	=> 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'searchFields' => 'topic, pages, allowed_by_user_stage1, allowed_by_user_stage2, allowed_tstamp_stage1, allowed_tstamp_stage2, sent_info_tstamp_stage1, sent_info_tstamp_stage2, sent_reminder_tstamp_stage1, sent_reminder_tstamp_stage2,',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_approval.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'topic, page, allowed_by_user_stage1, allowed_by_user_stage2, allowed_tstamp_stage1, allowed_tstamp_stage2, sent_info_tstamp_stage1, sent_info_tstamp_stage2, sent_reminder_tstamp_stage1, sent_reminder_tstamp_stage2',
	),
	'types' => array(
		'1' => array('showitem' => 'topic, page, allowed_by_user_stage1, allowed_by_user_stage2, allowed_tstamp_stage1, allowed_tstamp_stage2, sent_info_tstamp_stage1, sent_info_tstamp_stage2, sent_reminder_tstamp_stage1, sent_reminder_tstamp_stage2'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

        'topic' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.topic',
            'config' => array(
                'type' => 'select',
                'size' => 1,
                'eval' => 'int',
                'minitems' => 0,
                'maxitems' => 1,
                'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
                'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
            )
        ),
        'page' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.page',
            'config' => array(
                'type' => 'select',
                'size' => 1,
                'eval' => 'int',
                'minitems' => 0,
                'maxitems' => 1,
                'foreign_table' => 'pages',
                'foreign_table_where' => 'AND pages.deleted = 0 AND pages.hidden = 0',
            )
        ),
		'allowed_by_user_stage1' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.allowed_by_user_stage1',
			'config' => array(
				'type' => 'select',
				'size' => 7,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 99,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			)
		),
		'allowed_by_user_stage2' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.allowed_by_user_stage2',
			'config' => array(
				'type' => 'select',
				'size' => 7,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 99,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			)
		),
		'allowed_tstamp_stage1' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.allowed_tstamp_stage1',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'allowed_tstamp_stage2' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.allowed_tstamp_stage2',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'sent_info_tstamp_stage1' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.sent_info_tstamp_stage1',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'sent_info_tstamp_stage2' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.sent_info_tstamp_stage2',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'sent_reminder_tstamp_stage1' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.sent_reminder_tstamp_stage1',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'sent_reminder_tstamp_stage2' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_approval.sent_reminder_tstamp_stage2',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
			),
		),
		'issue' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_issue',
            ),
		),
	),
);
