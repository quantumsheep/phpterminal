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
    const USAGE = "rm [OPTION]... [FILE]...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Remove (unlink) the FILE(s).";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "By default, rm does not remove directories.  Use the --recursive (-r or -R)
    option to remove each listed directory, too, along with all of its contents.

    To remove a file whose name starts with a '-', for example '-foo',
    use one of these commands:
      rm -- -foo

      rm ./-foo

    Note that if you use rm to remove a file, it might be possible to recover
    some of its contents, given sufficient expertise and/or time.  For greater
    assurance that the contents are truly unrecoverable, consider using shred.

    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/rm>
    or available locally via: info '(coreutils) rm invocation'";

    /**
     * Command's options
     */
    const OPTIONS = [
        "-f, --force" => "ignore nonexistent files and arguments, never prompt",
        "-i" => "prompt before every removal",
        "-I" => "prompt once before removing more than three files, or
        when removing recursively; less intrusive than -i,
        while still giving protection against most mistakes
--interactive[=WHEN]  prompt according to WHEN: never, once (-I), or
        always (-i); without WHEN, prompt always
--one-file-system  when removing a hierarchy recursively, skip any
        directory that is on a file system different from
        that of the corresponding command line argument
--no-preserve-root  do not treat '/' specially
--preserve-root   do not remove '/' (default)",
        "-r, -R, --recursive" => "remove directories and their contents recursively",
        "-d, --dir" => "remove empty directories",
        "-v" => "explain what is being done",
        "--help" => "display this help and exit",
        "--version" => "output version information and exit",
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
                $parentPath = CommandAsset::getParentPath($param);
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
                        self::deleteFile($db, $data, $sender, $terminal_mac, $param, $parentId);
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

    public static function deleteFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $filename, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_file WHERE terminal = :terminal AND parent = :parent AND name = :name");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $filename);

        return $stmp->execute();
    }
}
