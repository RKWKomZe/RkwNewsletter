<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_newsletter"
 *
 * Auto generated by Extension Builder 2015-04-09
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'RKW Newsletter',
	'description' => '',
	'category' => 'misc',
    'author' => 'Maximilian Fäßler, Steffen Kroggel',
    'author_email' => 'faesslerweb@web.de, developer@steffenkroggel.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.7.10',
	'constraints' => [
		'depends' => [
            'typo3' => '7.6.0-8.7.99',
			'rkw_basics' => '8.7.15-8.7.99',
			'rkw_mailer' => '8.7.32-8.7.99',
            'rkw_registration' => '8.7.0-8.7.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
