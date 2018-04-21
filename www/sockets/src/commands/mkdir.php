<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class mkdir implements CommandInterface
{
    const USAGE = "help [-dms] [pattern ...]";

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
        $newDirs = [];
        $params = "";

        // Get relative parent from position
        $position = explode("/", $data->position);
        if (empty($position)) {
            $relativeParent = "/";
        } else {
            $relativeParent = $position[strlen($position)];
        }

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

        // -d parameters multiple creation case
        /*if (preg_match_all("/ ((\/\"[^\"]+[\"]?\")|(\/[^\"\/ ]+))+\/? /", " " . $parameters . " ", $absolutePathNDir) != 0) {
        $sender->send($absolutePathNDir[0]);
        $checkMultiDirectory = true;
        }
         */

        // Table of new directory with $paramParts
        $paramParts = explode(" ", $parameters);
        if (!empty($paramParts)) {
            for ($i = 0; $i < count($paramParts); $i++) {
                if (!empty($paramParts[$i]) && $paramParts[$i][0] != '-') {
                    $newDirs[] = $paramParts[$i];
                } else {

                    // Get parameters
                    $params .= $paramParts[$i];
                }
            }
        }

        // Add the quotedParams to the new directories list
        if (!empty($quotedParams[2])) {
            for ($i = 0; $i < count($quotedParams[2]); $i++) {
                $newDirs[] = $quotedParams[2][$i];
            }
        }

        // Get parameters
        $paramLetters = explode("-", $params);

        foreach ($newDirs as $name) {

            // Case Directory already exists
            $check = $db->prepare("SELECT name FROM terminal_directory WHERE name = :name");
            $check->bindParam(":name", $name);
            $check->execute();

            
            if ($check->rowCount() > 0) {
                // Check if directory exists
                $sender->send("<br>Error : " . $name . " directory already exists");

            } else if (strlen($name) > 255) {

                // Case directory name exceed 255 char
                $sender->send("Error : one of the directories' name is too long. It must not exceed 255 characters.");

            } else {

                // Case everything matches

                // Prepare
                $stmp = $db->prepare("INSERT INTO TERMINAL_DIRECTORY(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

                // Bind parameters put in SQL
                $stmp->bindParam(":terminal", $terminal_mac);
                $stmp->bindParam(":parent", $relativeParent);
                $stmp->bindParam(":name", $name);
                $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
                $stmp->bindParam(":owner", $data->user->idterminal_user);

                $stmp->execute();
            }
        }
    }
}
