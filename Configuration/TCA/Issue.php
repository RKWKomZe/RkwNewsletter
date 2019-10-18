<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_issue', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_issue.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_issue');
$GLOBALS['TCA']['tx_rkwnewsletter_domain_model_issue'] = array(
	'ctrl' => array(
		'hideTable' => true,
		'title'	=> 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,

		'searchFields' => 'name, status, pages, newsletter, pages_approval, allowed_by_user, info_tstamp, reminder_tstamp, allowed_tstamp, sent_tstamp',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_issue.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'title, status, sent_tstamp',
	),
	'types' => array(
		'1' => array('showitem' => 'title, status, sent_tstamp'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'title' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'status' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.status',
			'config' => array(
				'type' => 'select',
                'renderType' => 'selectSingle',
				'items' => array(
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.issue', '0'),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.approval', '1'),
                    array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.ready', '2'),
                    array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.sending', '3'),
                    array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.sent', '4'),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.deferred', '98'),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_issue.status.error', '99'),
				),
				'size' => 1,
				'eval' => 'int, required',
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'info_tstamp' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.info_tstamp',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
                'readOnly' => true
            ),
		),
        'reminder_tstamp' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.reminder_tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
            ),
        ),
        'release_tstamp' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.release_tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => 1,
                'readOnly' => true
            ),
        ),
        'sent_tstamp' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.sent_tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime',
                'checkbox' => true,
                'readOnly' => true
            ),
        ),
        'pages' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.pages',
            'config' => array(
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'pages',
                'foreign_field' => 'tx_rkwnewsletter_issue',
                'foreign_sortby' => 'sorting',
                'show_thumbs' =>  true,
                'minitems' => 0,
                'maxitems' => 9999,
                'size'  => 5,
                'readOnly' => true,
                'appearance' => array(
                    'elementBrowserType' => 'db',
                    'useSortable' => false,
                    'showPossibleLocalizationRecords' => false,
                    'showRemovedLocalizationRecords' => false,
                    'showSynchronizationLink' => false,
                    'showAllLocalizationLink' => false,
                    'enabledControls' => [
                        'info' => true,
                        'new' => false,
                        'dragdrop' => false,
                        'sort' => false,
                        'hide' => false,
                        'delete' => false,
                        'localize' => false,
                    ],
                ),
            )
        ),
        'approvals' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_issue.approvals',
            'config' => array(
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_approval',
                'foreign_field' => 'issue',
                //'foreign_sortby' => 'sorting',
                'show_thumbs' =>  true,
                'minitems' => 0,
                'maxitems' => 9999,
                'size'  => 5,
                'readOnly' => true,
                'appearance' => array(
                    'elementBrowserType' => 'db',
                    'useSortable' => false,
                    'showPossibleLocalizationRecords' => false,
                    'showRemovedLocalizationRecords' => false,
                    'showSynchronizationLink' => false,
                    'showAllLocalizationLink' => false,
                    'enabledControls' => [
                        'info' => true,
                        'new' => false,
                        'dragdrop' => false,
                        'sort' => false,
                        'hide' => false,
                        'delete' => false,
                        'localize' => false,
                    ],
                ),
            )
        ),
        'newsletter' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
            ),
        ),
        'recipients' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        'queue_mail' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwmailer_domain_model_queuemail',
            ),
        ),
	),
);
