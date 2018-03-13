<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Services\Database;

class AccountController
{
    public static function signup(array $params)
    {
        $view = (new View("account/signup"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);

        return $view;
    }

    public static function signin(array $params)
    {
        $view = (new View("account/signin"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);

        return $view;
    }

    public static function signupaction(array $params) {
        $db = Database::connect();

        $_SESSION["errors"] = AccountManager::checkUserRegister($db, $_POST["username"], $_POST["email"], $_POST["password"]);

        if(!empty($_SESSION["errors"])) {
            $_SESSION["data"]["username"] = $_POST["username"];
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signup");
            return;
        }

        AccountManager::createUser($db, $_POST["username"], $_POST["email"], $_POST["password"]);
    }

    public static function signinaction(array $params) {
        $db = Database::connect();
        
        $_SESSION["errors"] = AccountManager::checkUserLogin($db, $_POST["email"], $_POST["password"]);

        if(!empty($_SESSION["errors"])) {
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signin");
            return;
        }

        AccountManager::connectUser($db, $_POST["email"], $_POST["password"]);
    }
}
