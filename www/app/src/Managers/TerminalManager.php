<?php
namespace Alph\Managers;

use Alph\Models\TerminalModel;

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

        var_dump($stmp->errorInfo());

        // Execute the query and return the response (boolean)
        return $stmp->execute();
    }

    public static function getTerminal(\PDO $db, string $mac)
    {
        $stmp = $db->prepare("SELECT account, localnetwork FROM TERMINAL WHERE mac = :mac;");

        $stmp->bindParam(":mac", $mac);

        $stmp->execute();

        if ($stmp->rowCount() == 1) {
            $terminal = new TerminalModel();
            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

            $terminal->mac = $mac;
            $terminal->account = $row["account"];
            $terminal->localnetwork = $row["localnetwork"];

            return $terminal;
        }

        return false;
    }
}
