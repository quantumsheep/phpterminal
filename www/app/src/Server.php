<?php
session_name('alph_sess');
session_start();

// Load composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Start routing
require "./Routes.php";
var_dump(\Alph\Services\Database::connect());