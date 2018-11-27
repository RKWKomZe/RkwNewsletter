<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

// extend "pages" TCA
// Following line do ask for reload on newsletter-change (for correct topic selection)
$GLOBALS['TCA']['pages']['ctrl']['requestUpdate'] .= ',tx_rkwnewsletter_newsletter';

$tmpColsPages = array(

    'tx_rkwnewsletter_newsletter' => array(

        'onChange' => 'reload',
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_newsletter',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'eval' => 'int',
            'items' => array (
                array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_newsletter.please_choose',0),
            ),
            'minitems' => 0,
            'maxitems' => 1,
            'default' => '',
            'foreign_table' => 'tx_rkwnewsletter_domain_model_newsletter',
            'foreign_table_where' => 'AND tx_rkwnewsletter_domain_model_newsletter.deleted = 0 AND tx_rkwnewsletter_domain_model_newsletter.hidden = 0',
        )
    ),
    'tx_rkwnewsletter_topic' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_topic',
        'config' => array(
            'type' => 'select',
            'renderType' => 'selectSingle',
            'size' => 1,
            'eval' => 'int',
            'foreign_table' => 'tx_rkwnewsletter_domain_model_topic',
            'foreign_table_where' => 'AND newsletter=###REC_FIELD_tx_rkwnewsletter_newsletter### AND tx_rkwnewsletter_domain_model_topic.deleted = 0 AND tx_rkwnewsletter_domain_model_topic.hidden = 0',
            'minitems' => 0,
            'maxitems' => 1,
            'items' => array (
                array('LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_topic.please_choose',0),
            ),
        )
    ),
    'tx_rkwnewsletter_exclude' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_exclude',
        'config' => array(
            'type' => 'check',
        ),
    ),
    'tx_rkwnewsletter_teaser_heading' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_heading',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ),
    ),
    'tx_rkwnewsletter_teaser_text' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_text',
        'config' => array(
            'type' => 'text',
            'cols' => '40',
            'rows' => '15',
            'wrap' => 'off',
        ),
        'defaultExtras' => 'richtext[]:rte_transform[flag=rte_enabled|mode=ts_css]'
    ),
    'tx_rkwnewsletter_teaser_image' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_image',
        'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            'txRkwnewsletterTeaserImage',
            array(
                'maxitems' => 1,

                // Use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => array(
                    '0' => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
                        'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                    )
                )

            ),
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        ),
    ),
    'tx_rkwnewsletter_teaser_link' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_link',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
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
                        // 'blindLinkOptions' => 'mail,file,page,spec,folder',
                        // allowed extensions for file
                        //'allowedExtensions' => 'mp3,ogg',
                    )

                )
            ),
            'softref' => 'typolink'
        ),
    ),
    'tx_rkwnewsletter_include_tstamp' => array(
        'exclude' => 0,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_include_tstamp',
        'config' => array(
            'type' => 'input',
            'size' => 13,
            'max' => 20,
            'eval' => 'datetime',
            'checkbox' => 0,
            'default' => 0,
            'readOnly' => true,
            'range' => array(
                'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
            ),
        ),
    ),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages', $tmpColsPages, 1
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_newsletter,tx_rkwnewsletter_topic,tx_rkwnewsletter_exclude,tx_rkwnewsletter_teaser_heading,tx_rkwnewsletter_teaser_text,tx_rkwnewsletter_teaser_link,tx_rkwnewsletter_teaser_image,tx_rkwnewsletter_include_tstamp'
);