<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/../protected/config/main.php';

$debug = false;
if (stripos($_SERVER['HTTP_HOST'], '.local') !== false || stripos($_SERVER['HTTP_HOST'], '.dev') !== false) {
	$debug = true;
}

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',$debug);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();
