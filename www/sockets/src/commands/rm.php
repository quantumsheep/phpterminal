<?php
namespace Alph\Commands;

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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $basicmod = 777;
        $params = "";
        $positionDir = "KO";
        $dataFile = "";
        $sended_path = "";

        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Opérande manquant<br>Saisissez touch --help pour plus d'information");
            return;
        } else {

            preg_match_all("/\"[^\"]*\"/", $parameters, $quotedParams);

            if (!empty($quotedParams[0])) {

                for ($i = 0; $i < sizeof($quotedParams); $i++) {
                    $tmp[$i] = $quotedParams[0][$i];
                }

                foreach ($tmp as $value) {
                    $parameters = str_replace($value, "", $parameters);
                }

                $parameters = str_replace(' ', "*", $parameters);

                foreach ($tmp as $value) {
                    $parameters .= $value;
                }

                $parameters = str_replace('"', "", $parameters);
            } else {
                $parameters = str_replace(' ', "*", $parameters);
            }

            // Get parameters
            $paramList = explode("*", $parameters);

            foreach ($paramList as $name) {

                // Get actual directory ID
                if (!strstr($name, "/")) {
                    $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
                    $getIdDirectory->bindParam(":mac", $terminal_mac);
                    $getIdDirectory->bindParam(":paths", $data->position);
                    $getIdDirectory->execute();
                    $CurrentDir = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["id"];
                } else {
                    $paths = Helpers::getAbsolute($data->position, $name, "..");
                    $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
                    $getIdDirectory->bindParam(":mac", $terminal_mac);
                    $getIdDirectory->bindParam(":paths", $paths);
                    $getIdDirectory->execute();
                    $CurrentDir = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["id"];
                }

                $pathlist = explode('/', $name);

                $name = $pathlist[count($pathlist) - 1];

                //check if file or dir with the same name exist
                $getFileDirRecurence = $db->prepare("SELECT name FROM terminal_file where name= :nam AND parent= :pare");
                $getFileDirRecurence->bindParam(":nam", $name);
                $getFileDirRecurence->bindParam(":pare", $CurrentDir);
                $getFileDirRecurence->execute();
                $exist = $getFileDirRecurence->fetch();

                $getFileDirRecurence2 = $db->prepare("SELECT name FROM terminal_file where name= :nam AND parent IS NULL");
                $getFileDirRecurence2->bindParam(":nam", $name);
                $getFileDirRecurence2->execute();
                $existNULL = $getFileDirRecurence2->fetch();

                // Prepare
                $stmp1 = $db->prepare("DELETE FROM terminal_file WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

                // Bind parameters put in SQL
                $stmp1->bindParam(":terminal", $terminal_mac);
                $stmp1->bindParam(":parent", $CurrentDir);
                $stmp1->bindParam(":name", $name);
                $stmp1->bindParam(":owner", $data->user->idterminal_user);

                $stmp1->execute();

                $stmp2 = $db->prepare("DELETE FROM terminal_file WHERE terminal= :terminal AND parent IS NULL AND name= :name AND owner= :owner");

                // Bind parameters put in SQL
                $stmp2->bindParam(":terminal", $terminal_mac);
                $stmp2->bindParam(":name", $name);
                $stmp2->bindParam(":owner", $data->user->idterminal_user);

                $stmp2->execute();
            }
        }
    }

}
