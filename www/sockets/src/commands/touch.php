<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\Helpers;
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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        //Var for default CHMOD
        $basicmod = 777;

        //Error message if there is no parameters.
        if (empty($parameters)) {
            $sender->send("touch: missing file operand, Try 'touch --help' for more information.");
            return;
        } else {
            //Load all the parameters with quotes in one array
            preg_match_all("/\"[^\"]*\"/", $parameters, $quotedParams);

            //If there's parameters with quotes
            if (!empty($quotedParams[0])) {

                //Load all quoted parameters in a TMP array
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

            //Get all the parameters in an array
            $paramList = explode("*", $parameters);

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
                $exist = $getFileDirRecurence->rowCount() > 0;

                //If the file or the dir didn't exist, create the file
                if (!$exist) {
                    $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

                    $stmp->bindParam(":terminal", $terminal_mac);
                    $stmp->bindParam(":parent", $CurrentDir);
                    $stmp->bindParam(":name", $fileName);
                    $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
                    $stmp->bindParam(":owner", $data->user->idterminal_user);

                    $stmp->execute();
                }
            }
        }
    }

}
