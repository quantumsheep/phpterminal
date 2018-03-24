<?php
namespace Alph\Managers;

class NetworkManager
{
    /**
     * Create a new network
     */
    public static function createNetwork(\PDO $db)
    {
        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO network (mac, ipv4, ipv6) VALUES (:mac, :ipv4, :ipv6);");

        // Pre-define errorCode
        $errorCode = 0;

        // Pre-define SQL query response
        $response = false;

        // Do one time and loop if errorCode is key duplicate (for MAC, IPv4 or IPv6 address duplication)
        do {
            // Try to execute the query, if not catch the error
            try {
                // Generate a new mac address
                $mac = NetworkManager::generateMac();

                // Generate a new public IPv4 address
                $ipv4 = NetworkManager::generatePublicIPv4();

                // Generate a new public IPv6 address                
                // $ipv6 = NetworkManager::generatePublicIPv6();
                $ipv6 = "1ff0";

                // Execute the SQL query with prepared parameters
                $response = $stmp->execute([
                    ":mac" => $mac,
                    "ipv4" => $ipv4,
                    "ipv6" => $ipv6
                ]);
            } catch (\PDOException $e) {
                // Get the error code
                $errorCode = $e->errorInfo[1];
            }
        } while ($errorCode == 1062);

        // Return false if the query wasn't executed right
        if(!$response) {
            return false;
        }

        // Return the network's mac address
        return $mac;
    }

    /**
     * Assign a new private IP to a terminal in a specific network
     * 
     * @param string $network Network's mac address
     * @param string $terminal Terminal's mac address
     */
    public static function assignPrivateIP(\PDO $db, string $network, string $terminal) {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT ip FROM PRIVATEIP WHERE network = :network ORDER BY ip DESC LIMIT 1;");

        // Bind the query parameters
        $stmp->bindParam(":network", $network);
        $stmp->bindParam(":terminal", $terminal);

        // Execute the SQL command
        $stmp->execute();

        // Pre-define ip
        $ip;

        // Check if there's one IP in the SQL query (limited at one row)
        if($stmp->rowCount() == 1) {
            // Get the ip address from query's selected row
            $ip = $stmp->fetch()["ip"];

            // Check if the maximum IP has been reached (192.168.255.254)
            if($ip == "192.168.255.254") {
                return false;
            }

            // Split the ip into 4 logical parts
            $iparr = explode('.', $ip);

            // Check if IP's last part is 255 (the limit)
            if($iparr[3] == 255) {
                // Increment IP's third part
                $iparr[2]++;

                // Define IP's last part to 1               
                $iparr[3] = 1;
            } else {
                // Increment IP's last part
                $iparr[3]++;
            }

            $ip = "192.168." . $iparr[2] . "." . $iparr[3];
        } else {
            // Define the IP to the minimum address assignable
            $ip = "192.168.0.2";
        }
        
        // Prepare the SQL row insert
        $stmp = $db->prepare("INSERT INTO PRIVATEIP (network, terminal, ip) VALUES (:network, :terminal, :ip) ON DUPLICATE KEY UPDATE ip = :ip;");

        // Bind the query parameters
        $stmp->bindParam(":network", $network);
        $stmp->bindParam(":terminal", $terminal);
        $stmp->bindParam(":ip", $ip);

        // Execute the SQL command and return the boolean result
        return $stmp->execute();
    }

    /**
     * Generate a new MAC address
     */
    public static function generateMac(): string
    {
        // Pre-define mac address
        $mac = "";

        // Loop 5 times
        for ($i = 0; $i < 5; $i++, $mac .= ":") {
            // Add a MAC address part
            $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);
        }

        // Add the MAC address last part (to ignore the ':')
        $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);

        // Return the new generated MAC address
        return $mac;
    }

    /**
     * Generate a new public IPv4
     */
    public static function generatePublicIPv4(): string
    {
        // Pre-define ip
        $ip = "";

        // Define a random part (to escape private IP)
        $part = rand(0, 2);

        // Select the IP's first numbers
        switch ($part) {
            case 0:
                $ip .= rand(1, 9);
                break;
            case 1:
                $ip .= rand(11, 126);
                break;
            case 2:
                $ip .= rand(129, 191);
                break;
        }

        // Add random parts to the IP
        $ip .= "." . rand(0, 254) . "." . rand(0, 254) . "." . rand(2, 254);

        // Return the new generated IP address
        return $ip;
    }

    /**
     * Generate a new public IPv6
     */
    public static function generatePublicIPv6(): string
    {

    }

    public static function isMAC(string $str) {
        return \preg_match("/^([0-9A-F]{2}[:-]){5}[0-9A-F]{2}$/i", $str) === 1 ? true : false;
    }

    public static function formatMAC(string $mac) {
        return str_replace(['.', '-', ':'], '-', $mac);
    }
}
