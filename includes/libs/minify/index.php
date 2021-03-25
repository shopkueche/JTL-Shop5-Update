<?php declare(strict_types=1);
/**
 * Sets up MinApp controller and serves files
 * @package Minify
 */

use Minify\App;

$app = (require __DIR__ . '/bootstrap.php');
/* @var App $app */

$app->runServer();
