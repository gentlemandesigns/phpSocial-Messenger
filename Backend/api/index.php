<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * The Slim Framework init
 */
require 'vendor/autoload.php';
require '../includes/config.php';
require './config.php';
require './language.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['host']   = $CONF['host'];
$config['db']['user']   = $CONF['user'];
$config['db']['pass']   = $CONF['pass'];
$config['db']['name']   = $CONF['name'];

$app = new \Slim\App(['settings' => $config]);

/**
 * Get application routes
 */

require 'routes.php';

/**
 * Run the app
 */
$app->run();
