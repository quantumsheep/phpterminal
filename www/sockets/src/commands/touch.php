<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class touch implements CommandInterface
{
    const USAGE = "touch [OPTION]... [FILE]...";

    const SHORT_DESCRIPTION = "touch - change file timestamps";

    const FULL_DESCRIPTION = " Update the access and modification times of each FILE to the current time.
    A FILE argument that does not exist is created empty, unless -c or -his supplied.
    A FILE argument string of - is handled specially and causes touch to change the times of the file associated with standard output.
    Mandatory arguments to long options are  mandatory  for  short  options too.";

    const OPTIONS = [
        "-a" => "change only the access time",
        "-c" => "--no-create do not create any files",
        "-d" => "--date=STRING parse STRING and use it instead of current time",
        "-f" => "(ignored)",
        "-h" => "--no-dereference affect each symbolic link instead of any referenced file (useful only on systems that can change the timestamps of a symlink)",
        "-m" => "change only the modification time",
        "-r" => "--reference=FILE use this file's times instead of current time",
        "-t" => "STAMP use [[CC]YY]MMDDhhmm[.ss] instead of current time",
        "--time=WORD" => "change the specified time: WORD is access, atime, or use: equiv‐ alent to -a WORD is modify or mtime: equivalent to -m",
        "--help" => "display this help and exit",
        "--version" => "output version information and exit",
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
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter touch --help for more information");
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

        CommandAsset::concatenateParameters($newFiles, $pathParameters, $quotedParameters);

        return self::stageCreateNewFiles($db, $data, $sender, $terminal_mac, $newFiles);
    }

    public static function stageCreateNewFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $fullPathNewFile, string $content = ""): bool
    {
        // get Full Path of Parent directory
        $parentId = CommandAsset::getParentId($db, $terminal_mac, $fullPathNewFile);
        $parentPath = CommandAsset::getParentPath($fullPathNewFile);
        $parentName = explode("/", $parentPath)[count(explode("/", $parentPath)) - 1];
        if (CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $parentPath, CommandAsset::getChmod($db, $terminal_mac, $parentName, CommandAsset::getParentId($db, $terminal_mac, $parentPath)), 2)) {
            if ($parentId != null) {
                // Get name from created file
                $splitedPath = explode("/", $fullPathNewFile);
                $newFileName = $splitedPath[count($splitedPath) - 1];

                // Check if file already exists
                if (CommandAsset::checkDirectoryExistence($terminal_mac, $newFileName, $parentId, $db) === false && CommandAsset::checkFileExistence($terminal_mac, $newFileName, $parentId, $db) === false) {

                    // Create file
                    return self::createNewFile($db, $data, $terminal_mac, $newFileName, $parentId, $content);
                } else if (CommandAsset::checkDirectoryExistence($terminal_mac, $newFileName, $parentId, $db) === true || CommandAsset::checkFileExistence($terminal_mac, $newFileName, $parentId, $db) === true) {
                    $sender->send("message|<br>" . $newFileName . " : already exists");
                    return false;
                } else {
                    return false;
                }
            } else {
                $sender->send("message|<br> Path not found");
                return false;
            }
        } else {
            $sender->send("message|<br>You don't have rights to create new file in this directory " . $parentName . ".");
            return false;
        }

    }

    /**
     * Full stage of creating new files
     */
    public static function stageCreateNewFiles(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, array $fullPathNewFiles)
    {
        foreach ($fullPathNewFiles as $fullPathNewFile) {
            self::stageCreateNewFile($db, $data, $sender, $terminal_mac, $fullPathNewFile);
        }
    }

    /**
     * generate a new File
     */
    public static function createNewFile(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId, string $content = ""): bool
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, `data`, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :content, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":content", $content);
        $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        return
        $stmp->execute();
    }
}
