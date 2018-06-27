<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class mkdir implements CommandInterface
{
    const USAGE = "mkdir [OPTION]... DIRECTORY...";

    const SHORT_DESCRIPTION = "Create the DIRECTORY(ies), if they do not already exist.";
    const FULL_DESCRIPTION = "Create the DIRECTORY(ies), if they do not already exist. If paths are provided, do NOT create directory if the path provided is wrong.";

    const OPTIONS = [
        "-p" => "Create directory from paths provided in case of the directories doesn't exist",
    ];

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

        $basicmod = 777;

        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter mkdir --help for more information");
            return;
        }
        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        // Change simple parameters into array for further treatement
        $newDirectories = explode(" ", $parameters);
        
        if(!empty($newDirectories)){
            $newDirectories = CommandAsset::fullPathFromParameters($newDirectories, $data->position);
        }
        
        if (!empty($options)) {
            if (\in_array("p", $options)) {
                CommandAsset::mkdirDOption($db, $data, $terminal_mac, $pathParameters);
                $NewDirectories = CommandAsset::concatenateParameters($newDirectories, $quotedParameters);
                return CommandAsset::stageCreateNewDirectories($db, $data, $sender, $terminal_mac, $newDirectories);
            }

        }

        CommandAsset::concatenateParameters($newDirectories, $pathParameters, $quotedParameters);
        return CommandAsset::stageCreateNewDirectories($db, $data, $sender, $terminal_mac, $newDirectories);
    }
}
