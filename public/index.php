<?php
/**
 * @package Slim ^3.0
 */

require '../vendor/autoload.php';

include '../database/config.php';

$app = new Routes\Web();
$app->routes();