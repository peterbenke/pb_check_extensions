<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Check extensions',
	'description' => 'Checks, if there are updates available for installed extensions and sends an email',
	'category' => 'module',
	'author' => 'Peter Benke',
	'author_email' => 'info@typomotor.de',
	'state' => 'stable',
	'version' => '11.5.1',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-11.5.99',
		],
		'conflicts' => [],
		'suggests' => [],
	],
];
