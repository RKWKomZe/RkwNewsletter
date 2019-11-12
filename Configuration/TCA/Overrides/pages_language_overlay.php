<?php

$tmpColsPagesOverlay = [

    'tx_rkwnewsletter_teaser_heading' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_heading',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'tx_rkwnewsletter_teaser_text' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_text',
        'config' => [
            'type' => 'text',
            'cols' => '40',
            'rows' => '15',
            'wrap' => 'off',
            'eval' => 'RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation'
        ],
        'defaultExtras' => 'richtext[]:rte_transform[flag=rte_enabled|mode=ts_css]'
    ],
    'tx_rkwnewsletter_teaser_link' => [
        'exclude' => 1,
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
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages_language_overlay', $tmpColsPagesOverlay
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages_language_overlay',
    '--div--;LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter;,tx_rkwnewsletter_teaser_heading,tx_rkwnewsletter_teaser_text,tx_rkwnewsletter_teaser_link'
);
;