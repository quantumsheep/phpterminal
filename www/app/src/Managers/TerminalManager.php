<?php
namespace Alph\Managers;

use Alph\Managers\NetworkManager;

class TerminalManager
{
    public static function createTerminal(\PDO $db, int $idaccount, string $localnetwork_mac)
    {
        $stmp = $db->prepare("INSERT INTO terminal (mac, account, localnetwork) VALUES(:mac, :account, :localnetwork_mac)");
        $errorCode = 0;
        
        do {
            try {
                $mac = NetworkManager::generateMac();

                $response = $stmp->execute([
                    ":mac" => $mac,
                    ":account" => $idaccount,
                    ":localnetwork_mac" => $localnetwork_mac
                ]);
            } catch (\PDOException $e) {
                $errorCode = $e->errorInfo[1];
            }
        } while ($errorCode == 1062);

        return $response;
    }
}
