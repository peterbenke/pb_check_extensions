<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Check extensions',
	'description' => 'Checks, if there are updates available for installed extensions and sends an email',
	'category' => 'module',
	'author' => 'Peter Benke',
	'author_email' => 'info@typomotor.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '2.0.0',
	'constraints' => [
		'depends' => [
			'typo3' => '9.5.0-9.5.99',
		],
		'conflicts' => [],
		'suggests' => [],
	],
];