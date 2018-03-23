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