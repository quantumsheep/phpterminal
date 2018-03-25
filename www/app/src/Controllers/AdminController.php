<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Models\AdminModels\AdminTerminalListModel;
use Alph\Models\AdminModels\AdminNetworkListModel;
use Alph\Models\AdminModels\AdminAccountListModel;
use Alph\Services\Database;
use Alph\Managers\AccountManager;

class AdminController
{
    public static function index(array $params)
    {
        return (new View("admin/index"))->render();
    }

    public static function terminal(array $params)
    {
        $db = Database::connect();
        $model = new AdminTerminalListModel();
        $model->terminals = [];
        $model->accounts = [];

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $model->terminals[] = TerminalManager::getTerminal($db, $params["mac"]);

            if(empty($model->terminals[0])) {
                return header("Location: /admin/terminal");
            }

            $model->accounts[] = AccountManager::getAccountById($db, $model->terminals[0]->account);

            \setcookie("terminal", $params["mac"], 0, "/");
            return (new View("admin/terminal_edit", $model))->render();
        } else {
            $model->terminals = TerminalManager::getTerminals($db) ?? [];

            $accountids = [];

            foreach($model->terminals as &$terminal) {
                $accountids[] = $terminal->account;
            }

            $model->accounts = AccountManager::getAccountsById($db, $accountids);

            return (new View("admin/terminal_list", $model))->render();
        }
    }

    public static function network(array $params) {
        $db = Database::connect();
        $model = new AdminNetworkListModel();

        $model->networks = NetworkManager::getNetworks($db);

        return (new View("admin/network_list", $model))->render();        
    }

    public static function account(array $params) {
        $db = Database::connect();
        $model = new AdminAccountListModel();

        $model->accounts = AccountManager::getAccounts($db);

        $idaccounts = [];

        foreach($model->accounts as &$account) {
            $idaccounts[] = $account->idaccount;
        }

        $model->terminalsCount = TerminalManager::countTerminalsByAccounts($db, $idaccounts);

        return (new View("admin/user_list", $model))->render();        
    }
}
