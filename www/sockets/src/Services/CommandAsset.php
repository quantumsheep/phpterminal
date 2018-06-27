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
     * get absolute path parameters and retuen it in an array
     */
    public static function getAbsolutePathParameters(string &$parameters)
    {

        $pattern = "/ ((\/+((\"[^\"]*\")|[^\/ ]+))+)/";

        // Get path parameters with the pattern
        preg_match_all($pattern, " " . $parameters, $pathParameters);

        if (!empty($pathParameters[1])) {
            foreach ($pathParameters[1] as $pathParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace($pathParameter, "", $parameters);
            }
            return $pathParameters[1];
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
                $parameters = str_replace($pathParameter, "", $parameters);

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

    /**
     * return ID of parent from the absolute path given, directory or file as last element
     */
    public static function getParentId(\PDO $db, string $terminal_mac, string $absolutePath)
    {
        // Treat fullPath of created directory to get parent Directory
        $directorySplited = explode("/", $absolutePath);
        array_pop($directorySplited);
        array_shift($directorySplited);
        $parentPath = "/" . implode("/", $directorySplited);
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
     * Check if a directory exist from its Absolute Path
     */
    public static function checkDirectoryExistence(string $directoryName, int $parentId, \PDO $db)
    {
        $stmp = $db->prepare("SELECT * FROM TERMINAL_DIRECTORY WHERE name= :name AND parent= :parent");
        $stmp->bindParam(":name", $directoryName);
        $stmp->bindParam(":parent", $parentId);
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
    public static function checkFileExistence(string $FileName, int $parentId, \PDO $db)
    {
        $stmp = $db->prepare("SELECT * FROM TERMINAL_FILE WHERE name= :name AND parent= :parent");
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":parent", $parentId);
        $stmp->execute();
        $count = $stmp->rowCount();
        if ($count > 0) {
            return true;
        }
        return false;
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
     *
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

    //MKDIR USAGES FUNCTIONS -- START

    /**
     * Generate new directories from array of Full Paths
     */
    public static function stageCreateNewDirectories(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, $fullPathNewDirectories)
    {
        foreach ($fullPathNewDirectories as $fullPathNewDirectory) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $terminal_mac, $fullPathNewDirectory);

            if ($parentId != null) {
                // Get name from created directory
                $newDirectoryName = explode("/", $fullPathNewDirectory)[count(explode("/", $fullPathNewDirectory)) - 1];

                // Check if directory already exists
                if (self::checkDirectoryExistence($newDirectoryName, $parentId, $db) === false && self::checkFileExistence($newDirectoryName, $parentId, $db) === false) {
                    // Create directory
                    self::createNewDirectory($db, $data, $terminal_mac, $newDirectoryName, $parentId);
                } else {

                    $sender->send("message|<br>" . $newDirectoryName . " : already exists");
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    /**
     * generate a new directory
     */
    public static function createNewDirectory(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId)
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_DIRECTORY(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
    }

    /**
     * Automatically generate directory if it doesn't exist
     * -d's mkdir option
     */
    public static function mkdirDOption(\PDO $db, SenderData &$data, string $terminal_mac, $fullPathParameters)
    {
        foreach ($fullPathParameters as $fullPathParameter) {
            $parentId = 1;
            $parentPath = "";
            // Get whole directory name
            $directorySplited = explode("/", $fullPathParameter);
            array_shift($directorySplited);

            foreach ($directorySplited as $directoryName) {
                if (self::checkDirectoryExistence($directoryName, $parentId, $db) === false) {
                    self::createNewDirectory($db, $data, $terminal_mac, $directoryName, $parentId);
                }
                $parentPath = $parentPath . "/" . $directoryName;
                $parentId = self::getIdDirectory($db, $terminal_mac, $parentPath);
            }
        }

    }
    //MKDIR USAGES FUNCTIONS -- END

    //RM USAGES FUNCTIONS -- START
    public static function stageDeleteFiles(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, array $fullPathFiles, string $type)
    {
        foreach ($fullPathFiles as $fullPathFile) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $terminal_mac, $fullPathFile);

            if ($parentId != null) {
                // Get name from created file
                $FileName = explode("/", $fullPathFile)[count(explode("/", $fullPathFile)) - 1];

                // Check if file exists
                if (self::checkDirectoryExistence($FileName, $parentId, $db) === false && self::checkFileExistence($FileName, $parentId, $db) === false) {
                    $sender->send("message|<br>" . $FileName . " : didn't exists");
                } else {
                    if ($type == 'file') {
                        // Delete file
                        self::deleteFile($db, $data, $sender, $terminal_mac, $FileName, $parentId);
                    } else if ($type == 'dir') {
                        // Delete file
                        self::deleteDir($db, $data, $sender, $terminal_mac, $FileName, $parentId);
                    } else {
                        self::deleteFile($db, $data, $sender, $terminal_mac, $FileName, $parentId);
                        self::deleteDir($db, $data, $sender, $terminal_mac, $FileName, $parentId);
                    }
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    /**
     * delete a File
     */
    public static function deleteFile(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, string $name, int $parentId)
    {
        $stmp = $db->prepare("SELECT name FROM terminal_directory WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        //If the file or the dir exist, delete the file
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
        if ($stmp->fetch()['name']) {
            $sender->send("message|<br>rm: cannot remove '" . $name . "': Is a directory");
        } else {

            $stmp = $db->prepare("DELETE FROM terminal_file WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

            //If the file or the dir exist, delete the file
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":parent", $parentId);
            $stmp->bindParam(":name", $name);
            $stmp->bindParam(":owner", $data->user->idterminal_user);

            $stmp->execute();
        };
    }

    /**
     * delete a Directory
     */
    public static function deleteDir(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId)
    {
        $stmp = $db->prepare("DELETE FROM terminal_directory WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        //If the file or the dir exist, delete the file
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();

        $stmp = $db->prepare("SELECT name FROM terminal_directory WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        //If the file or the dir exist, delete the file
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
        if ($stmp->fetch()['name']) {
            $sender->send("message|<br> Directory not empty.");
        };
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
    public static function createNewFile(\PDO $db, SenderData &$data, string $terminal_mac, string $name, int $parentId): bool
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
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
            $newFileName = explode("/", $fullPathNewFile)[count(explode("/", $fullPathNewFile)) - 1];

            // Check if file already exists
            if (self::checkDirectoryExistence($newFileName, $parentId, $db) === false && self::checkFileExistence($newFileName, $parentId, $db) === false) {
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

    //LOCATE USAGE FUNCTIONS -- START
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
    //LOCATE USAGE FUNCTIONS --END

    //CHMOD USAGE FUNCTIONS --END

    public static function stageChangeChmod(\PDO $db, SenderData &$data, ConnectionInterface $sender, string $terminal_mac, $fullPathFiles, int $askedChmod)
    {
        foreach ($fullPathFiles as $fullPathFile) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $sender, $terminal_mac, $fullPathFile);

            if ($parentId != null) {
                // Get name from created file
                $FileName = explode("/", $fullPathFile)[count(explode("/", $fullPathFile)) - 1];

                // Check if file exists
                if (self::checkDirectoryExistence($FileName, $parentId, $db) === false && self::checkFileExistence($FileName, $parentId, $db) === false) {
                    $sender->send("message|<br>" . $FileName . " : didn't exists");
                } else {
                    self::changeChmod($db, $data, $terminal_mac, $FileName, $askedChmod, $parentId);
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    public static function changeChmod(\PDO $db, SenderData &$data, string $terminal_mac, string $FileName, int $askedChmod, int $parentId)
    {
        $stmp = $db->prepare("UPDATE terminal_file SET chmod= :chmod WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        $stmp->bindParam(":chmod", $askedChmod);
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();

        $stmp = $db->prepare("UPDATE terminal_directory SET chmod= :chmod WHERE terminal= :terminal AND parent= :parent AND name= :name AND owner= :owner");

        $stmp->bindParam(":chmod", $askedChmod);
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $FileName);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
    }
    //CHMOD USAGE FUNCTIONS --END

    //MV USAGE FUNCTIONS -- START
    /**
     * Treat mv element to determine Element position
     */
    public static function mvIsolateElement($parameters)
    {
        $parametersArray = [];

        $pattern = "/(\"([^\"]+)\") /";
        $fullQuotedParameters = [];
        // Get quoted element with the pattern
        preg_match_all($pattern, $parameters . " ", $quotedParameters);

        // Use 2 position of array, to exclude " "
        if (!empty($quotedParameters[1])) {
            foreach ($quotedParameters[1] as $quotedParameter) {
                // Update the whole parameters for further concatenation
                $parameters = str_replace(" " . $quotedParameter, "", " " . $parameters);

                $fullQuotedParameters[] = $quotedParameter;
            }
        }

        // get Regular parameters into array for further concatenation
        $regularParameters = explode(" ", $parameters);

        //Update element if quoted element is in. It creates an empty entry
        if (!empty($fullQuotedParameters)) {
            array_shift($regularParameters);
        }

        //concatenate whole parameters
        self::concatenateParameters($parametersArray, $regularParameters, $fullQuotedParameters);

        return $parametersArray;
    }

    /**
     * Get option from array of parameters
     */
    public static function mvGetOptions(array &$fullParameters)
    {
        $options = "";
        $option = "";
        $pattern = "/(-([a-zA-Z\d]+))/";

        // check if every array entry is an option
        foreach ($fullParameters as $parameter) {
            //reset option
            $option = "";
            preg_match($pattern, $parameter, $option);
            if (!empty($option)) {
                //remove the Element from the array
                self::removeElementFromArray($fullParameters, $parameter);

                // get parameters without "-" and concatenate into option, to get a full string of options
                $options .= $option[2];
            }
        }
        return $options;
    }

    /**
     * Return target (last element) with array and string
     */
    public static function getTarget($parameters, &$fullParameters)
    {
        $position = 0;
        
        $elementPosition = [];

        //research Element
        for ($i = 0; $i < count($fullParameters); $i++) {
            $elementPosition[] = strpos($parameters, $fullParameters[$i]);
            if ($elementPosition[$i] > $position) {
                $target = $fullParameters[$i];
            }
            
        }
        return $target;
    }

    //MV USAGE FUNCTIONS -- END
}
