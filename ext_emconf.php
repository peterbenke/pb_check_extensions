<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Check extensions',
    'description' => 'Checks, if there are updates available for installed extensions and sends an email',
    'category' => 'module',
    'author' => 'Peter Benke',
    'author_email' => 'info@typomotor.de',
    'state' => 'stable',
    'version' => '13.4.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
