<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class locate implements CommandInterface
{
    const USAGE = "locate [FILE]";

    const SHORT_DESCRIPTION = "Locate a specified [FILE].";
    const FULL_DESCRIPTION = "Locate a specified [FILE].";

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
        $path = [];
        // locate by himself return an error
        if (empty($parameters)) {
            return $sender->send("message|<br> You must enter a file or a directory to be found");
        }

        //controle quoteParameters and concanate it all
        $fullNames = CommandAsset::getDirFileName($parameters, $data->position);

        // check if there's only one argument
        if (isset($fullNames[1])) {
            return $sender->send("message|<br> multiple argument entered. Locate only support one argument");
        }

        $localisations = self::locateFile($db, $fullNames, $terminal_mac);

        if (!empty($localisations)) {
            foreach ($localisations as $localisation) {
                $sender->send("message|<br>" . $localisation);
            }
            return;
        } else {
            return $sender->send("message|<br>Can't locate file passed.");
        }
    }

    /**
     * return array full of paths leading to file
     */

    public static function locateFile(\PDO $db, array $fileName, string $terminal_mac)
    {

        $fileIds = self::getIdfromName($db, $fileName[0], $terminal_mac);

        return self::getFullPathFromIdFile($db, $fileIds, $terminal_mac);
    }

    /**
     * return IDs from $name
     */
    public static function getIdFromName(\PDO $db, string $fileName, string $terminal_mac)
    {
        $fileIds = [];

        $stmp = $db->prepare("SELECT idfile FROM terminal_file where name=:file_name and terminal=:terminal");
        $stmp->bindParam(":file_name", $fileName);
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->execute();
        $fileIdsArray = $stmp->fetchAll(\PDO::FETCH_NUM);

        // remove multiple size array, for easier further treatment
        foreach ($fileIdsArray as $fileIdArray) {
            $fileIds[] = $fileIdArray[0];
        }

        return $fileIds;
    }

    /**
     * From an array of id file, return an array of full path
     */
    public static function getFullPathFromIdFile(\PDO $db, array $fileIds, string $terminal_mac)
    {
        $reversedPaths = [];
        $realFullPaths = [];

        // Get reversed full Path as an intermediary stage
        foreach ($fileIds as $fileId) {
            $stmp = $db->prepare("SELECT GET_REVERSED_FULL_PATH_FROM_FILE_ID(:id, :terminal_mac);");
            $stmp->bindParam(":id", $fileId);
            $stmp->bindParam(":terminal_mac", $terminal_mac);
            $stmp->execute();
            $reversedPaths[] = $stmp->fetch(\PDO::FETCH_ASSOC)["GET_REVERSED_FULL_PATH_FROM_FILE_ID('" . $fileId . "', '" . $terminal_mac . "')"];
        }

        // Reverse Paths to have true Full paths
        foreach ($reversedPaths as $reversedPath) {

            $realFullPath = "";
            $interArray = explode("/", $reversedPath);
            array_pop($interArray);

            // Concatenate and reverse array into strings
            for ($i = count($interArray) - 1; $i >= 0; $i--) {
                $realFullPath = $realFullPath . "/" . $interArray[$i];
            }
            $realFullPaths[] = $realFullPath;
        }
        return $realFullPaths;
    }
}
