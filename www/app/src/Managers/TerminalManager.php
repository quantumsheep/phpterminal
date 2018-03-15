<?php
namespace Alph\Managers;

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
        $stmp = $db->prepare("INSERT INTO terminal (mac, account, localnetwork) VALUES(:mac, :account, :localnetwork_mac)");

        // Pre-define errorCode
        $errorCode = 0;

        // Do one time and loop if errorCode is key duplicate (for MAC address duplication)
        do {
            // Try to execute the query, if not catch the error
            try {
                // Generate a new mac address
                $mac = NetworkManager::generateMac();

                // Execute the SQL query with prepared parameters
                $response = $stmp->execute([
                    ":mac" => $mac,
                    ":account" => $idaccount,
                    ":localnetwork_mac" => $localnetwork_mac
                ]);
            } catch (\PDOException $e) {
                // Get the error code
                $errorCode = $e->errorInfo[1];
            }
        } while ($errorCode == 1062);

        // Returned an array with the boolean response and the terminal's mac address
        return [
            "response" => $response,
            "mac" => $mac
        ];
    }
}
