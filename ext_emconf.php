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
	'clearCacheOnLoad' => 0,
	'version' => '10.4.1',
	'constraints' => [
		'depends' => [
            'typo3' => '10.4.0-10.4.99',
			'core_extended' => '10.4.0-12.4.99',
			'postmaster' => '10.4.0-12.4.99',
            'fe_register' => '10.4.0-12.4.99',
            'rkw_authors' => '10.4.0-12.4.99',
		],
		'conflicts' => [
		],
		'suggests' => [
            'sr_freecap' => '2.5.6-2.5.99',
        ],
	],
];
