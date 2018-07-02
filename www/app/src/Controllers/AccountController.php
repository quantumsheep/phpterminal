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

    public static function account(array $params)
    {
        $view = (new View("account/account"))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["data"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function account_modify(array $params)
    {
        $db = Database::connect();
        $account = new AccountModel();

        if (!empty($_POST["email"] || $_POST["email"] != null)) {

            $account->email = $_POST["email"];
            $_SESSION["errors"] = AccountManager::checkAccountRegister($db, $_SESSION["account"]->username . 2, $account->email, $_POST["oldPassword2"], $_POST["oldPassword2"]);

            if (empty($_SESSION["errors"])) {
                $res = AccountManager::editAccount($db, $_SESSION["account"]->idaccount, $account);
                $_SESSION["success"] = ["You have successfuly changed your mail."];
            }

        } else if (!empty($_POST["username"] || $_POST["username"] != null)) {

            if (strlen($_POST["username"]) < 3) {
                $_SESSION["errors"] = ["The username must contains 3 characters minimum."];
            } else if (!empty(AccountManager::usernameExist($db, $_POST["username"]))) {
                $_SESSION["errors"] = ["This username is already used."];
            } else {
                $account->username = $_POST["username"];
                $res = AccountManager::editAccount($db, $_SESSION["account"]->idaccount, $account);

                $_SESSION["success"] = ["You have successfuly changed your username."];
            }

        } else if ($_POST["password"] != null || $_POST["newPasswordVerif"] != null || $_POST["password"] == $_POST["newPasswordVerif"] || !empty($_POST["password"]) || !empty($_POST["newPasswordVerif"])) {

            if (empty(AccountManager::checkAccountLogin($_SESSION["account"]->email, $_POST["oldPassword"]))) {

                $_SESSION["errors"] = AccountManager::checkAccountRegister($db, $_SESSION["account"]->username . 2, $_SESSION["account"]->email . 'r', $_POST["password"], $_POST["newPasswordVerif"]);

                if (empty($_SESSION["errors"])) {
                    $account->password = \password_hash($_POST["password"], PASSWORD_BCRYPT);
                    $res = AccountManager::editAccount($db, $_SESSION["account"]->idaccount, $account);

                    $_SESSION["success"] = ["You have successfuly changed your password."];
                }
            }
        }

        header("Location: /account");
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
            $mail = new Mail($db, "alPH - Account validation", "Please validate your email at this link: <a href=\"" . SITE_PROTOCOL . SITE_ADRESS . "/validate/" . $account_code . "\">Click here</a>.", [$_POST["email"]]);
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
