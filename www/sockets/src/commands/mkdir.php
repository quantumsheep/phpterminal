<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class mkdir implements CommandInterface
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
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
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
        var_dump($parameters);
        $newDirectories = explode(" ", $parameters);
        if(!empty($newDirectories)){
            $newDirectories = CommandAsset::fullPathFromParameters($newDirectories, $data->position);
        }
        var_dump($newDirectories);
        

        if (!empty($options)) {
            if (!null(\array_count_values($options["d"])) && \array_count_values($options)["d"] > 0) {
                CommandAsset::mkdirDOption($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $pathParameters);
                $NewDirectories = array_merge($newDirectories, $quotedParameters);
            }
        }
        var_dump($newDirectories);
        CommandAsset::concatenateParameters($newDirectories, $pathParameters, $quotedParameters);
        var_dump($newDirectories);
        return CommandAsset::stageCreateNewDirectories($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $newDirectories);

        // Get parameters from

        /*
    // Get parameters

    foreach ($paramList as $name) {

    // Get actual directory ID
    $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
    $getIdDirectory->bindParam(":mac", $terminal_mac);
    $getIdDirectory->bindParam(":paths", $data->position);
    $getIdDirectory->execute();
    $CurrentDir = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["id"];

    $pathlist = explode('/', $name);

    $name = $pathlist[count($pathlist) - 1];

    // Prepare
    $stmp = $db->prepare("INSERT INTO terminal_directory(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

    // Bind parameters put in SQL
    $stmp->bindParam(":terminal", $terminal_mac);
    $stmp->bindParam(":parent", $CurrentDir);
    $stmp->bindParam(":name", $name);
    $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
    $stmp->bindParam(":owner", $data->user->idterminal_user);
    -
    $stmp->execute();

    }
     */
    }
}
