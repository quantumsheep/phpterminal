<?php
namespace Alph\Managers;

use Alph\Managers\NetworkManager;

class TerminalManager
{
    public static function createTerminal(\PDO $db, int $accountid)
    {
        $stmp = $db->prepare("INSERT INTO terminal (mac, account, localnetwork) VALUES()");
    }
}
