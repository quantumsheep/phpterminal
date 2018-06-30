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
                self::mkdirDOption($db, $data, $terminal_mac, $pathParameters);
                $NewDirectories = CommandAsset::concatenateParameters($newDirectories, $quotedParameters);
                return self::stageCreateNewDirectories($db, $data, $sender, $terminal_mac, $newDirectories);
            }

        }

        CommandAsset::concatenateParameters($newDirectories, $pathParameters, $quotedParameters);
        return self::stageCreateNewDirectories($db, $data, $sender, $terminal_mac, $newDirectories);
    }

    /**
     * Generate new directories from array of Full Paths
     */
    public static function stageCreateNewDirectories(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, $fullPathNewDirectories)
    {
        foreach ($fullPathNewDirectories as $fullPathNewDirectory) {
            // get Full Path of Parent directory
            $parentId = CommandAsset::getParentId($db, $terminal_mac, $fullPathNewDirectory);

            if ($parentId != null) {
                // Get name from created directory
                $newDirectoryName = explode("/", $fullPathNewDirectory)[count(explode("/", $fullPathNewDirectory)) - 1];

                // Check if directory already exists
                if (CommandAsset::checkDirectoryExistence($terminal_mac, $newDirectoryName, $parentId, $db) === false && CommandAsset::checkFileExistence($terminal_mac, $newDirectoryName, $parentId, $db) === false) {
                    // Create directory
                    self::createNewDirectory($db, $data, $terminal_mac, $newDirectoryName, $parentId);
                } else {

                    $sender->send("message|<br>" . $newDirectoryName . " : already exists");
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    /**
     * generate a new directory
     */
    public static function createNewDirectory(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId)
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_DIRECTORY(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
    }

    /**
     * Automatically generate directory if it doesn't exist
     * -d's mkdir option
     */
    public static function mkdirDOption(\PDO $db, SenderData &$data, string $terminal_mac, $fullPathParameters)
    {
        foreach ($fullPathParameters as $fullPathParameter) {
            $parentId = 1;
            $parentPath = "";
            // Get whole directory name
            $directorySplited = explode("/", $fullPathParameter);
            array_shift($directorySplited);

            foreach ($directorySplited as $directoryName) {
                if (CommandAsset::checkDirectoryExistence($terminal_mac, $directoryName, $parentId, $db) === false) {
                    self::createNewDirectory($db, $data, $terminal_mac, $directoryName, $parentId);
                }
                $parentPath = $parentPath . "/" . $directoryName;
                $parentId = CommandAsset::getIdDirectory($db, $terminal_mac, $parentPath);
            }
        }

    }
}
