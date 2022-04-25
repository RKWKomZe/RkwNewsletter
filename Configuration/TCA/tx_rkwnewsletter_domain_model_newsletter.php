<?php
return [
	'ctrl' => [
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
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => 'name, issue_title, sender_name, sender_mail, reply_mail, return_path, priority, template, settings_page, format, rythm, day_for_sending, approval, usergroup, topic, recently_sent,',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_newsletter.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, issue_title, sender_name, sender_mail, reply_mail, return_path, template, type, settings_page, rythm, day_for_sending, approval, usergroup, topic, last_sent_tstamp, last_issue_tstamp, issue',
	],
	'types' => [
		'1' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, name, issue_title, sender_name, sender_mail, reply_mail, return_path, template, type, settings_page, encoding, charset, rythm, day_for_sending, approval, usergroup, topic, last_sent_tstamp, last_issue_tstamp, issue, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden,--palette--;;1, starttime, endtime'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [
	
		'sys_language_uid' => [
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'default' => 0,
				'items' => [
					['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0],
				],
			],
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => false,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
				'items' => [
					['', 0],
				],
				'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
				'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_newsletter.pid=###CURRENT_PID### AND tx_rkwnewsletter_domain_model_newsletter.sys_language_uid IN (-1,0)',
			],
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
			],
		],

		'hidden' => [
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]

			],
		],
		'endtime' => [
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputDateTime',
				'size' => 13,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
			],
		],
		'name' => [
			'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
			],
		],
		'issue_title' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.issue_title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			],
		],
		'sender_name' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.sender_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'sender_mail' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.sender_mail',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'reply_name' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.reply_name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'reply_mail' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.reply_mail',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'return_path' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.return_path',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'priority' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
				'items' => [
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.normal', '2'],
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.high', '3'],
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.priority.low', '1'],
				],
				'size' => 1,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
        'template' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.template',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'default' => 'Default',
                'eval' => 'trim, required'
            ],
        ],
        'settings_page' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xl:tx_rkwnewsletter_domain_model_newsletter.settings_page',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputLink',
                'size' => 30,
                'eval' => 'int, required',
                'softref' => 'typolink'
            ],
        ],
		'format' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.format',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
				'items' => [
					['Plaintext', '1'],
					['HTML', '2'],
					['Plaintext / HTML', '3'],
				],
				'size' => 1,
				'eval' => 'int, required',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
		'rythm' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
				'items' => [
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.weekly', 1],
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.monthly', 2],
                    ['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.bimonthly', 3],
                    ['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.quarterly', 4],
					['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.rythm.semiannually', 5],
				],
				'size' => 1,
				'eval' => 'int, required',
				'minitems' => 1,
				'maxitems' => 1,
			],
		],
        'day_for_sending' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.day_for_sending',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'range' => array(
                    'lower' => 1,
                    'upper' => 31
                ),
                'default' => 15,
                'eval' => 'num, trim, required',
            ],
        ],        
		'approval' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.approval',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
				'size' => 8,
				'eval' => 'int',
				'default' => '',
				'minitems' => 1,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0 ORDER BY be_users.username',
			],
		],
		'usergroup' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.usergroup',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'AND fe_groups.deleted = 0 AND fe_groups.hidden = 0',
			],
		],
		'topic' => [
            'exclude' => true,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.topic',
			'config' => [
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
				'appearance' => [
					'elementBrowserType' => 'db'
				],
			],
		],
        'issue' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.issue',
            'config' => [
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_issue',
                'foreign_field' => 'newsletter',
                'show_thumbs' =>  true,
                'minitems' => 0,
                'maxitems' => 9999,
                'size'  => 5,
                'appearance' => [
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
                ],
            ],
        ],
        'last_sent_tstamp' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.last_sent_tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'readOnly' => 1,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
            ],
        ],
        'last_issue_tstamp' => [
            'exclude' => true,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_newsletter.last_issue_tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'readOnly' => 1,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
            ],
        ],
	],
];
