<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class locate implements CommandInterface
{
    const USAGE = "mkdir [OPTION]... DIRECTORY...";

    const SHORT_DESCRIPTION = "Create the DIRECTORY(ies), if they do not already exist.";
    const FULL_DESCRIPTION = "Create the DIRECTORY(ies), if they do not already exist. If paths are provided, do NOT create directory if the path provided is wrong.";

    const OPTIONS = [
        "-p" => "Create directory from paths provided in case of the directories doesn't exist",
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
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        // locate by himself return an error
        if (empty($parameters)) {
            return $sender->send("message|<br> You must enter a file or a directory to be found");
        }

        //controle quoteParameters and concanate it all
        $fullNames = CommandAsset::getDirFileName($parameters, $data->position);
        
        // check if there's only one argument
        if(isset($fullNames[1])){
            return $sender->send("message|<br> multiple argument entered. Locate only support one argument");
        }

        $localisations = CommandAsset::locateFile($db, $fullNames, $terminal_mac);
        
        if(!empty($localisations)){
            foreach($localisations as $localisation){
                $sender->send("message|<br>" . $localisation);
            }
            return;
        } else {
            return $sender->send("message|<br>Can't locate file passed.");
        }
        
    }
}
