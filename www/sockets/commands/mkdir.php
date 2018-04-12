<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class mkdir implements CommandInterface
{
    const USAGE = "mkdir [OPTION]... DIRECTORY...";

    const SHORT_DESCRIPTION = "Display information about builtin commands.";
    const FULL_DESCRIPTION = "Displays brief summaries of builtin commands.  If PATTERN is specified, gives detailed help on all commands matching PATTERN, otherwise the list of help topics is printed.";

    const OPTIONS = [
        "-d" => "output short description for each topic",
        "-s" => "output only a short usage synopsis for each topic matching PATTERN",
    ];

    const ARGUMENTS = [
        "PATTERN" => "Pattern specifiying a help topic",
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
        $basicmod = 777;
        $daddy = null;
        $i = 0;
        $newDirs = [];


        //if no params
        if (empty($parameters)) {
            $sender->send("<br>Opérande manquant<br>Saisissez mkdir --help pour plus d'information");
            return;
        }
        
        //check for "" case
        preg_match_all("/ (\"([^\"]*)\") /", " " . $parameters . " ", $quotedParams);

        //rearrange params in case of "" case
        if (!empty($quotedParams[1])) {
            foreach ($quotedParams[1] as $quotedParameter) {
                $parameters = str_replace($quotedParameter, "", $parameters);
            }
        }

        

        //table of new directory with $paramParts
        $paramParts = explode(" ", $parameters);
        if (!empty($paramParts) && $paramParts[0][0] != '-') {
            while (!empty($paramParts[$i]) && $paramParts[$i][0] != '-' && $i < count($paramParts)) {
                $newDirs[] = $paramParts[$i];
                $i++;
            }
            if (!empty($paramParts[1])) {
                $params = $paramParts[$i];
            }
        } else if (!empty($paramParts[1])) {
            $i = 1;
            $params = $paramParts[0];
            while (!empty($paramParts[$i]) && $paramParts[$i][0] != '-' && $i < count($paramParts));
            $newDirs[] = $paramParts[$i];
            $i++;
        }

        $sender->send($params);

        //add the quotedParams to the new directories list
        if (!empty($quotedParams[2])) {
            for ($i = 0; $i < count($quotedParams[2]); $i++) {
                $newDirs[] = $quotedParams[2][$i];
            }
        }

        foreach ($newDirs as $name) {
            //Case Directory already exists
            $check = $db->prepare("SELECT name FROM terminal_directory WHERE name = :name");
            $check->bindParam(":name", $name);
            $check->execute();

            // Check if directory exists
            if ($check->rowCount() > 0) {
                $sender->send("<br>Error : ".$name." directory already exists");
            } else {
                //prepare
                $stmp = $db->prepare("INSERT INTO TERMINAL_DIRECTORY(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

                //bind parameters put in SQL
                $stmp->bindParam(":terminal", $terminal_mac);
                $stmp->bindParam(":parent", $daddy);
                $stmp->bindParam(":name", $name);
                $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
                $stmp->bindParam(":owner", $data->credentials->idterminal_user);
                $stmp->execute();
            }
        }
    }
}
