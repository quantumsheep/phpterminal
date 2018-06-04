<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;
use Alph\Services\CommandAsset;

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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        //Parameters variable
        $params = "";

        // If no params send an error message
        if (empty($parameters)) {
            $sender->send("rm: missing file operand, Try 'rm --help' for more information.");
            return;
        } else {
            //Get all the parameters with quotes in one array
            preg_match_all("/\"[^\"]*\"/", $parameters, $quotedParams);

            //If parameters with quotes are existing
            if (!empty($quotedParams[0])) {

                //Create a temporary array of all the quoted parameters
                for ($i = 0; $i < sizeof($quotedParams); $i++) {
                    $tmp[$i] = $quotedParams[0][$i];
                }

                //Delete all quoted parameters in the complete parameters list
                foreach ($tmp as $value) {
                    $parameters = str_replace($value, "", $parameters);
                }

                //Replace all the spaces in complete parameters list by an illegal file name character
                $parameters = str_replace(' ', "*", $parameters);

                //Adding back all the quoted parameters in the entire parameters list
                foreach ($tmp as $value) {
                    $parameters .= $value;
                }

                //Remove all the quotes
                $parameters = str_replace('"', "", $parameters);
            } else {
                //Replace all the spaces in complete parameters list by an illegal file name character
                $parameters = str_replace(' ', "*", $parameters);
            }

            //For each parameters
            foreach ($paramList as $fileName) {

                //If there's '/' in the parameter, get the actual position directory ID
                $paths = $data->position;
                //If there's no '/' in the parameter, get the parameter directory ID
                if (strstr($fileName, "/")) {
                    $paths = Helpers::getAbsolute($data->position, $fileName, "..");
                }

                $getDirId = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
                $getDirId->bindParam(":mac", $terminal_mac);
                $getDirId->bindParam(":paths", $paths);
                $getDirId->execute();
                $CurrentDir = $getDirId->fetch(\PDO::FETCH_ASSOC)["id"];

                //Split the parameters after the path set by the user in an array
                $pathlist = explode('/', $fileName);

                //Get the file name set by the user (logically the last string in the actual parameter)
                $fileName = $pathlist[count($pathlist) - 1];

                //Check if a file or a dir with the same name of the desired string exist in the BDD
                $getFileDirRecurence = $db->prepare("SELECT name FROM terminal_file where name= :name AND parent= :parent");
                $getFileDirRecurence->bindParam(":name", $fileName);
                $getFileDirRecurence->bindParam(":parent", $CurrentDir);
                $getFileDirRecurence->execute();
                $fileExist = $getFileDirRecurence->fetch();

                // Prepare
                $stmp1 = $db->prepare("DELETE FROM terminal_file WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

                //If the file or the dir didn't exist, delete the file
                $stmp1->bindParam(":terminal", $terminal_mac);
                $stmp1->bindParam(":parent", $CurrentDir);
                $stmp1->bindParam(":name", $name);
                $stmp1->bindParam(":owner", $data->user->idterminal_user);

                $stmp1->execute();
            }
        }
    }

}
