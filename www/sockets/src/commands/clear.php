<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\DefinedCommands;
use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

class clear implements CommandInterface
{
    /**
     * Call the command
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool $lineReturn)
    {
        $sender->send("action|clear");
    }
}
