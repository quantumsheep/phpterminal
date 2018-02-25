<?php
ini_set('session.use_strict_mode', "1");
ini_set('session.save_handler', "files");
ini_set('session.save_path', "D:\\Projets webs\\phpterminal\\www\\session");
session_start([
    'read_and_close' => true,
]);

// Load composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Start routing
require "./Routes.php";
