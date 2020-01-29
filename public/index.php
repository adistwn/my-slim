<?php
/**
 * @package Slim ^3.0
 */

require_once __DIR__.'/../vendor/autoload.php';

require __DIR__.'/../database/config.php';

$app = new Routes\Web();
$app->routes();