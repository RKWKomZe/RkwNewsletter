<?php
return [
	'ctrl' => [
		'hideTable' => 1,
		'title'	=> 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'delete' => 'deleted',
		'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
		],
		'searchFields' => 'name,short_description,approval_stage1,approval_stage2,topic_pid,',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_topic.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'hidden, name, approval_stage1, approval_stage2, container_page, primary_color, primary_color_editorial, secondary_color, secondary_color_editorial, is_special',
	],
	'types' => [
		'1' => ['showitem' => 'name, approval_stage1, approval_stage2, container_page, primary_color, primary_color_editorial, secondary_color, secondary_color_editorial, is_special, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden,--palette--;;1, starttime, endtime'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [
	
	    'hidden' => [
			'exclude' => false,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => false,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
			],
		],
		'endtime' => [
			'exclude' => false,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
				],
			],
		],
		'name' => [
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.name',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
		'short_description' => [
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.short_description',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],

		'approval_stage1' => [
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.approval_stage1',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 1,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			],
		],
		'approval_stage2' => [
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.approval_stage2',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			],
		],

        'container_page' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xl:tx_rkwnewsletter_domain_model_topic.container_page',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'int, required',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'JSopenParams' => 'height=400,width=550,status=0,menubar=0,scrollbars=1',
                        'params' => [
                            // List of tabs to hide in link window. Allowed values are:
                            // file, mail, page, spec, folder, url
                             'blindLinkOptions' => 'mail,file,spec,folder,url',

                            // allowed extensions for file
                            //'allowedExtensions' => 'mp3,ogg',
                        ],
                    ],
                ],
                'softref' => 'typolink'
            ],
        ],

        'primary_color' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#e64415',
                'wizards' => [
                    'colorChoice' => [
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color',
                        'module' => [
                            'name' => 'wizard_colorpicker',
                        ],
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],

        'primary_color_editorial' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color_editorial',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#e64415',
                'wizards' => [
                    'colorChoice' => [
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color_editorial',
                        'module' => [
                            'name' => 'wizard_colorpicker',
                        ],
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],

        'secondary_color' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#333333',
                'wizards' => [
                    'colorChoice' => [
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color',
                        'module' => [
                            'name' => 'wizard_colorpicker',
                        ],
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],

        'secondary_color_editorial' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color_editorial',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#333333',
                'wizards' => [
                    'colorChoice' => [
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color_editorial',
                        'module' => [
                            'name' => 'wizard_colorpicker',
                        ],
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],

        'is_special' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.is_special',
            'config' => [
                'type' => 'check'
            ],
        ],

        'newsletter' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
            ],
        ],

        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
