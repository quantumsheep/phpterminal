<?php
namespace Alph\Managers;

use Alph\Models\TerminalModel;
use Alph\Models\ViewTerminal_InfoModel;

class TerminalManager
{
    /**
     * Create a new terminal
     *
     * @param int $idaccount Owner id
     * @param string $localnetwork_mac Network's mac address
     */
    public static function createTerminal(\PDO $db, int $idaccount, string $localnetwork_mac)
    {
        // Prepare SQL row insert
        $stmp = $db->prepare("CALL NewTerminal(:account, :localnetwork_mac);");

        $stmp->bindParam(":account", $idaccount);
        $stmp->bindParam(":localnetwork_mac", $localnetwork_mac);

        // Execute the query
        if ($stmp->execute()) {
            if ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                // Get the terminal_mac from the stored procedure
                return $row["@terminal_mac"];
            }
        }

        return false;
    }

    public static function getTerminal(\PDO $db, string $mac)
    {
        $stmp = $db->prepare("SELECT mac, account, localnetwork FROM TERMINAL WHERE mac = :mac;");

        $stmp->bindParam(":mac", $mac);

        $stmp->execute();

        if ($stmp->rowCount() == 1) {
            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

            return TerminalModel::map($row);
        }

        return false;
    }

    public static function getTerminalInfo(\PDO $db, string $mac)
    {
        $stmp = $db->prepare("SELECT terminalmac, networkmac, privateipv4, publicipv4, sshport FROM TERMINAL_INFO WHERE terminalmac = :mac;");

        $stmp->bindParam(":mac", $mac);

        $stmp->execute();

        if ($stmp->rowCount() == 1) {
            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

            return ViewTerminal_InfoModel::map($row);
        }

        return false;
    }

    public static function countTerminalsByAccounts(\PDO $db, array $idaccounts)
    {
        $terminalCount = [];

        $stmp = $db->prepare("SELECT COUNT(*) as c FROM TERMINAL WHERE account = :account;");

        foreach ($idaccounts as &$idaccount) {
            $stmp->bindParam(":account", $idaccount);

            $stmp->execute();

            if ($row = $stmp->fetch()) {
                $terminalCount[$idaccount] = $row["c"];
            } else {
                $terminalCount[$idaccount] = 0;
            }
        }

        return $terminalCount;
    }

    public static function getTerminalsByAccount(\PDO $db, string $idaccount)
    {
        $stmp = $db->prepare("SELECT mac, account, localnetwork FROM TERMINAL WHERE account = :idaccount;");

        $stmp->bindParam(":idaccount", $idaccount);

        $stmp->execute();

        $terminals = [];

        if ($stmp->rowCount() > 0) {
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $terminals[] = TerminalModel::map($row);
            }

            return $terminals;
        }

        return $terminals;
    }

    public static function getTerminalsByNetwork(\PDO $db, string $network_mac)
    {
        $stmp = $db->prepare("SELECT mac, account, localnetwork FROM TERMINAL WHERE localnetwork = :localnetwork;");

        $stmp->bindParam(":localnetwork", $network_mac);

        $stmp->execute();

        $terminals = [];

        if ($stmp->rowCount() > 0) {
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $terminals[] = TerminalModel::map($row);
            }

            return $terminals;
        }

        return $terminals;
    }

    public static function getTerminals(\PDO $db, int $limit = 10, int $offset = 0)
    {
        $sql = "SELECT mac, account, localnetwork FROM TERMINAL";

        $isOffset = $offset != null;
        $isLimited = $limit != null;

        if ($isOffset && $isLimited) {
            $sql .= " LIMIT :offset, :limit";
        } else if ($isLimited) {
            $sql .= " LIMIT :limit";
        } else if ($isOffset) {
            $sql .= " OFFSET :offset";
        }

        $stmp = $db->prepare($sql);

        if ($isOffset) {
            $stmp->bindParam(":offset", $offset);
        }

        if ($isLimited) {
            $stmp->bindParam(":limit", $limit, \PDO::PARAM_INT);
        }

        $stmp->execute();

        $terminals = [];

        if ($stmp->rowCount() > 0) {
            while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $terminals[] = TerminalModel::map($row);
            }

            return $terminals;
        }

        return $terminals;
    }
}
