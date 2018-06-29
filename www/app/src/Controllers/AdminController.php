<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Managers\AccountManager;
use Alph\Managers\AdminManager;
use Alph\Managers\NetworkManager;
use Alph\Managers\ReferentialManager;
use Alph\Managers\TerminalManager;
use Alph\Models\Model;
use Alph\Services\Database;

class AdminController
{
    public static function index(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

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
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

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
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();
        $model = new Model();

        $model->networks = NetworkManager::getNetworks($db, null, null);
        $model->accounts = AccountManager::getAccounts($db, null, null);

        $view = (new View("admin/terminal/terminal_add", $model))->render();

        unset($_SESSION["errors"]);
        unset($_SESSION["success"]);

        return $view;
    }

    public static function terminal_add_action(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();

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

    public static function network(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

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
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

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
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();
        $model = new Model();

        $model->idreferencial = !empty($params["idreferential"]) ? intval($params["idreferential"]) : null;

        if (!empty($model->idreferencial)) {
            $model->referential = ReferentialManager::getReferential($db, $model->idreferencial);

            if (!empty($model->referential)) {
                $model->referentialParentName = ReferentialManager::getReferentialCategoryCode($db, $model->referential->category);
            }
        }

        $model->referentials = ReferentialManager::getReferentials($db, $model->idreferencial);

        return (new View("admin/referential/referential_list", $model))->render();
    }

    public static function referential_add(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();
        $model = new Model();

        $model->referentials = ReferentialManager::getReferentials($db);

        return (new View("admin/referential/referential_add", $model))->render();
    }

    public static function referential_add_action(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();

        $_SESSION["errors"] = [];
        $_SESSION["success"] = [];
        if (isset($_POST["type"]) && isset($_POST["code"])) {
            // Redefining category

            if (!empty($_POST["category"]) && ($_POST["type"] === "0" || $_POST["type"] === "1")) {
                $_POST["category"] = intval($_POST["category"]);
            } else {
                $_POST["category"] = null;
            }

            // Checking value's value
            if (empty($_POST["value"]) || $_POST["type"] === "0") {
                $_POST["value"] = null;
            }

            ReferentialManager::createReferential($db, intval($_POST["type"]), $_POST["code"], $_POST["category"], $_POST["value"]);
        }

        header("Location: ");
    }

    public static function referential_edit(array $params)
    {
        if (!AccountManager::isConnected() || !AccountManager::isAdmin()) {
            return header('Location: /');
        }

        $db = Database::connect();

        if (!empty($params["idreferential"])) {
            if (isset($_POST["value"])) {
                ReferentialManager::updateValue($db, intval($params["idreferential"]), $_POST["value"]);
            }
        }

        header("Location: ");
    }
}
