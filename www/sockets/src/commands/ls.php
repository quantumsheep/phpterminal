<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class ls implements CommandInterface
{
    const USAGE = "cd [-L|[-P [-e]] [-@]] [dir]";

    const SHORT_DESCRIPTION = "Change the shell working directory.";
    const FULL_DESCRIPTION = "Change the current directory to DIR.  The default DIR is the value of the HOME shell variable.
    <br>
    The variable CDPATH defines the search path for the directory containing
    DIR.  Alternative directory names in CDPATH are separated by a colon (:).
    A null directory name is the same as the current directory.  If DIR begins
    with a slash (/), then CDPATH is not used.
    <br>
    If the directory is not found, and the shell option `cdable_vars' is set,
    the word is assumed to be  a variable name.  If that variable has a value,
    its value is used for DIR.";

    const OPTIONS = [
        "-L" => "force symbolic links to be followed: resolve symbolic links in DIR after processing instances of `..'",
        "-P" => "use the physical directory structure without following symbolic links: resolve symbolic links in DIR before processing instances of `..'",
        "-e" => "if the -P option is supplied, and the current working directory cannot be determined successfully, exit with a non-zero status",
        "-@" => "on systems that support it, present a file with extended attributes as a directory containing the file attributes",
    ];

    const EXIT_STATUS = "Returns 0 if the directory is changed, and if \$PWD is set successfully when -P is used; non-zero otherwise.";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd   ²
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $idDirectory = null;
        $jump = 0;
        // Get name of relative position directory
        if ($data->position == '/') {
            $positionDir = null;
        } else {
            $position = explode("/", $data->position);
            $positionDir = $position[count($position) - 1];
        }

        if (empty($parameters)) {
            // Get actual directory ID
            if ($positionDir != null) {
                $getIdDirectory = $db->prepare("SELECT iddir FROM TERMINAL_DIRECTORY WHERE name = :daddy");
                $getIdDirectory->bindParam(":daddy", $positionDir);
                if ($getIdDirectory->execute()) {
                    if ($getIdDirectory->rowCount() > 0) {
                        $idDirectory = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["iddir"];
                    }
                }
            }

            // Fetch directories and file in the actual Directory
            $stmp = $db->prepare("SELECT name FROM terminal_directory WHERE parent = :daddy");
            $stmp->bindParam(":daddy", $idDirectory);
            if ($stmp->execute()) {
                if ($stmp->rowCount() > 0) {
                    $fetchedDirectories = $stmp->fetchAll(\PDO::FETCH_ASSOC);
                    if (!empty($fetchedDirectories)) {
                        for ($i = 0; $i < count($fetchedDirectories); $i++, $jump++) {
                            if ($jump % 4 == 0) {
                                $sender->send("message|<br>");
                            }
                            $sender->send("message|" . $fetchedDirectories[$i]["name"] . "&emsp;");
                        }
                    }
                }
            }
        }
    }
}
