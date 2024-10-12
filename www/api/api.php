<?php

require 'vendor/autoload.php';

session_start();

$settings = require 'config/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require 'config/dependencies.php';

// Register middleware
require 'config/middleware.php';

// Register routes
require 'config/routes.php';

$app->run();
