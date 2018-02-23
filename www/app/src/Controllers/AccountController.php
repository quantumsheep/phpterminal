<?php
namespace Alph\Controllers;

class AccountController {
    public static function connect($params) {
        return (new View("account/connect"))->render();
    }

    public static function signup($params) {
        return (new View("account/signup"))->render();
    }
}