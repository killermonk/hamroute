<?php

require_once('constants.php');

// Things I want
if (!defined('STDIN'))
	define('STDIN', fopen('php://stdin', 'r'));
if (!defined('STDOUT'))
	define('STDOUT', fopen('php://stdout', 'w'));
if (!defined('STDERR'))
	define('STDERR', fopen('php://stderr', 'w'));

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'HAM Trip Planner - Console',
	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host='.DB_HOST.';dbname='.DB_NAME,
			'emulatePrepare' => true,
			'username' => DB_USER,
			'password' => DB_PASS,
			'charset' => 'utf8',
		),
	),
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params' => array(
		// Used by the config action
		'dbHost' => DB_HOST,
		'dbName' => DB_NAME,
		'dbUser' => DB_USER,
		'dbPass' => DB_PASS,
	),
);
