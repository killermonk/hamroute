<?php
/**
 * Define all of the Path aliases that we want to use globally throughout the application
 */

// Alias to our Models directory
Yii::setPathOfAlias('model',realpath(dirname(__FILE__).'/../model'));
