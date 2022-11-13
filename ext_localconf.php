<?php
if (!defined('TYPO3')) die ('Access denied');

// Scheduler Job: Check extensions
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\PeterBenke\PbCheckExtensions\Task\CheckExtensionsTask::class] = [
	'extension' => 'pb_check_extensions',
	'title' => 'LLL:EXT:pb_check_extensions/Resources/Private/Language/locallang.xlf:task.checkExtensionsTask.title',
	'description' => 'LLL:EXT:pb_check_extensions/Resources/Private/Language/locallang.xlf:task.checkExtensionsTask.description',
	'additionalFields' => \PeterBenke\PbCheckExtensions\Task\CheckExtensionsTaskAdditionalFieldProvider::class
];
