<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Check extensions',
	'description' => 'Checks, if there are updates available for installed extensions and sends an email',
	'category' => 'module',
	'author' => 'Peter Benke',
	'author_email' => 'info@typomotor.de',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '8.7.0-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);