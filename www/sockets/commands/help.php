<?php
namespace Alph\Commands;
 
use Ratchet\ConnectionInterface;

class help {
    /**
     * Call the command
     * 
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, ConnectionInterface $sender, string $sess_id, string $cmd) {
        $sender->send("Availables commands:");
        $sender->send("- help");
        $sender->send("The number of current openned terminals is " . count($clients));
    }
}