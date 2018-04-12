<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\DefinedCommands;
use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

class hello implements CommandInterface
{
    const USAGE = "help [-dms] [pattern ...]";

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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $answer=["What's alPH Nigga ?","Yo yo yo yo","Bonjour","Oui","Non","sometimes","sisi","non","Amusant, humain","Do what you know, cause a Pirate's free","I'm not world. I'm you","Is this real life ?","Don't talk to me","jajajajajaja","Gaggagaga","rofl","Julio","Yo yo yo yo","Bonjour","Oui","Non","sometimes","sisi","non","What's alPH Nigga ?","Yo yo yo yo","Bonjour","Oui","Non","sometimes","sisi","non","What's alPH Nigga ?","Yo yo yo yo","Bonjour","Oui","Non","sometimes","sisi","non",];
        $choice = 0;
        $count = strlen($cmd);
        for($i = 0;$i<strlen($cmd);$i++){
            $choice = $choice + ord($cmd[$i])%47;
        }
        $sender->send("World : ".$answer[$choice]);
    }
}
