<?php
// Reduce the DIRECTORY_SEPARATOR to DS
define("DS", DIRECTORY_SEPARATOR);

// Define the DIR_ROOT constant that link to the root of the project
define("DIR_ROOT", dirname(dirname(dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName()))) . DS);

// Define the DIR_SRC constant that link to src directory
define("DIR_SRC", DIR_ROOT . "src" . DS);

// Define the DIR_ASSETS constant that link to src directory
define("DIR_ASSETS", DIR_ROOT . "assets" . DS);

// Define the DIR_VIEWS constant that link to the views directory
define("DIR_VIEWS", DIR_ROOT . "views" . DS);

// Define the DIR_BLADE_CACHE constant that define the cache to store Blade files
define("DIR_BLADE_CACHE", DIR_ROOT . "cache" . DS);

define("DB_HOST", "localhost");
define("DB_PORT", "9956");
define("DB_USER", "root");
define("DB_PASS", "koala");
define("DB_NAME", "alph");