<?php
namespace Alph\Managers;

class NetworkManager
{
    public static function createNetwork(\PDO $db)
    {
        $stmp = $db->prepare("INSERT INTO network (mac, ipv4, ipv6) VALUES (:mac, :ipv4, ipv6);");
        $errorCode = 0;
        $response = false;

        do {
            try {
                $mac = NetworkManager::generateMac();
                $ipv4 = NetworkManager::generatePublicIPv4();
                // $ipv6 = NetworkManager::generatePublicIPv6();
                $ipv6 = "";

                $response = $stmp->execute([$mac, $ipv4, $ipv6]);
            } catch (\PDOException $e) {
                $errorCode = $e->errorInfo[1];
            }
        } while ($errorCode == 1062);

        return $mac;
    }

    public static function generateMac(): string
    {
        $mac = "";

        for ($i = 0; $i < 5; $i++, $mac .= ":") {
            $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);
        }

        $mac .= base_convert(rand(0, 15), 10, 16) . base_convert(rand(0, 15), 10, 16);

        return $mac;
    }

    public static function generatePublicIPv4(): string
    {
        $ip = "";
        $class = rand(0, 2);

        switch ($class) {
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

        $ip .= "." . rand(0, 254);
        $ip .= "." . rand(0, 254);
        $ip .= "." . rand(3, 254);

        return $ip;
    }

    public static function generatePublicIPv6(): string
    {

    }
}
