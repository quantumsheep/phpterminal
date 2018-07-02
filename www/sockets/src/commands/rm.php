<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class rm implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "rm [FILE]...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Remove (unlink) the FILE(s).";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "By default, rm does not remove directories

    To remove a file whose name starts with a ' ', for example 'fo o',
    use one of these commands:
      rm 'fo o'

    Note that if you use rm to remove a file, it might be possible to recover
    some of its contents, given sufficient expertise and/or time.

    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
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
            $sender->send("message|<br>Operand missing <br>please enter rm --help for more information");
            return;
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $paramArray = explode(" ", $parameters);

        foreach ($quotedParameters as $quoted) {
            if ($quoted != "" || !empty($quoted)) {
                $type = CommandAsset::checkBoth($terminal_mac, $quoted, CommandAsset::getIdDirectory($db, $terminal_mac, $data->position), $db);

                if ($type == 2) {
                    $parentId = CommandAsset::getParentId($db, $terminal_mac, CommandAsset::getAbsolute($quoted));
                    if (strpos($quoted, '/') !== false) {
                        $quoted = explode("/", $quoted);

                        $quoted = end($quoted);
                    }

                    self::deleteFile($db, $data, $sender, $terminal_mac, $quoted, $parentId);
                } else if ($type == 1) {
                    $sender->send('message|<br>' . $quoted . ' is a directory, please use rmdir.');
                } else {
                    $sender->send('message|<br>' . $quoted . ' didnt exist.');
                }
            }
        }

        foreach ($paramArray as $param) {
            if ($param != "" || !empty($param)) {
                //Get parent information for further treatment
                $paramFullPath = CommandAsset::getAbsolute($data->position, $param);
                $paramName = $param;
                $parentPath = CommandAsset::getParentPath($paramFullPath);
                $parentName = explode("/", $parentPath)[count(explode("/", $parentPath)) - 1];

                //Check if you've righ to act on directory
                if (CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $parentPath, CommandAsset::getChmod($db, $terminal_mac, $parentName, CommandAsset::getParentId($db, $terminal_mac, $parentPath)), 1)) {

                    $parentId = CommandAsset::getParentId($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $param));
                    $type = CommandAsset::checkBoth($terminal_mac, $param, $parentId, $db);

                    if (strpos($param, '/') !== false) {
                        $param = explode("/", $param);

                        $param = end($param);
                    }

                    if ($type == 2) {
                        self::deleteFile($db, $data, $sender, $terminal_mac, $paramName, $parentId, $param);
                    } else if ($type == 1) {
                        $sender->send('message|<br>' . $param . ' is a directory, please use rmdir.');
                    } else {
                        $sender->send('message|<br>' . $param . ' didnt exist.');
                    }
                } else {
                    $sender->send("message|<br>You can't remove a file from here " . $parentPath);
                }
            }
        }
    }

    public static function deleteFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $filename, int $parentId, string $fileFullPath)
    {
        if (CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $fileFullPath, CommandAsset::getChmod($db, $terminal_mac, $filename, $parentId), 2)) {
            $stmp = $db->prepare("DELETE FROM terminal_file WHERE terminal = :terminal AND parent = :parent AND name = :name");

            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":parent", $parentId);
            $stmp->bindParam(":name", $filename);

            return $stmp->execute();
        } else {
            return $sender->send("message|<br>You can't remove a file you can't write.");
        }
    }
}
