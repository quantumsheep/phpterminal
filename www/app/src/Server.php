<?php
// Load composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Load SessionHandler
new \Alph\Services\SessionHandler;

$_POST["ROUTED"] = false;

// Start routing
require "./Routes.php";