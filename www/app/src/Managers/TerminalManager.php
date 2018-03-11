<?php
namespace Alph\Managers;

use Alph\Managers\NetworkManager;

class TerminalManager
{
    public static function createTerminal(\PDO $db, int $accountid) {
        $stmp = $db->prepare("INSERT INTO network (mac, ipv4, ipv6) VALUES (:mac, :ipv4, ipv6);");

        try {
            $mac = NetworkManager::generateMac();            
            $ipv4 = NetworkManager::generatePublicIPv4();
            $ipv6 = NetworkManager::generatePublicIPv6();

            $stmp->execute([$mac, $ipv4, $ipv6]);
        } catch(\PDOException $e) {
            
        }

        
        $stmp = $db->prepare("INSERT INTO terminal (mac, account, localnetwork) VALUES()");
    }
}
