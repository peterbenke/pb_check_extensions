<?php

use PeterBenke\PbCheckExtensions\Task\CheckExtensionsTask;
use PeterBenke\PbCheckExtensions\Task\CheckExtensionsTaskAdditionalFieldProvider;

defined('TYPO3') or die();

// Scheduler Job: Check extensions
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CheckExtensionsTask::class] = [
    'extension' => 'pb_check_extensions',
    'title' => 'LLL:EXT:pb_check_extensions/Resources/Private/Language/locallang.xlf:task.checkExtensionsTask.title',
    'description' => 'LLL:EXT:pb_check_extensions/Resources/Private/Language/locallang.xlf:task.checkExtensionsTask.description',
    'additionalFields' => CheckExtensionsTaskAdditionalFieldProvider::class
];
