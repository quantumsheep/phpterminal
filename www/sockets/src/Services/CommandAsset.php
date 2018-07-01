<?php
namespace Alph\Services;

use Alph\Models\Terminal_FileModel;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class CommandAsset
{
    //GLOBAL USAGES FUNCTIONS -- START

    //Note for GetDirFileName : This function was generate as an new function and use other functionality to get element's pur name (cleaning quoted)
    /**
     * Get name from parameters and return an array filled with
     */
    public static function getDirFileName(&$parameters, $position)
    {
        $quotedParametersName = [];
        $finalDirNames = [];
        // Get Quoted parameters name
        $quotedParameters = self::getQuotedParameters($parameters, $position);
        foreach ($quotedParameters as $fullPathQuotedParameters) {
            $partQuotedParameters = explode("/", $fullPathQuotedParameters);
            $quotedParametersName[] = $partQuotedParameters[1];
        }
        // concatenate table if $parameters is not empty after quoted removal
        if (!empty($parameters)) {
            // RISK generate empty parameters in array
            $dirFileNames = explode(" ", $parameters);
            foreach ($dirFileNames as $dirFileName) {
                // treat empty parameters potentially generate
                if ($dirFileName != "") {
                    $finalDirNames[] = $dirFileName;
                }
            }
        }

        //
        self::concatenateParameters($finalDirNames, $quotedParametersName);
        return $finalDirNames;
    }

    /**
     * get quoted Parameters and return full Path of those in an array
     */
    public static function getQuotedParameters(string &$parameters, string $position)
    {
        $pattern = "/(\"([^\"]+)\") /";
        $fullPathQuotedParameters = [];
        // Get quoted element with the pattern
        preg_match_all($pattern, $parameters . " ", $quotedParameters);

        // Use 2 position of array, to exclude " "
        if (!empty($quotedParameters[1])) {
            foreach ($quotedParameters[1] as $quotedParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $quotedParameter, "", " " . $parameters);

                $fullPathQuotedParameters[] = self::GetAbsolute($position, str_replace('"', "", $quotedParameter));
            }
        }

        return $fullPathQuotedParameters;
    }

    /**
     * get command options and return it as an array
     */
    public static function getOptions(string &$parameters)
    {

        $pattern = "/(-[a-zA-Z\d]+) /";
        $finalOptions = [];

        // Get options with the pattern
        preg_match_all($pattern, $parameters . " ", $options);

        if (!empty($options[1])) {
            foreach ($options[1] as $option) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $option, "", " " . $parameters);
                // remove "-" from option for easiest treatment
                $finalOptions[] = str_replace("-", "", $option);
            }
        }

        return $finalOptions;
    }

    /**
     * get path parameters and return full path of both relative and absolute one in an array
     */
    public static function getPathParameters(string &$parameters, string $position): array
    {
        $fullPathParameters = [];

        // Get absolute Path parameters
        $absolutePathParameters = self::getAbsolutePathParameters($parameters);

        // Get relative Path parameters
        $relativePathParameters = self::getRelativePathParameters($parameters, $position);

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

        // remove "" element, in order to get easier element to work on
        $fullPathParameters = str_replace('"', "", $fullPathParameters);
        return $fullPathParameters;
    }

    /**
     * get absolute path parameters and return it in an array
     */
    public static function getAbsolutePathParameters(string &$parameters)
    {
        $finalPathParameters = [];
        $pattern = "/ ((\/+((\"[^\"]*\")|[^\/ ]+))+)/";

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
     * localize relative path parameters and return absolute path of those in an array
     */
    public static function getRelativePathParameters(string &$parameters, string $position)
    {

        $FinalPathParameters = [];
        $pattern = "/ (((\"[^\"]*\")|([^\/ ]))+\/((\"[^\"]*\")|([^\/ ]+\/?))*)+/";

        // Get path parameters with the pattern
        preg_match_all($pattern, " " . $parameters, $pathParameters);

        if (!empty($pathParameters[1])) {
            foreach ($pathParameters[1] as $pathParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $pathParameter, "", $parameters);

                $FinalPathParameters[] = self::getAbsolute($position, $pathParameter);

            }
            return $FinalPathParameters;
        }

        return;
    }

    /**
     * Give absolute path from any relative path and the actual position
     */
    public static function getAbsolute(string...$path)
    {
        $absolute = "";

        $absolute_parts = [];

        if (count($path) <= 0) {
            return "/";
        }

        if ($path[0][0] !== '/') {
            throw new \Exception("The first path given to getAbsolute function must be an absolute path.");
        }

        $i = 0;

        foreach ($path as $p) {
            $n = 0;
            $part = explode('/', $p);
            foreach ($part as $partofpart) {
                if ($partofpart == "" && $n == 0) {
                    $absolute_parts = [];
                    $i = 0;
                    $n++;
                } else if ($partofpart == ".") {
                    $i--;
                    $n++;
                } else if ($partofpart == "..") {
                    if (!isset($absolute_parts[$i - 1])) {
                        throw new \Exception("Wrong path value.");
                        return false;
                    }

                    array_splice($absolute_parts, --$i, 1);
                    $n++;
                } else {
                    $absolute_parts[] = $partofpart;
                    $i++;
                    $n++;
                }
            }
        }

        for ($j = 0; $j <= $i; $j++) {
            if (isset($absolute_parts[$j]) && $absolute_parts[$j] == "") {
                \array_splice($absolute_parts, $j, 1);
            }
        }

        return '/' . join('/', $absolute_parts);
    }

    /***
     * Get Parent Path
     */
    public static function getParentPath(string $absolutePath)
    {

        $directorySplited = explode("/", $absolutePath);
        array_pop($directorySplited);
        array_shift($directorySplited);
        $parentPath = "/" . implode("/", $directorySplited);
        return $parentPath;
    }

    /**
     * return ID of parent from the absolute path given, directory or file as last element
     */
    public static function getParentId(\PDO $db, string $terminal_mac, string $absolutePath)
    {
        // Treat fullPath of created directory to get parent Directory
        $parentPath = self::getParentPath($absolutePath);
        return self::getIdDirectory($db, $terminal_mac, $parentPath);
    }

    /**
     * Check if absolute Path does exist and provide ID in case it does. Return Null otherwise
     */
    public static function getIdDirectory(\PDO $db, string $terminal_mac, string $path)
    {
        $stmp = $db->prepare("SELECT IdDirectoryFromPath(:absolutePath, :mac) as id");
        $stmp->bindParam(":mac", $terminal_mac);
        $stmp->bindParam(":absolutePath", $path);
        $stmp->execute();
        $idDirectory = $stmp->fetch(\PDO::FETCH_ASSOC)["id"];
        return $idDirectory;
    }

    /**
     * Check if a directory exist from its name
     */
    public static function checkDirectoryExistence(string $terminal_mac, string $directoryName, int $parentId, \PDO $db)
    {
        $stmp = $db->prepare("SELECT * FROM TERMINAL_DIRECTORY WHERE name= :name AND parent= :parent AND terminal= :terminal_mac");
        $stmp->bindParam(":name", $directoryName);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":terminal_mac", $terminal_mac);
        $stmp->execute();
        $count = $stmp->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
    }
    /**
     * Check if a file exist from its Absolute Path
     */
    public static function checkFileExistence(string $terminal_mac, string $FileName, int $parentId, \PDO $db)
    {
        $stmp = $db->prepare("SELECT * FROM TERMINAL_FILE WHERE name= :name AND parent= :parent AND terminal= :terminal_mac");
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":terminal_mac", $terminal_mac);
        $stmp->execute();
        $count = $stmp->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
    }

    /**
     * Check Both and return with a bin (0,1 or 2) what is the document
     */
    public static function checkBoth(string $terminal_mac, string $ElementName, int $parentId, \PDO $db)
    {
        //Will Trim in case Element passed is a relative of a full path
        $ElementName = explode("/", $ElementName)[count(explode("/", $ElementName)) - 1];

        $ElementAttribut = 0;

        //Check if it's a directory
        if (self::checkDirectoryExistence($terminal_mac, $ElementName, $parentId, $db)) {
            $ElementAttribut = 1;

            // otherwise check if it's a file
        } else if (self::checkFileExistence($terminal_mac, $ElementName, $parentId, $db)) {
            $ElementAttribut = 2;

        }
        return $ElementAttribut;
    }

    /**
     * return array of fullPath from array of parameters
     */
    public static function fullPathFromParameters(array $parameters, string $position)
    {
        $fullPathParameters = [];
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                if ($parameter != "") {
                    $fullPathParameters[] = self::getAbsolute($position, $parameter);
                }
            }
            return $fullPathParameters;
        }
        return;
    }

    /**
     * Concatenate Parameters
     */
    public static function concatenateParameters(array &$hostArray, array...$parameters)
    {
        if (!empty($parameters)) {
            for ($i = 0; $i < count($parameters); $i++) {
                for ($j = 0; $j < count($parameters[$i]); $j++) {
                    $hostArray[] = $parameters[$i][$j];
                }
            }
        }
    }

    public static function getFile(\PDO $db, string $path, string $terminal_mac): Terminal_FileModel
    {
        $stmp = $db->prepare("SELECT idfile, terminal, parent, name, data, chmod, owner, `group`, createddate, editeddate FROM TERMINAl_FILE WHERE idfile = IdFileFromPath(:path, :terminal);");
        $stmp->bindParam(':path', $path);
        $stmp->bindParam(':terminal', $terminal_mac);

        $stmp->execute();

        $data = $stmp->fetch(\PDO::FETCH_ASSOC);

        return Terminal_FileModel::map($data !== false ? $data : []);
    }

    /**
     * Get the CHMOD of the sended file/dir
     */
    public static function getChmod(\PDO $db, string $terminal_mac, string $name, int $parentId)
    {
        $stmp = $db->prepare("SELECT chmod FROM terminal_file WHERE name= :name AND terminal= :terminal AND parent= :parentId");
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":parentId", $parentId);

        $stmp->execute();
        $chmod = $stmp->fetch(\PDO::FETCH_COLUMN);

        if ($chmod == false) {
            $stmp2 = $db->prepare("SELECT chmod FROM terminal_directory WHERE name=:name AND terminal=:terminal AND parent=:parentId");
            $stmp2->bindParam(":terminal", $terminal_mac);
            $stmp2->bindParam(":name", $name);
            $stmp2->bindParam(":parentId", $parentId);

            $stmp2->execute();
            $chmod = $stmp2->fetch(\PDO::FETCH_COLUMN);
        }

        return $chmod;
    }

    /**
     * Remove element from array (whatever the times it appears in)
     */
    public static function removeElementFromArray(&$array, $element)
    {
        $newArray = [];
        for ($i = 0; $i < count($array); $i++) {
            if ($element != $array[$i]) {
                $newArray[] = $array[$i];
            }
        }
        $array = $newArray;
    }

    /**
     * Check if user can make functionality
     */
    public static function checkRightsTo(\PDO $db, string $terminal_mac, int $owner, int $group, string $elementFullPath, int $elementChmod, int $chmodNeeded)
    {
        $userType = self::getUserType($db, $terminal_mac, $group, $owner, $elementFullPath);

        if ($userType == 1) {
            $rightsTo = floor($elementChmod / 100);

        } else if ($userType == 2) {
            $rightsTo = floor(($elementChmod / 10) % 10);

        } else if ($userType == 3) {
            $rightsTo = floor($elementChmod % 10);
        } else {
            return;
        }
        var_dump($elementChmod);
        var_dump($rightsTo);

        if ($chmodNeeded == 1) {
            return $rightsTo % 2 == 1;

        } else if ($chmodNeeded == 2) {
            return ($rightsTo == 2 || $rightsTo == 3 || $rightsTo == 6 || $rightsTo == 7);

        } else if ($chmodNeeded == 3) {
            return ($rightsTo == 3 ||$rightsTo == 5 ||$rightsTo == 7);

        } else if ($chmodNeeded == 4) {
            return ($rightsTo * 2) <= 8;

        } else if ($chmodNeeded == 5){
            return ($rightsTo == 5 ||$rightsTo == 7);

        } else if ($chmodNeeded == 6){
            return($rightsTo == 6 || $rightsTo == 7);

        } else if ($chmodNeeded == 7){
            return($rightsTo == 7);
        }

        return true;


    }
    /**
     * return User type (1,2,3) for owner, group or others
     */
    public static function getUserType(\PDO $db, string $terminal_mac, int $group, int $owner, string $elementFullPath)
    {
        $elementName = explode("/", $elementFullPath)[count(explode("/", $elementFullPath)) - 1];

        if ($owner == self::getElementOwner($db, $terminal_mac, $elementFullPath, $elementName)) {
            return 1;
        } else if ($group == self::getElementGroup($db, $terminal_mac, $elementFullPath, $elementName)) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Return Element owner from its fullPath and its name
     */
    public static function getElementOwner(\PDO $db, string $terminal_mac, string $elementFullPath, string $elementName)
    {
        $parentId = self::getParentId($db, $terminal_mac, $elementFullPath);
        $elementType = self::checkBoth($terminal_mac, $elementName, $parentId, $db);

        if ($elementType == 1) {
            $stmp = $db->prepare("SELECT owner FROM terminal_directory WHERE name=:name AND terminal=:terminal AND parent=:parentId");
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":name", $elementName);
            $stmp->bindParam(":parentId", $parentId);

            $stmp->execute();
            $owner = $stmp->fetch(\PDO::FETCH_COLUMN);

            return $owner;

        } else if ($elementType == 2) {

            $stmp = $db->prepare("SELECT owner FROM terminal_file WHERE name=:name AND terminal=:terminal AND parent=:parentId");
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":name", $elementName);
            $stmp->bindParam(":parentId", $parentId);

            $stmp->execute();
            $owner = $stmp->fetch(\PDO::FETCH_COLUMN);

            return $owner;
        } else {
            return false;
        }
    }

    /**
     * Return Element group from its fullPath and its name
     */
    public static function getElementGroup(\PDO $db, string $terminal_mac, string $elementFullPath, string $elementName)
    {
        $parentId = self::getParentId($db, $terminal_mac, $elementFullPath);
        $elementType = self::checkBoth($terminal_mac, $elementName, $parentId, $db);

        if ($elementType == 1) {
            $stmp = $db->prepare("SELECT group FROM terminal_directory WHERE name=:name AND terminal=:terminal AND parent=:parentId");
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":name", $elementName);
            $stmp->bindParam(":parentId", $parentId);

            $stmp->execute();
            $group = $stmp->fetch(\PDO::FETCH_COLUMN);

            return $group;

        } else if ($elementType == 2) {

            $stmp = $db->prepare("SELECT group FROM terminal_file WHERE name=:name AND terminal=:terminal AND parent=:parentId");
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":name", $elementName);
            $stmp->bindParam(":parentId", $parentId);

            $stmp->execute();
            $group = $stmp->fetch(\PDO::FETCH_COLUMN);

            return $group;
        } else {
            return false;
        }
    }
    //GLOBAL USAGES FUNCTIONS -- END

    //LS USAGES FUNCTIONS -- START
    /**
     * Get the files in the actual directory in an array
     */
    public static function getFiles(\PDO $db, string $terminal_mac, $currentPath)
    {
        $stmp = $db->prepare("SELECT name, chmod, editeddate, length(data), username FROM terminal_file,terminal_user WHERE terminal_file.terminal=:mac AND parent=:parent AND idterminal_user = owner");
        $stmp->bindParam(":mac", $terminal_mac);
        $stmp->bindParam(":parent", $currentPath);
        $stmp->execute();
        $files = [];

        while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
            $files[] = Terminal_FileModel::map($row);
        }
        return $files;
    }

    public static function getDirectories(\PDO $db, string $terminal_mac, $currentPath)
    {
        $stmp = $db->prepare("SELECT name, chmod, editeddate, username FROM terminal_directory,terminal_user WHERE terminal_directory.terminal=:mac AND parent=:parent AND idterminal_user = owner");
        $stmp->bindParam(":mac", $terminal_mac);
        $stmp->bindParam(":parent", $currentPath);
        $stmp->execute();
        $dirs = [];

        while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
            $dirs[] = Terminal_FileModel::map($row);
        }
        return $dirs;
    }
    //LS USAGES FUNCTIONS -- END

    //RM USAGES FUNCTIONS -- START
    public static function deleteFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $filename, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_file WHERE terminal = :terminal AND parent = :parent AND name = :name AND owner = :owner");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $filename);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        return $stmp->execute();
    }

    public static function getAllFilesAndDirs(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $dirname, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_file WHERE terminal = :terminal AND parent = :parent AND name = :name AND owner = :owner");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $filename);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        return $stmp->execute();
    }

    public static function deleteDir(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $dirname, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_directory WHERE terminal = :terminal AND parent = :parent AND name = :name AND owner = :owner");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $dirname);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        return $stmp->execute();
    }
    //RM USAGES FUNCTIONS -- END

    //TOUCH USAGES FUNCTIONS -- START
    /**
     * Full stage of creating new files
     */
    public static function stageCreateNewFiles(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, array $fullPathNewFiles)
    {
        foreach ($fullPathNewFiles as $fullPathNewFile) {
            self::stageCreateNewFile($db, $data, $sender, $terminal_mac, $fullPathNewFile);
        }
    }

    /**
     * generate a new File
     */
    public static function createNewFile(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId, string $content = ""): bool
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, `data`, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :content, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":content", $content);
        $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        return
        $stmp->execute();
    }

    /**
     * Create or update files
     */
    public static function createOrUpdateFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $path, string $terminal_mac, string $content = ""): bool
    {
        $parent = self::getIdDirectory($db, $terminal_mac, self::getAbsolute($path, '..'));

        if ($parent != null) {
            $file = self::getFile($db, $path, $terminal_mac);

            if ($file->idfile != null) {
                return self::updateFile($db, $path, $terminal_mac, $content);
            } else {
                return self::stageCreateNewFile($db, $data, $sender, $terminal_mac, $path, $content);
            }
        }
    }

    public static function updateFile(\PDO $db, string $path, string $terminal_mac, string $content): bool
    {
        $stmp = $db->prepare("UPDATE TERMINAL_FILE SET data = :content WHERE idfile = IdFileFromPath(:path, :terminal_mac);");

        $stmp->bindParam(":content", $content);
        $stmp->bindParam(":path", $path);
        $stmp->bindParam(":terminal_mac", $terminal_mac);

        return $stmp->execute();
    }

    public static function stageCreateNewFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $fullPathNewFile, string $content = ""): bool
    {
        // get Full Path of Parent directory
        $parentId = self::getParentId($db, $terminal_mac, $fullPathNewFile);

        if ($parentId != null) {
            // Get name from created file
            $splitedPath = explode("/", $fullPathNewFile);
            $newFileName = $splitedPath[count($splitedPath) - 1];

            // Check if file already exists
            if (self::checkDirectoryExistence($terminal_mac, $newFileName, $parentId, $db) === false && self::checkFileExistence($terminal_mac, $newFileName, $parentId, $db) === false) {
                // Create file
                return self::createNewFile($db, $data, $terminal_mac, $newFileName, $parentId, $content);
            } else {
                $sender->send("message|<br>" . $newFileName . " : already exists");
                return false;
            }
        } else {
            $sender->send("message|<br> Path not found");
            return false;
        }
    }
    //TOUCH USAGES FUNCTIONS -- END

}
