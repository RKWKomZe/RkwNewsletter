<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Subscription',
	'RKW Newsletter: Anmeldung'
);


if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'RKW.' . $_EXTKEY,
		'tools',	 		// Make module a submodule of 'tools'
		'management',		// Submodule key
		'',					// Position
		array(
			'Release' => 'testList, test, list, approve, defer, sendList, sendConfirm, send',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_management.xlf',
		)
	);

}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'RKW Newsletter');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile($_EXTKEY, 'Configuration/TsConfig/setup.txt', 'RKW Newsletter');

//=================================================================
// Add tables
//=================================================================
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_approval', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_approval.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_approval');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_issue', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_issue.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_issue');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_newsletter', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_newsletter.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_newsletter');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_rkwnewsletter_domain_model_topic', 'EXT:rkw_newsletter/Resources/Private/Language/locallang_csh_tx_rkwnewsletter_domain_model_topic.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_rkwnewsletter_domain_model_topic');