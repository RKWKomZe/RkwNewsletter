<?php

// extend "pages" TCA
// Following line do ask for reload on newsletter-change (for correct topic selection],
// @deprecated since TYPO3 9.5
//$GLOBALS['TCA']['pages']['ctrl']['requestUpdate'] .= ',tx_rkwnewsletter_newsletter, tx_rkwnewsletter_topic';

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
    'tx_rkwnewsletter_exclude' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_exclude',
        'config' => [
            'type' => 'passthrough',
        ],
    ],
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
            'eval' => 'RKW\\RkwNewsletter\\Validation\\TCA\\NewsletterTeaserLengthEvaluation,required',
            'enableRichtext' => true,
        ],
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
                'maxitems' => 1
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
            'renderType' => 'inputLink',
            'size' => 30,
            'eval' => 'trim',
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
        'label' => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_include_tstamp',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'size' => 13,
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