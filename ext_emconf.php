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
    'author_email' => 'maximilian@faesslerweb.de, developer@steffenkroggel.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '9.5.15',
	'constraints' => [
		'depends' => [
            'typo3' => '7.6.0-8.7.99',
			'rkw_basics' => '8.7.80-8.7.99',
            'rkw_authors' => '8.7.0-8.7.99',
			'rkw_mailer' => '9.5.9-9.5.99',
            'rkw_registration' => '8.7.0-8.7.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
