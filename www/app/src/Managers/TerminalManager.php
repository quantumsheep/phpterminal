<?php
namespace Alph\Managers;

use Alph\EntityModels\RowTerminal;
use Alph\Managers\NetworkManager;

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

        // Execute the query and return the response (boolean)
        return $stmp->execute();
    }

    public static function getTerminal(\PDO $db, string $mac)
    {
        $stmp = $db->prepare("SELECT account, localnetwork FROM TERMINAL WHERE mac = :mac;");

        $stmp->bindParam(":mac", $mac);

        $stmp->execute();

        if ($stmp->rowCount() == 1) {
            $terminal = new RowTerminal();
            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

            $terminal->mac = $mac;
            $terminal->account = $row["account"];
            $terminal->localnetwork = $row["localnetwork"];

            return $terminal;
        }

        return false;
    }

    public static function countTerminalsByAccounts(\PDO $db, array $idaccounts) {
        $terminalCount = [];

        $stmp = $db->prepare("SELECT COUNT(*) as c FROM TERMINAL WHERE account = :account;");

        foreach($idaccounts as &$idaccount) {
            $stmp->bindParam(":account", $idaccount);

            $stmp->execute();

            if($row = $stmp->fetch()) {
                $terminalCount[$idaccount] = $row["c"];
            } else {
                $terminalCount[$idaccount] = 0;
            }
        }

        return $terminalCount;
    }

    public static function getTerminals(\PDO $db, int $limit = 10, int $offset = 0) {
        $sql = "SELECT mac, account, localnetwork FROM TERMINAL";

        $isOffset = $offset != null;
        $isLimited = $limit != null;

        if($isOffset && $isLimited) {
            $sql .= " LIMIT :offset, :limit";
        } else if($isLimited) {
            $sql .= " LIMIT :limit";
        } else if ($isOffset) {
            $sql .= " OFFSET :offset";
        }

        $stmp = $db->prepare($sql);

        if($isOffset) {
            $stmp->bindParam(":offset", $offset);
        }

        if($isLimited) {
            $stmp->bindParam(":limit", $limit, \PDO::PARAM_INT);
        }

        $stmp->execute();

        if($stmp->rowCount() > 0) {
            $terminals = [];

            while($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
                $terminal = new RowTerminal();

                $terminal->mac = NetworkManager::formatMAC($row["mac"]);
                $terminal->account = $row["account"];
                $terminal->localnetwork = $row["localnetwork"];

                $terminals[] = $terminal;
            }

            return $terminals;
        }

        return false;
    }
}
