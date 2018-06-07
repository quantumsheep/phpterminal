<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;
use Alph\Services\CommandAsset;

class test implements CommandInterface
{
    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     */

    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool $lineReturn)
    {
        CommandAsset::mkdirDOption($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $parameters);

    }
}
