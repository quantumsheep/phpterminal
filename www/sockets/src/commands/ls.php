<?php
namespace Alph\Commands;

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
        $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
        $getIdDirectory->bindParam(":mac", $terminal_mac);
        $getIdDirectory->bindParam(":paths", $data->position);
        $getIdDirectory->execute();
        $Path = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["id"];

        var_dump($Path[0]);

        if ($Path[0] = "NULL") {
            $getFiles = $db->prepare("SELECT name FROM TERMINAL_FILE WHERE terminal=:mac AND parent IS NULL");
            $getFiles->bindParam(":mac", $terminal_mac);
            $getFiles->execute();
            $files = $getFiles->fetchAll(\PDO::FETCH_COLUMN);

            var_dump($files);

            $getDirs = $db->prepare("SELECT name FROM TERMINAL_DIRECTORY WHERE terminal=:mac AND parent IS NULL");
            $getDirs->bindParam(":mac", $terminal_mac);
            $getDirs->execute();
            $dirs = $getDirs->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $getFiles = $db->prepare("SELECT name FROM TERMINAL_FILE WHERE terminal=:mac AND parent=:parent");
            $getFiles->bindParam(":mac", $terminal_mac);
            $getFiles->bindParam(":parent", $Path[0]);
            $getFiles->execute();
            $files = $getFiles->fetchAll(\PDO::FETCH_COLUMN);

            $getDirs = $db->prepare("SELECT name FROM TERMINAL_DIRECTORY WHERE terminal=:mac AND parent=:parent");
            $getDirs->bindParam(":mac", $terminal_mac);
            $getDirs->bindParam(":parent", $Path[0]);
            $getDirs->execute();
            $dirs = $getDirs->fetchAll(\PDO::FETCH_COLUMN);
        }

        foreach ($files as $file) {
            $sender->send("message|<br>file : " . $file);
        }

        foreach ($dirs as $dir) {
            $sender->send("message|<br>dir : " . $dir);
        }
    }
}
