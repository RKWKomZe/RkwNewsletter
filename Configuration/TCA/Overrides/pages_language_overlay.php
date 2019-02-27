<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tmpColsPagesOverlay = array(

    'tx_rkwnewsletter_teaser_heading' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_heading',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ),
    ),
    'tx_rkwnewsletter_teaser_text' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_text',
        'config' => array(
            'type' => 'text',
            'cols' => '40',
            'rows' => '15',
            'wrap' => 'off',
            'eval' => 'RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation'
        ),
        'defaultExtras' => 'richtext[]:rte_transform[flag=rte_enabled|mode=ts_css]'
    ),
    'tx_rkwnewsletter_teaser_link' => array(
        'exclude' => 1,
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
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages_language_overlay', $tmpColsPagesOverlay, 1
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages_language_overlay',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_teaser_heading,tx_rkwnewsletter_teaser_text,tx_rkwnewsletter_teaser_link'
);
;