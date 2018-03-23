<?php
namespace Alph\Controllers;

class ErrorController {
    public static function e404(array $errorCode) {
        return "Page not found.";
    }

    public static function e403(array $errorCode) {
        return "Access refused.";
    }
}