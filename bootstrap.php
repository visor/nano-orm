<?php

error_reporting(E_ALL | E_STRICT);
ini_set('error_log', __DIR__ . '/../build/logs/error.log');
ini_set('display_errors', true);

if (!class_exists('Application', false)) {
	include __DIR__ . '/dependencies/nano/library/Application.php';
}

$application = Application::create()
	->withConfigurationFormat('php')
	->withRootDir(__DIR__)
	->withModule('nano-orm', __DIR__)
	->configure()
;

$GLOBALS['application'] = $application;