<?php
// Reduce the DIRECTORY_SEPARATOR to DS
define("DS", DIRECTORY_SEPARATOR);

// Define the DIR_ROOT constant that link to the root of the project
define("DIR_ROOT", dirname(dirname(dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName()))) . DS);

define("DB_HOST", "localhost");
define("DB_PORT", "9956");
define("DB_USER", "root");
define("DB_PASS", "koala");
define("DB_NAME", "alph");