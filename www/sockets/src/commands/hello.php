<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\DefinedCommands;
use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

class hello implements CommandInterface
{
    const USAGE = "hello";

    const SHORT_DESCRIPTION = "Display information about builtin commands.";
    const FULL_DESCRIPTION = "Displays brief summaries of builtin commands.  If PATTERN is specified, gives detailed help on all commands matching PATTERN, otherwise the list of help topics is printed.";

    const OPTIONS = [
        "-d" => "output short description for each topic",
        "-s" => "output only a short usage synopsis for each topic matching PATTERN",
    ];

    const ARGUMENTS = [
        "PATTERN" => "Pattern specifiying a help topic",
    ];

    const EXIT_STATUS = "Returns exit status of command or success if command is null.";

    /**
     * Call the command
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        $data->count = 0;
        $data->count++;
        var_dump($data->count);
        $sender->send("message|hello");

        return;
    }
}
