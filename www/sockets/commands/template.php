<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

/**
 * template = the name of the commands
 */
class template implements CommandInterface
{
    /**
     * The usage of the command
     */
    const USAGE = "";

    /**
     * The short descriptin of the command
     */
    const SHORT_DESCRIPTION = "";

    /**
     * The full description of the command
     */
    const FULL_DESCRIPTION = "";

    /**
     * All the options of the command
     */
    const OPTIONS = [
        "" => "",
        "" => "",
    ];

    /**
     * The arguments of the command
     */
    const ARGUMENTS = [
        "PATTERN" => "",
    ];

    /**
     * The exit status of the command
     */
    const EXIT_STATUS = "";

    /**
     * Call the command
     * Define the algorithm of the command
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {

    }
}
