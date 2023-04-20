<?php

error_reporting(E_ERROR);
if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/bootstrap.php';
require_once ROOT_PATH . '/config/routes.php';

/* @var \Slim\App $app */
$app->run();