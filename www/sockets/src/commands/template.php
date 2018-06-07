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
     * Command's usage
     */
    const USAGE = "";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "";

    /**
     * Command's options
     */
    const OPTIONS = [
        "" => "",
        "" => "",
    ];

    /**
     * Command's arguments
     */
    const ARGUMENTS = [
        "PATTERN" => "",
    ];

    /**
     * Command's exit status
     */
    const EXIT_STATUS = "";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {

    }
}
