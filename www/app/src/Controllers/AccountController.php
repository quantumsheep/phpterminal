<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Models\AccountModel;
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

    public static function accountOption(array $params)
    {
        $view = (new View("account/accountOption"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function accountOption_modify(array $params)
    {
        $db = Database::connect();

        if (empty(AccountManager::checkAccountLogin($_SESSION["account"]->email, $_POST["oldPassword"]))) {
            $account = new AccountModel();

            if ($_POST["email"] != null) {
                $account->email = $_POST["email"];
            } else {
                $account->email = $_SESSION["account"]->email;
            }
            if ($_POST["username"] != null) {
                $account->username = $_POST["username"];
            } else {
                $account->username = $_SESSION["account"]->username;
            }
            if ($_POST["password"] != null || $_POST["newPasswordVerif"] != null || $_POST["password"] != $_POST["newPasswordVerif"]) {
                $account->password = $_POST["password"];
            } else {
                $account->password = $_SESSION["account"]->email;
            }

            $res = AccountManager::editAccount($db, $_SESSION["account"]->idaccount, $account);

            if (!$res) {
                header("Location: /");
            }
        }
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
                if ($network_mac = NetworkManager::createNetwork($db)) {
                    if (TerminalManager::createTerminal($db, $idaccount, $network_mac)) {
                        AccountManager::removeValidationCode($db, $params["code"]);

                        $_SESSION["success"] = [
                            "You have successfully validate your account !",
                        ];
                    }
                }
            }
        } else {
            $_SESSION["errors"] = [
                "Your validation code was not correct.",
            ];
        }

        $return();
    }

    public static function signupaction(array $params)
    {
        $db = Database::connect();

        $_SESSION["errors"] = AccountManager::checkAccountRegister($db, $_POST["username"], $_POST["email"], $_POST["password"], $_POST["password2"]);

        if (!empty($_SESSION["errors"])) {
            $_SESSION["data"]["username"] = $_POST["username"];
            $_SESSION["data"]["email"] = $_POST["email"];

            header("Location: /signup");
            return;
        }

        $account_code = AccountManager::createAccount($db, $_POST["username"], $_POST["email"], $_POST["password"]);

        if ($account_code !== false) {
            $mail = new Mail($db, "Account validation", "Please validate your email at this link: <a href=\"" . SITE_PROTOCOL . SITE_ADRESS . "/validate/" . $account_code . "\">Click here</a>.", [$_POST["email"]]);
            $mail->send();

            $_SESSION["success"] = [
                "You will receipt a validation email soon, please confirm it!",
            ];
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

            return header("Location: /signin");
        }

        if (!AccountManager::identificateAccount($db, $_POST["email"], $_POST["password"])) {
            $_SESSION["data"]["email"] = $_POST["email"];
            $_SESSION["errors"] = [
                "You have entered an invalid email or password.",
            ];

            return header("Location: /signin");
        }

        header("Location: /");
    }
}
