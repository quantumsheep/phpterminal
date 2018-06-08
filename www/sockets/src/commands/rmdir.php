<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class rmdir implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "rmdir [OPTION]... DIRECTORY...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Remove the DIRECTORY(ies), if they are empty.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/rm>
    or available locally via: info '(coreutils) rm invocation'";

    /**
     * Command's exit status
     */
    const EXIT_STATUS = "Returns exit status of command or success if command is null.";

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
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter rmdir --help for more information");
            return;
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        // Change simple parameters into array for further treatement
        $Files = explode(" ", $parameters);
        if (!empty($Files)) {
            $Files = CommandAsset::fullPathFromParameters($Files, $data->position);
        }
        CommandAsset::concatenateParameters($Files, $pathParameters, $quotedParameters);
        return CommandAsset::stageDeleteFiles($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $Files, 'dir');
    }
}
