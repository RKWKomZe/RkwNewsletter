<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_topic', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_topic.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_topic');
$GLOBALS['TCA']['tx_rkwnewsletter_domain_model_topic'] = array(
	'ctrl' => array(
		'hideTable' => 1,
		'title'	=> 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'delete' => 'deleted',
		'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
		),
		'searchFields' => 'name,short_description,approval_stage1,approval_stage2,topic_pid,',
		'iconfile' => 'EXT:rkw_newsletter/Resources/Public/Icons/tx_rkwnewsletter_domain_model_topic.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden, name, approval_stage1, approval_stage2, container_page, primary_color, secondary_color, is_special',
	),
	'types' => array(
		'1' => array('showitem' => 'name, approval_stage1, approval_stage2, container_page, primary_color, secondary_color, is_special, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden;;1, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
	
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
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'short_description' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.short_description',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),

		'approval_stage1' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.approval_stage1',
			'config' => array(
				'type' => 'select',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 1,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			)
		),
		'approval_stage2' => array(
			'exclude' => false,
			'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.approval_stage2',
			'config' => array(
				'type' => 'select',
				'size' => 8,
				'eval' => 'int',
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'be_users',
				'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0',
			)
		),

        'container_page' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xl:tx_rkwnewsletter_domain_model_topic.container_page',
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

        'primary_color' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#e64415',
                'wizards' => array(
                    'colorChoice' => array(
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.primary_color',
                        'module' => array(
                            'name' => 'wizard_colorpicker',
                        ),
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    )
                )
            ),
        ),

        'secondary_color' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color',
            'config' => array(
                'type' => 'input',
                'size' => 10,
                'eval' => 'trim,required',
                'default' => '#333333',
                'wizards' => array(
                    'colorChoice' => array(
                        'type' => 'colorbox',
                        'title' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.secondary_color',
                        'module' => array(
                            'name' => 'wizard_colorpicker',
                        ),
                        'JSopenParams' => 'height=400,width=600,status=0,menubar=0,scrollbars=1',
                    )
                )
            ),
        ),

        'is_special' => array(
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:tx_rkwnewsletter_domain_model_topic.is_special',
            'config' => array(
                'type' => 'check'
            ),
        ),        

        'newsletter' => array(
            'config' => array(
                'type' => 'passthrough',
                'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
            ),
        ),

        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        
	),
);
