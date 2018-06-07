<?php
namespace Alph\Services;

use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

interface CommandInterface
{
    /**
     * Call the command
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn);
}
