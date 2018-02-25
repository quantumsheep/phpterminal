<?php
namespace Alph\Controllers;

class AccountController
{
    public static function logon($params)
    {
        $_SESSION["hi"] = "test done";
        var_dump($_SESSION);
        return (new View("account/logon"))->render();
    }
}
