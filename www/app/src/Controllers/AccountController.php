<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Services\Database;
use Alph\Services\Mail;

class AccountController
{
    public static function signup(array $params)
    {
        $view = (new View("account/signup"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function signin(array $params)
    {
        $view = (new View("account/signin"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function logout(array $params)
    {
        AccountManager::logout();

        header("Location: /");
    }

    public static function validate(array $params)
    {
        $return = function () {
            header("Location: /signin");
        };

        if (strlen($params["code"]) != 100) {
            $return();
            return false;
        }

        $db = Database::connect();

        $idaccount = AccountManager::getAccountIdFromCode($db, $params["code"]);

        if ($idaccount !== false) {
            if (AccountManager::validateAccount($db, $idaccount)) {
                $network_mac = NetworkManager::createNetwork($db);

                if ($network_mac !== false) {
                    if (TerminalManager::createTerminal($db, $idaccount, $network_mac)) {
                        AccountManager::removeValidationCode($db, $params["code"]);
                        $_SESSION["success"] = [];
                        $_SESSION["success"][] = "You have successfully validate your account !";
                    }
                }
            }
        }else{
            $_SESSION["errors"] = [];
            $_SESSION["errors"][] = "Your validation code was not correct.";
        }

        $return();
    }

    public static function signupaction(array $params)
    {
        $db = Database::connect();

        $_SESSION["errors"] = AccountManager::checkAccountRegister($db, $_POST["username"], $_POST["email"], $_POST["password"]);

        if (!empty($_SESSION["errors"])) {
            $_SESSION["data"]["username"] = $_POST["username"];
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signup");
            return;
        }

        $result = AccountManager::createAccount($db, $_POST["username"], $_POST["email"], $_POST["password"]);

        if ($result !== false) {
            $mail = new Mail($db, "Account validation", "Please validate your email at this link: <a href=\"" .
                sprintf("%s://%s:%s/validate/%s",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME'],
                    $_SERVER["SERVER_PORT"],
                    $result) .
                "\">Click here</a>.", [$_POST["email"]]);
            $mail->send();

            $_SESSION["success"] = [];
            $_SESSION["success"][] = "You will receipt a validation mail soon, please confirm it !";
        }

        header("Location: /signin");
    }

    public static function signinaction(array $params)
    {
        $db = Database::connect();

        $_SESSION["validation"] = AccountManager::checkAccountLogin($_POST["email"], $_POST["password"]);
        $_SESSION["errors"] = AccountManager::checkAccountLogin($_POST["email"], $_POST["password"]);

        if (!empty($_SESSION["errors"])) {
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signin");
            return;
        }

        if (!AccountManager::identificateAccount($db, $_POST["email"], $_POST["password"])) {
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signin");
            return;
        }

        header("Location: /");
    }
}
