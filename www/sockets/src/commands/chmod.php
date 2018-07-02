<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

/**
 * chmod
 */
class chmod implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "chmod [MODE] [FILE].";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Change the mode of each FILE to MODE.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/chmod>
    or available locally via: info '(coreutils) chmod invocation'";

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
        $allowedFiles = [];
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter chmod --help for more information");
            return;
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        // Change simple parameters into array for further treatement
        $Files = explode(" ", $parameters);
        // Remove potential bug on the Files explode array
        for ($i = 0; $i < count($pathParameters); $i++) {
            array_shift($Files);
        }

        CommandAsset::concatenateParameters($Files, $quotedParameters, $pathParameters);
        var_dump($Files);

        for ($i = 0; $i < count($options); $i++) {
            unset($Files[$i]);
        }

        $askedChmod = $Files[count($options)];
        unset($Files[count($options)]);
        var_dump($Files);
        var_dump($askedChmod);

        if (is_numeric($askedChmod)) {
            if (!empty($Files)) {
                $Files = CommandAsset::fullPathFromParameters($Files, $data->position);
            }
            CommandAsset::concatenateParameters($Files, $pathParameters, $quotedParameters);
            if (empty($options)) {
                foreach ($Files as $file) {

                    if (CommandAsset::isRoot($db, $terminal_mac, $data->user->idterminal_user) || CommandAsset::getElementOwner($db, $terminal_mac, $file, explode("/", $file)[count(explode("/", $file)) - 1]) == $data->user->idterminal_user) {
                        $allowedFiles[] = $file;

                    } else {
                        $sender->send("message|<br>You can't change rights on a directory or a file you don't possess.");
                    }
                }
                if (!empty($allowedFiles)) {
                    return self::stageChangeChmod($db, $data, $sender, $terminal_mac, $Files, $askedChmod);
                }
            }
        } else {
            $sender->send("message|<br>chmod: missing operand after ‘" . $askedChmod . "’<br>Try 'chmod --help' for more information.");
        }
    }

    public static function stageChangeChmod(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, $fullPathFiles, int $askedChmod)
    {
        foreach ($fullPathFiles as $fullPathFile) {
            // get Full Path of Parent directory
            $parentId = CommandAsset::getParentId($db, $terminal_mac, $fullPathFile);

            if ($parentId != null) {
                // Get name from created file
                $FileName = explode("/", $fullPathFile)[count(explode("/", $fullPathFile)) - 1];

                // Check if file exists
                if (CommandAsset::checkDirectoryExistence($terminal_mac, $FileName, $parentId, $db) === false && CommandAsset::checkFileExistence($terminal_mac, $FileName, $parentId, $db) === false) {
                    $sender->send("message|<br>" . $FileName . " : didn't exists");
                } else {
                    self::changeChmod($db, $data, $terminal_mac, $FileName, $askedChmod, $parentId);
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    /**
     *
     */
    public static function changeChmod(\PDO $db, SenderData &$data, string $terminal_mac, string $FileName, int $askedChmod, int $parentId)
    {

        $stmp = $db->prepare("UPDATE terminal_file SET chmod= :chmod WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        $stmp->bindParam(":chmod", $askedChmod);
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();

        $stmp = $db->prepare("UPDATE terminal_directory SET chmod= :chmod WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        $stmp->bindParam(":chmod", $askedChmod);
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
    }
}
