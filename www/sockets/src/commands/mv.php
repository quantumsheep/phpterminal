<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

/**
 * template = the name of the commands
 */
class mv implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "mv [OPTION]... [-T] SOURCE DEST
    or:  mv [OPTION]... SOURCE... DIRECTORY
    or:  mv [OPTION]... -t DIRECTORY SOURCE...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.

    Mandatory arguments to long options are mandatory for short options too.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.";

    /**
     * Command's options
     */
    const OPTIONS = [
        "-b" => "make a backup of each existing destination file, like --backup but does not accept an argument",
        "-f, --force" => "do not prompt before overwriting",
        "-i, --interactive" => "prompt before overwrite",
        "-n, --no-clobber" => "do not overwrite an existing file",
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
        // stock parameters for further treatment
        $registeredParameters = $parameters;
        $cleanedParameters = [];
        $fullElements = [];

        // Treat command to get parameters
        $quotedParameters = self::mvGetQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = self::mvGetPathParameters($parameters, $data->position);

        // Fix several bug in the array, due to precedent manipulation
        if ($parameters !== "") {
            $fullElements = explode(" ", $parameters);
            if (!empty($pathParameters)) {
                for ($i = 0; $i < count($pathParameters); $i++) {
                    array_shift($fullElements);
                }
            }
        }

        CommandAsset::concatenateParameters($fullElements, $quotedParameters, $pathParameters);

        //Check if element provided is more than 1
        if (count($fullElements) < 2) {
            return $sender->send("message|<br>mv: target operand missing" . (count($fullElements) == 1 ? " after " . $fullElements[0] . "." : "."));
        }

        //get and remove target from parameters
        $target = self::getTarget($registeredParameters, $fullElements);

        //Clean parameters from "" now everything is cleared
        $cleanedTarget = str_replace('"', "", $target);

        foreach ($fullElements as $Element) {
            $cleanedParameters[] = str_replace('"', "", $Element);
        }

        // Get target attributs
        $targetFullPath = CommandAsset::getAbsolute($data->position, $cleanedTarget);
        $targetType = CommandAsset::checkBoth($terminal_mac, $target, CommandAsset::getParentId($db, $terminal_mac, $targetFullPath), $db);

        // Action if target is a directory
        if ($targetType == 1) {
            $targetId = CommandAsset::getIdDirectory($db, $terminal_mac, $targetFullPath);

            foreach ($cleanedParameters as $parameter) {
                self::updatePosition($db, $terminal_mac, $parameter, $targetId, $targetFullPath, $sender, $data->position);
            }

        } else if ($targetType == 0) {
            //in case the targetType is nothing, we may change directory or file provided as parameter for this name
            if (count($cleanedParameters) == 1) {
                self::changeName($db, $data->position, $terminal_mac, $cleanedParameters[0], $cleanedTarget, $sender);
            } else {
                return $sender->send("message|<br> You can only change name of 1 Element at a time");
            }
        } else if ($targetType == 2) {
            return $sender->send("message|<br>" . $cleanedTarget . " already exists");
        }
    }









    /**
     * custom get quoted
     */
    public static function mvGetQuotedParameters(string &$parameters, string $position)
    {
        $pattern = "/ (\"([^\"]+)\") /";
        $fullPathQuotedParameters = [];
        // Get quoted element with the pattern
        preg_match_all($pattern, " " . $parameters . " ", $quotedParameters);

        // Use 2 position of array, to exclude " "
        if (!empty($quotedParameters[1])) {
            foreach ($quotedParameters[1] as $quotedParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $quotedParameter, "", $parameters);

            }
        }

        return $quotedParameters[1];
    }
    /**
     * custom get Path parameters
     */
    public static function mvGetPathParameters(string &$parameters, string $position): array
    {
        $fullPathParameters = [];

        // Get absolute Path parameters
        $absolutePathParameters = CommandAsset::getAbsolutePathParameters($parameters);

        // Get relative Path parameters
        $relativePathParameters = self::mvGetRelativePathParameters($parameters, $position);

        // Check empty array case
        if (!empty($relativePathParameters) && !empty($absolutePathParameters)) {
            $fullPathParameters = array_merge($relativePathParameters, $absolutePathParameters);
        } else if (empty($relativePathParameters) && !empty($absolutePathParameters)) {
            // If no relative Parameters, $fullPath = absolute path parameters
            $fullPathParameters = $absolutePathParameters;
        } else if (empty($absolutePathParameters) && !empty($relativePathParameters)) {
            // If no absolute Parameters, $fullPath = relative path parameters
            $fullPathParameters = $relativePathParameters;
        }

        return $fullPathParameters;
    }

    /**
     * custom relative path parameters
     */
    public static function mvGetRelativePathParameters(string &$parameters, string $position)
    {

        $finalPathParameters = [];
        $pattern = "/ (((\"[^\"]*\")|([^\/ ]))+\/((\"[^\"]*\")|([^\/ ]+\/?))*)+/";

        // Get path parameters with the pattern
        preg_match_all($pattern, " " . $parameters, $pathParameters);
        if (!empty($pathParameters[1])) {
            foreach ($pathParameters[1] as $pathParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $pathParameter, "", " " . $parameters);

                //remove potential empty element
                if ($pathParameter != "") {
                    $finalPathParameters[] = $pathParameter;
                }
            }
            return $finalPathParameters;
        }

        return;
    }

    /**
     * Return target (last element) with array and string
     */
    public static function getTarget(string &$parameters, array &$fullParameters)
    {
        $target;
        $lastPosition = 0;

        foreach ($fullParameters as $parameter) {
            $position = strpos($parameters, $parameter);
            if ($position > $lastPosition) {
                $lastPosition = $position;
                $target = $parameter;
            }
        }

        CommandAsset::removeElementFromArray($fullParameters, $target);
        return $target;
    }

    /**
     * Function update element Position after several check up
     */
    public static function updatePosition(\PDO $db, string $terminal_mac, string $movedElementName, int $newParentId, string $newParentFullPath, ConnectionInterface $sender, string $position)
    {

        // Check if Element is a directory, or a file, or even exist.
        $elementAttribut = CommandAsset::checkBoth($terminal_mac, $movedElementName, CommandAsset::getParentId($db, $terminal_mac, $movedElementName), $db);

        // If Element is a directory
        if ($elementAttribut == 1) {
            //Get full path of moved directory
            $directoryFullPath = CommandAsset::getAbsolute($position, $movedElementName);
            $directoryId = CommandAsset::getIdDirectory($db, $terminal_mac, $directoryFullPath);

            //Check if directory can be moved (depends of the full path)
            if (self::checkSiblings($movedElementName, $newParentFullPath) == true) {
                return $sender->send("message|<br>Cannot move parent into child's Path. Children shouldn't live that way.");
            } else {
                //change Directory position
                if (CommandAsset::checkDirectoryExistence($terminal_mac, $movedElementName, $newParentId, $db) == false) {
                    // check if directory doesn't already exist in target directory
                    return self::changeDirectoryParentId($db, $directoryId, $newParentId, $terminal_mac);
                } else {
                    return $sender->send("message|<br>" . $movedElementName . " directory already exist in " . $newParentFullPath . ".");
                }

            }
            // If Element is a file
        } else if ($elementAttribut == 2) {

            //Get full path of moved file
            $fileFullPath = CommandAsset::getAbsolute($position, $movedElementName);
            $fileParentId = CommandAsset::getParentId($db, $terminal_mac, $fileFullPath);
            var_dump($fileParentId);

            //check if file does exist
            if (CommandAsset::checkFileExistence($terminal_mac, $movedElementName, $newParentId, $db) == false) {
                // check if file doesn't already exist in target directory
                return self::changeFileParentId($db, $fileParentId, $newParentId, $movedElementName, $terminal_mac);
            } else {
                return $sender->send("message|<br>" . $movedElementName . " file already exist in " . $newParentFullPath . ".");
            }

            // If Element doesn't exist
        } else {
            return $sender->send("message|<br>" . $movedElementName . " doesn't exist and cannot be moved.");
        }
    }

    /**
     * check if 2 directories are parents from their full Path. Parent shouldn't walk in their children's Path
     */
    public static function checkSiblings(string $sonPath, string $daddyPath)
    {
        if (strpos($daddyPath, $sonPath) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * update directory parent
     */
    public static function changeDirectoryParentId(\PDO $db, int $idDirectory, string $newParentId, string $terminal_mac)
    {
        var_dump($idDirectory);
        var_dump($newParentId);
        $stmp = $db->prepare("UPDATE terminal_directory SET parent= :newParent WHERE iddir= :idDirectory AND terminal= :terminal ");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":newParent", $newParentId);
        $stmp->bindParam(":idDirectory", $idDirectory);

        $stmp->execute();
    }

    /**
     * update file parent
     */
    public static function changeFileParentId(\PDO $db, int $parentId, string $newParentId, string $fileName, string $terminal_mac)
    {
        $stmp = $db->prepare("UPDATE terminal_file SET parent= :newParent WHERE parent= :parent AND terminal= :terminal AND name = :filename");
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":newParent", $newParentId);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":filename", $fileName);

        $stmp->execute();
    }

    /**
     * Change file or directory name
     */
    public static function changeName(\PDO $db, string $position, string $terminal_mac, string $elementName, string $newName, ConnectionInterface $sender)
    {
        // Get whole Element information
        $elementAbsolutePath = CommandAsset::getAbsolute($position, $elementName);
        $elementParentId = CommandAsset::getParentId($db, $terminal_mac, $elementAbsolutePath);
        $elementType = CommandAsset::checkBoth($terminal_mac, $elementName, $elementParentId, $db);
        //if Element doesn't exist
        if ($elementType == 0) {
            return $sender->send("message|<br>" . $elementName . " doesn't exist.");
            //If element is a directory
        } else if ($elementType == 1) {
            return self::changeDirectoryName($db, $terminal_mac, CommandAsset::getIdDirectory($db, $terminal_mac, $elementAbsolutePath), $newName);
            //if element is a file
        } else if ($elementType == 2) {
            return self::changeFileName($db, $terminal_mac, $elementParentId, $newName, $elementName);
        }
    }

    /**
     * change File name
     */
    public static function changeDirectoryName(\PDO $db, string $terminal_mac, int $idDirectory, string $newName)
    {
        $stmp = $db->prepare("UPDATE terminal_directory SET name= :newName WHERE iddir= :iddir AND terminal= :terminal");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":newName", $newName);
        $stmp->bindParam(":iddir", $idDirectory);

        $stmp->execute();
    }

    /**
     * change File name
     */
    public static function changeFileName(\PDO $db, string $terminal_mac, int $parentId, string $newName, string $fileName)
    {
        $stmp = $db->prepare("UPDATE terminal_file SET name= :newName WHERE parent= :parent AND terminal= :terminal AND name = :filename");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":newName", $newName);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":filename", $fileName);

        $stmp->execute();
    }
}
