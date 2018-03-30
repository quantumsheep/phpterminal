<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Models\Model;
use Alph\Services\Database;

class AdminController
{
    public static function index(array $params)
    {
        return (new View("admin/index"))->render();
    }

    public static function terminal(array $params)
    {
        $db = Database::connect();
        $model = new Model();
        $model->terminals = [];
        $model->accounts = [];

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $params["mac"] = NetworkManager::formatMACForDatabase($params["mac"]);

            $model->terminals[] = TerminalManager::getTerminal($db, $params["mac"]);

            if (empty($model->terminals[0])) {
                return header("Location: /admin/terminal");
            }

            $model->accounts[] = AccountManager::getAccountById($db, $model->terminals[0]->account);

            \setcookie("terminal", $params["mac"], 0, "/");
            return (new View("admin/terminal_edit", $model))->render();
        } else {
            $model->terminals = TerminalManager::getTerminals($db) ?? [];

            $accountids = [];

            foreach ($model->terminals as &$terminal) {
                $accountids[] = $terminal->account;
            }

            $model->accounts = AccountManager::getAccountsById($db, $accountids);

            return (new View("admin/terminal_list", $model))->render();
        }
    }

    public static function network(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $model->network = NetworkManager::getNetwork($db, $params["mac"]);

            $model->terminals = TerminalManager::getTerminalsByNetwork($db, $params["mac"]);
            
            return (new View("admin/network_edit", $model))->render();            
        } else {
            $model->networks = NetworkManager::getNetworks($db);

            return (new View("admin/network_list", $model))->render();
        }
    }

    public static function account(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if (!empty($params["idaccount"])) {
            $model->account = AccountManager::getAccountById($db, $params["idaccount"]);

            $model->terminals = TerminalManager::getTerminalsByAccount($db, $params["idaccount"]);

            return (new View("admin/user_edit", $model))->render();
        } else {
            $model->numberAccounts = AccountManager::countAccounts($db, !empty($_GET["search"]) ? $_GET["search"] : null);

            $model->accounts = AccountManager::getAccounts($db, 10, $_GET["page"] ?? null !== null ? ($_GET["page"] - 1) * 10 : 0, !empty($_GET["search"]) ? $_GET["search"] : null);

            if ($model->accounts) {
                $idaccounts = [];

                foreach ($model->accounts as &$account) {
                    $idaccounts[] = $account->idaccount;
                }

                $model->terminalsCount = TerminalManager::countTerminalsByAccounts($db, $idaccounts);
            }

            return (new View("admin/user_list", $model))->render();
        }
    }
}
