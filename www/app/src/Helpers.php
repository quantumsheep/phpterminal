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
