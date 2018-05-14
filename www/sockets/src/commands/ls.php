<?php
namespace Alph\Commands;

use Alph\Services\Helpers;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class ls implements CommandInterface
{
    const USAGE = "ls";

    const SHORT_DESCRIPTION = "";
    const FULL_DESCRIPTION = "";

    const OPTIONS = [
    ];

    const EXIT_STATUS = "";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd   ²
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(':Path', ':Mac')");
        $getIdDirectory->bindParam(":Mac", $terminal_mac);
        $getIdDirectory->bindParam(":Path", $data->position);
        $getIdDirectory->execute();
        $Path=$getIdDirectory->fetchAll();

        $getFiles = $db->prepare("SELECT * FROM TERMINAL_FILE WHERE terminal=':Mac' AND parent=':Parent'");
        $getFiles->bindParam(":Mac", $terminal_mac);
        $getFiles->bindParam(":Parent", $Path);
        $getFiles->execute();
        $files=$getFiles->fetchAll();

        $getDirs = $db->prepare("SELECT * FROM TERMINAL_FILE WHERE terminal=':Mac' AND parent=':Parent'");
        $getDirs->bindParam(":Mac", $terminal_mac);
        $getDirs->bindParam(":Parent", $Path);
        $getDirs->execute();
        $dirs=$getDirs->fetchAll();

        foreach($files as $file){
            $sender->send("message|file : ".$file);
        }
        
        foreach($dirs as $dir){
            $sender->send("message|dir : ".$dir);
        }
    }
}
