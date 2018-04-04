<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\AdminManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\TerminalManager;
use Alph\Managers\ReferencialManager;
use Alph\Models\Model;
use Alph\Services\Database;

class AdminController
{
    public static function index(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        $accountCreatedData = AdminManager::getAccountCreatedByDate($db);

        $model->accountCreatedDataDates = "";
        $model->accountCreatedDataNumbers = "";

        foreach ($accountCreatedData as $date => &$number) {
            $model->accountCreatedDataDates .= '"' . $date . '",';
            $model->accountCreatedDataNumbers .= $number . ',';
        }

        return (new View("admin/index", $model))->render();
    }

    public static function terminal(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $params["mac"] = NetworkManager::formatMACForDatabase($params["mac"]);

            $model->terminal = TerminalManager::getTerminal($db, $params["mac"]);

            if (empty($model->terminal)) {
                return header("Location: /admin/terminal");
            }

            $model->account = AccountManager::getAccountById($db, $model->terminal->account);

            \setcookie("terminal", $params["mac"], 0, "/");
            return (new View("admin/terminal/terminal_edit", $model))->render();
        } else {
            $model->terminals = TerminalManager::getTerminals($db) ?? [];

            $accountids = [];

            foreach ($model->terminals as &$terminal) {
                $accountids[] = $terminal->account;
            }

            $model->accounts = AccountManager::getAccountsById($db, $accountids);

            return (new View("admin/terminal/terminal_list", $model))->render();
        }
    }

    public static function terminal_add(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $_SESSION["errors"] = [];
            $_SESSION["success"] = [];

            if (!empty($_POST["account"]) && isset($_POST["network"])) {

                if (empty($_POST["network"])) {
                    $_POST["network"] = NetworkManager::createNetwork($db);

                    if (empty($_POST["network"])) {
                        $_SESSION["errors"][] = "An error occured while creating a new network.";
                        return header("Location: /admin/terminal/add?account=" . $_POST["account"]);
                    }
                } else if (!NetworkManager::isMAC($_POST["network"])) {
                    $_SESSION["errors"][] = "The selected network is unvalid.";
                    return header("Location: /admin/terminal/add?account=" . $_POST["account"]);
                }

                if ($terminal_mac = TerminalManager::createTerminal($db, $_POST["account"], $_POST["network"])) {
                    $_SESSION["success"][] = "Terminal <a href=\"/admin/terminal/" . $terminal_mac . "\">" . $terminal_mac . "</a> created for account <a href=\"/admin/account/" . $_POST["account"] . "\">" . $_POST["account"] . "</a>" . " in network <a href=\"/admin/network/" . $_POST["network"] . "\">" . $_POST["network"] . "</a>";

                    return header("Location: /admin/terminal/add");
                } else {
                    $_SESSION["errors"][] = "An error occured while creating the new terminal.";
                    return header("Location: /admin/terminal/add?account=" . $_POST["account"] . "&network=" . $_POST["network"]);
                }
            } else {
                $_SESSION["errors"][] = "Thanks to complete the form.";
                return header("Location: /admin/terminal/add?account=" . ($_POST["account"] ?? null) . "&network=" . ($_POST["network"] ?? null));
            }
        }

        $model->networks = NetworkManager::getNetworks($db, null, null);
        $model->accounts = AccountManager::getAccounts($db, null, null);

        $view = (new View("admin/terminal/terminal_add", $model))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function network(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if (!empty($params["mac"]) && NetworkManager::isMAC($params["mac"])) {
            $model->network = NetworkManager::getNetwork($db, $params["mac"]);

            $model->terminals = TerminalManager::getTerminalsByNetwork($db, $params["mac"]);

            return (new View("admin/network/network_edit", $model))->render();
        } else {
            $model->networks = NetworkManager::getNetworks($db);

            return (new View("admin/network/network_list", $model))->render();
        }
    }

    public static function account(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        if (!empty($params["idaccount"])) {
            $model->account = AccountManager::getAccountById($db, $params["idaccount"]);

            $model->terminals = TerminalManager::getTerminalsByAccount($db, $params["idaccount"]);

            return (new View("admin/account/account_edit", $model))->render();
        } else {
            $model->numberAccounts = AccountManager::countAccounts($db, !empty($_GET["search"]) ? $_GET["search"] : null);

            $offset = 0;

            if (!empty($_GET["page"]) && \is_numeric($_GET["page"]) && $_GET["page"] > 1) {
                $offset = ($_GET["page"] - 1) * 10;
            }

            $model->accounts = AccountManager::getAccounts($db, 10, $offset, !empty($_GET["search"]) ? $_GET["search"] : null);

            if ($model->accounts) {
                $idaccounts = [];

                foreach ($model->accounts as &$account) {
                    $idaccounts[] = $account->idaccount;
                }

                $model->terminalsCount = TerminalManager::countTerminalsByAccounts($db, $idaccounts);
            }

            return (new View("admin/account/account_list", $model))->render();
        }
    }

    public static function referential(array $params)
    {
        $db = Database::connect();
        $model = new Model();

        $model->referentialCategories = ReferencialManager::getReferencialCategories($db);

        return (new View("admin/referential/referential_list", $model))->render();
    }

    public static function referential_add(array $params) {
        $db = Database::connect();
        $model = new Model();
        
        return (new View("admin/referential/referential_add", $model))->render();        
    }
}
