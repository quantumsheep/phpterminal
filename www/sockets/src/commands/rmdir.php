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
            $sender->send("message|<br>Operand missing <br>please enter rm --help for more information");
            return;
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $paramArray = explode(" ", $parameters);

        foreach ($quotedParameters as $quoted) {
            if ($quoted != "" || !empty($quoted)) {
                $type = CommandAsset::checkBoth($terminal_mac, $quoted, CommandAsset::getIdDirectory($db, $terminal_mac, $data->position), $db);

                if ($type == 1) {
                    $parentId = CommandAsset::getParentId($db, $terminal_mac, CommandAsset::getAbsolute($quoted));
                    if (strpos($quoted, '/') !== false) {
                        $quoted = explode("/", $quoted);

                        $quoted = end($quoted);
                    }

                    var_dump($parentId);
                    var_dump($quoted);
                    self::deleteDir($db, $data, $sender, $terminal_mac, $quoted, $parentId);
                } else if ($type == 2) {
                    $sender->send('message|<br>' . $quoted . ' is a file, please use rm.');
                } else {
                    $sender->send('message|<br>' . $quoted . ' didnt exist.');
                }
            }
        }

        foreach ($paramArray as $param) {
            if ($param != "" || !empty($param)) {
                //Get parent information for further treatment
                $parentPath = CommandAsset::getParentPath($param);
                $parentName = explode("/", $parentPath)[count(explode("/", $parentPath)) - 1];

                //Check if you've righ to act on directory
                if (CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $parentPath, CommandAsset::getChmod($db, $terminal_mac, $parentName, CommandAsset::getParentId($db, $terminal_mac, $parentPath)), 1)) {

                    $parentId = CommandAsset::getParentId($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $param));
                    $type = CommandAsset::checkBoth($terminal_mac, $param, $parentId, $db);

                    if (strpos($param, '/') !== false) {
                        $param = explode("/", $param)[count(explode("/", $param)) - 1];

                    }
                    if ($type == 1) {
                        self::deleteDir($db, $data, $sender, $terminal_mac, $param, $parentId);
                    } else if ($type == 2) {
                        $sender->send('message|<br>' . $param . ' is a file, please use rm.');
                    }
                    
                } else {
                    $sender->send("message|<br> You don't have rights to remove directory here " . $parentName . ".");
                }
            }
        }
    }

    public static function deleteDir(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $dirname, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_directory WHERE terminal = :terminal AND parent = :parent AND name = :name");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $dirname);

        $stmp->execute();

        $stmp = $db->prepare("SELECT name FROM terminal_directory WHERE terminal= :terminal AND parent= :parent AND name= :name");

        //If the file or the dir exist, delete the file
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $dirname);

        $stmp->execute();
        if ($stmp->fetch()['name']) {
            $sender->send("message|<br> Directory not empty.");
        };
    }
}
