<?php
// Load composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

ini_set('session.save_handler', 'files');
// Load SessionHandler
new \Alph\Services\SessionHandler;

// Start routing
require "./Routes.php";