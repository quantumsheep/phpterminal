<?php
namespace Alph\Services;

use Ratchet\ConnectionInterface;

interface CommandInterface
{
    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, string $parameters);
}
