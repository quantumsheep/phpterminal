<?php
// Load composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Load SessionHandler
new \Alph\Services\SessionHandler;

// Start routing
require "./Routes.php";