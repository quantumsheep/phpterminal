<?php
/**
 * Check if a variable is set and return it, else return the default value
 */
function ifsetor(&$variable, $default = null)
{
    if (isset($variable)) {
        $tmp = $variable;
    } else {
        $tmp = $default;
    }
    
    return $tmp;
}

function csrf_token() {
    if(empty($_SESSION["token"])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf-token" value="' . $_SESSION["token"] . '">';
}

function randomAlphanumeric(int $length) {
    // Define the allowed characters
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

    // Get the characters string length
    $characters_length = strlen($characters);

    // Pre-define rand_str to an empty string
    $rand_str = "";

    // Loop x times
    for ($i = 0; $i < $length; $i++) {
        // Add a random character to the random string
        $rand_str .= $characters[rand(0, $characters_length - 1)];
    }

    return $rand_str;
}