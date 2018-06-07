<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

/**
 * template = the name of the commands
 */
class mv implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "mv [OPTION]... [-T] SOURCE DEST
    or:  mv [OPTION]... SOURCE... DIRECTORY
    or:  mv [OPTION]... -t DIRECTORY SOURCE...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.

    Mandatory arguments to long options are mandatory for short options too.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.";

    /**
     * Command's options
     */
    const OPTIONS = [
        "-b" => "make a backup of each existing destination file, like --backup but does not accept an argument",
        "-f, --force" => "do not prompt before overwriting",
        "-i, --interactive" => "prompt before overwrite",
        "-n, --no-clobber" => "do not overwrite an existing file",
    ];

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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter mv --help for more information");
            return;
        }
        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        // Change simple parameters into array for further treatement
        $newFiles = explode(" ", $parameters);
        if (!empty($newFiles)) {
            $newFiles = CommandAsset::fullPathFromParameters($newFiles, $data->position);
        }

        if (!empty($options)) {
            if (!null(\array_count_values($options["d"])) && \array_count_values($options)["d"] > 0) {
                CommandAsset::mkdirDOption($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $pathParameters);
                $newFiles = array_merge($newFiles, $quotedParameters);
            }
        }
        CommandAsset::concatenateParameters($newFiles, $pathParameters, $quotedParameters);
        return CommandAsset::stageCreateNewFiles($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $newFiles);
            }
        }
    }
}
