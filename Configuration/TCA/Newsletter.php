<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_newsletter', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_newsletter.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_newsletter');
$GLOBALS['TCA']['tx_rkwnewsletter_domain_model_newsletter'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'name, issue_title, sender_name, sender_mail, reply_name, reply_mail, return_path, priority, template, settings_page, format, rythm, approval, type, usergroup, topic, recently_sent,',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_newsletter.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, issue_title, sender_name, sender_mail, reply_name, reply_mail, return_path, template, settings_page, rythm, approval, type, usergroup, topic, last_sent_tstamp, last_issue_tstamp, issue',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, name, issue_title, sender_name, sender_mail, reply_name, reply_mail, return_path, template, settings_page, encoding, charset, rythm, approval, type, usergroup, topic, last_sent_tstamp, last_issue_tstamp, issue, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden;;1, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
	
		'sys_language_uid' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'default' => 0,
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => false,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
				'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_newsletter.pid=###CURRENT_PID### AND tx_rkwnewsletter_domain_model_newsletter.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		'hidden' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => false,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => false,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'name' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			),
		),
		'issue_title' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.issue_title',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			),
		),
		'sender_name' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.sender_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			),
		),
		'sender_mail' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.sender_mail',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			),
		),
		'reply_name' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.reply_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'reply_mail' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.reply_mail',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'return_path' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.return_path',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'priority' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.normal', '2'),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.high', '3'),
					array('nLLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.low', '1'),
				),
				'size' => 1,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
        'template' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.template',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'default' => 'Default',
                'eval' => 'trim, required'
            )
        ),
        'settings_page' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xl:tx_rkwnewsletter_domain_model_newsletter.settings_page',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'int, required',
                'wizards' => array(
                    '_PADDING' => 2,
                    'link' => array(
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'link_popup.gif',
                        'module' => array(
                            'name' => 'wizard_element_browser',
                            'urlParameters' => array(
                                'mode' => 'wizard',
                            )
                        ),
                        'JSopenParams' => 'height=400,width=550,status=0,menubar=0,scrollbars=1',
                        'params' => Array(
                            // List of tabs to hide in link window. Allowed values are:
                            // file, mail, page, spec, folder, url
                            'blindLinkOptions' => 'mail,file,spec,folder,url',

                            // allowed extensions for file
                            //'allowedExtensions' => 'mp3,ogg',
                        )
                    )
                ),
                'softref' => 'typolink'
            )
        ),
		'format' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.format',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('Plaintext', '1'),
					array('HTML', '2'),
					array('Plaintext / HTML', '3'),
				),
				'size' => 1,
				'eval' => 'int, required',
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'rythm' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.weekly', 1),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.monthly', 2),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.quarterly', 3),
					array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.yearly', 4),
				),
				'size' => 1,
				'eval' => 'int, required',
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'approval' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.approval',
			'config' => array(
				'type' => 'select',
				'size' => 8,
				'eval' => 'int',
				'default' => '',
				'minitems' => 1,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			)
		),
		'usergroup' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.usergroup',
			'config' => array(
				'type' => 'select',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'AND fe_groups.deleted = 0 AND fe_groups.hidden = 0',
			)
		),
		'topic' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.topic',
			'config' => array(
				'type' => 'inline',
				'internal_type' => 'db',
				'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
				'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
				'foreign_field' => 'newsletter',
				'foreign_sortby' => 'sorting',
				'show_thumbs' =>  true,
				'minitems' => 1,
                'maxitems' => 9999,
				'size'  => 5,
				'appearance' => array(
					'elementBrowserType' => 'db'
				),
			)
		),
        'issue' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.issue',
            'config' => array(
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_issue',
                'foreign_field' => 'newsletter',
                //'foreign_sortby' => 'sorting',
                'show_thumbs' =>  true,
                'minitems' => 0,
                'maxitems' => 9999,
                'size'  => 5,
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
            ),
        ),
        'last_sent_tstamp' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.last_sent_tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'readOnly' => 1,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
        'last_issue_tstamp' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.last_issue_tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'readOnly' => 1,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
	),
);
