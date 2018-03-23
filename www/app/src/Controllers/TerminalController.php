<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Services\Database;

class TerminalController
{
    public static function index(array $params)
    {
        if (empty($_SESSION["account"]["idaccount"])) {
            return header("Location: /signin");
        }

        if (!NetworkManager::isMAC($params["mac"])) {
            return header("Location: /");
        }

        $db = Database::connect();

        $params["mac"] = NetworkManager::formatMAC($params["mac"]);

        $terminal = TerminalManager::getTerminal($db, $params["mac"]);

        \setcookie("terminal", $params["mac"]);

        return (new View("terminal"))->render();
    }
}
