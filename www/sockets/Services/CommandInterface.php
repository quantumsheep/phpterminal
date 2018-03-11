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
    public static function call(\PDO $db, \SplObjectStorage $clients, ConnectionInterface $sender, string $sess_id, string $cmd, string $parameters);
}
