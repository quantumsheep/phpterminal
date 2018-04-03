<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Services\Database;
use Alph\Models\Model;

class TerminalController
{
    public static function terminal_list(array $params) {
        if (empty($_SESSION["account"]["idaccount"])) {
            return header("Location: /signin");
        }
        
        $db = Database::connect();
        $model = new Model();

        $model->terminals = TerminalManager::getTerminalsByAccount($db, $_SESSION["account"]["idaccount"]);

        return (new View("terminal_list", $model))->render();        
    }
    
    public static function terminal(array $params)
    {
        if (empty($_SESSION["account"]["idaccount"])) {
            return header("Location: /signin");
        }

        if (empty($params["mac"]) || !NetworkManager::isMAC($params["mac"])) {
            return header("Location: /terminal");
        }

        $db = Database::connect();

        $params["mac"] = NetworkManager::formatMAC($params["mac"]);

        $terminal = TerminalManager::getTerminal($db, $params["mac"]);

        \setcookie("terminal", $params["mac"], 0, "/");

        return (new View("terminal"))->render();
    }
}
