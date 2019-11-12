<?php

// extend "pages" TCA
// Following line do ask for reload on newsletter-change (for correct topic selection],
$GLOBALS['TCA']['pages']['ctrl']['requestUpdate'] .= ',tx_rkwnewsletter_newsletter, tx_rkwnewsletter_topic';

$tmpColsPages = [

    'tx_rkwnewsletter_newsletter' => [
        'exclude' => 0,
        'onChange' => 'reload',
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_newsletter',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'eval' => 'int',
            'items' => [
                ['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_newsletter.please_choose',0],
            ],
            'minitems' => 0,
            'maxitems' => 1,
            'default' => '',
            'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
            'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_newsletter.deleted = 0 AND tx_rkwnewsletter_domain_model_newsletter.hidden = 0',
        ],
    ],
    'tx_rkwnewsletter_topic' => [
        'displayCond' => 'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
        'onChange' => 'reload',
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_topic',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'eval' => 'int',
            'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
            'foreign_table_where' => 'AND newsletter=###REC_FIELD_tx_rkwnewsletter_newsletter### AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
            'minitems' => 1,
            'maxitems' => 1,
            'items' => [
                ['LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_topic.please_choose',0],
            ],
        ],
    ],
    /*'tx_rkwnewsletter_exclude' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_exclude',
        'config' => [
            'type' => 'passthrough',
        ],
    ),*/
    'tx_rkwnewsletter_teaser_heading' => [
        'displayCond' => [
            'AND' => [
                'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
                'FIELD:tx_rkwnewsletter_topic:REQ:TRUE',
            ],
        ],
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_heading',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim,required'
        ],
    ],
    'tx_rkwnewsletter_teaser_text' => [
        'displayCond' => [
            'AND' => [
                'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
                'FIELD:tx_rkwnewsletter_topic:REQ:TRUE',
            ],
        ],
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_text',
        'config' => [
            'type' => 'text',
            'cols' => '40',
            'rows' => '15',
            'wrap' => 'off',
            'eval' => 'RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation,required'
        ],
        'defaultExtras' => 'richtext[]:rte_transform[flag=rte_enabled|mode=ts_css]'
    ],
    'tx_rkwnewsletter_teaser_image' => [
        'displayCond' => [
            'AND' => [
                'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
                'FIELD:tx_rkwnewsletter_topic:REQ:TRUE',
            ],
        ],
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'txRkwnewsletterTeaserImage',
            [
                'maxitems' => 1,

                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => [
                    '0' => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ],
                ],

            ],
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        ),
    ],
    'tx_rkwnewsletter_teaser_link' => [
        'displayCond' => [
            'AND' => [
                'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
                'FIELD:tx_rkwnewsletter_topic:REQ:TRUE',
            ],
        ],
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_link',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
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
                        // 'blindLinkOptions' => 'mail,file,page,spec,folder',
                        // allowed extensions for file
                        //'allowedExtensions' => 'mp3,ogg',
                    ],

                ],
            ],
            'softref' => 'typolink'
        ],
    ],
    'tx_rkwnewsletter_include_tstamp' => [
        'displayCond' => [
            'AND' => [
                'FIELD:tx_rkwnewsletter_newsletter:REQ:TRUE',
                'FIELD:tx_rkwnewsletter_topic:REQ:TRUE',
            ],
        ],
        'exclude' => 0,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_include_tstamp',
        'config' => [
            'type' => 'input',
            'size' => 13,
            'max' => 20,
            'eval' => 'datetime',
            'checkbox' => 0,
            'default' => 0,
            'readOnly' => true,
            'range' => [
                'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
            ],
        ],
    ],
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages', $tmpColsPages
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_newsletter,tx_rkwnewsletter_topic,tx_rkwnewsletter_teaser_heading,tx_rkwnewsletter_teaser_text,tx_rkwnewsletter_teaser_link,tx_rkwnewsletter_teaser_image,tx_rkwnewsletter_include_tstamp'
);