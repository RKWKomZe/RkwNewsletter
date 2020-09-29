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
            'eval' => 'RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation',
            'enableRichtext' => true,
        ],
    ],
    'tx_rkwnewsletter_teaser_link' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_teaser_link',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputLink',
            'size' => 30,
            'eval' => 'trim',
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