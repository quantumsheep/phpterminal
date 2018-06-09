<?php
namespace Alph\Services;

use Alph\Models\Terminal_FileModel;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class CommandAsset
{
    //GLOBAL USAGES FUNCTIONS -- START
    /**
     * get quoted Parameters and return full Path of those in an array
     */
    public static function getQuotedParameters(string &$parameters, $position)
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
    public static function getParentId(\PDO $db, ConnectionInterface $sender, string $terminal_mac, SenderData &$data, string $absolutePath)
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

    //GLOBAL USAGES FUNCTIONS -- END

    //CD USAGES FUNCTIONS -- START
    //CD USAGES FUNCTIONS -- END

    //CLEAR USAGES FUNCTIONS -- START
    //CLEAR USAGES FUNCTIONS -- END

    /**
     * Get the CHMOD of the sended file/dir
     */
    public static function getChmod(\PDO $db, string $terminal_mac, string $name, string $parentId)
    {
        $stmp = $db->prepare("SELECT chmod FROM terminal_file WHERE name= :name AND terminal= :terminal AND parent= :parent");
        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":parent", $parentId);
        $stmp->execute();
        $chmod = $stmp->fetch(\PDO::FETCH_COLUMN);

        if ($chmod == false) {
            $stmp2 = $db->prepare("SELECT chmod FROM terminal_directory WHERE name= :name AND terminal= :terminal");
            $stmp2->bindParam(":terminal", $terminal_mac);
            $stmp2->bindParam(":name", $name);
            $stmp2->execute();
            $chmod = $stmp2->fetch(\PDO::FETCH_COLUMN);
        }

        return $chmod;
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
    public static function stageCreateNewDirectories(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $fullPathNewDirectories)
    {
        foreach ($fullPathNewDirectories as $fullPathNewDirectory) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $sender, $terminal_mac, $data, $fullPathNewDirectory);

            if ($parentId != null) {
                // Get name from created directory
                $newDirectoryName = explode("/", $fullPathNewDirectory)[count(explode("/", $fullPathNewDirectory)) - 1];

                // Check if directory already exists
                if (self::checkDirectoryExistence($newDirectoryName, $parentId, $db) === false && self::checkFileExistence($newDirectoryName, $parentId, $db) === false) {
                    // Create directory
                    self::createNewDirectory($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $newDirectoryName, $parentId);
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
    public static function createNewDirectory(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, string $name, int $parentId)
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
    public static function mkdirDOption(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $fullPathParameters)
    {
        foreach ($fullPathParameters as $fullPathParameter) {
            $parentId = 1;
            $parentPath = "";
            // Get whole directory name
            $directorySplited = explode("/", $fullPathParameter);
            array_shift($directorySplited);
            foreach ($directorySplited as $directoryName) {
                if (self::checkDirectoryExistence($directoryName, $parentId, $db) === false) {
                    self::createNewDirectory($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $directoryName, $parentId);
                }
                $parentPath = $parentPath . "/" . $directoryName;
                $parentId = self::getIdDirectory($db, $terminal_mac, $parentPath);
            }
        }

    }
    //MKDIR USAGES FUNCTIONS -- END

    //RM USAGES FUNCTIONS -- START
    public static function stageDeleteFiles(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $fullPathFiles, string $type)
    {
        foreach ($fullPathFiles as $fullPathFile) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $sender, $terminal_mac, $data, $fullPathFile);

            if ($parentId != null) {
                // Get name from created file
                $FileName = explode("/", $fullPathFile)[count(explode("/", $fullPathFile)) - 1];

                // Check if file exists
                if (self::checkDirectoryExistence($FileName, $parentId, $db) === false && self::checkFileExistence($FileName, $parentId, $db) === false) {
                    $sender->send("message|<br>" . $FileName . " : didn't exists");
                } else {
                    if ($type == 'file') {
                        // Delete file
                        self::deleteFile($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $FileName, $parentId);
                    } else if ($type == 'dir') {
                        // Delete file
                        self::deleteDir($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $FileName, $parentId);
                    } else {
                        self::deleteFile($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $FileName, $parentId);
                        self::deleteDir($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $FileName, $parentId);
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
    public static function deleteFile(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, string $name, int $parentId)
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
    public static function deleteDir(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, string $name, int $parentId)
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
    public static function stageCreateNewFiles(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $fullPathNewFiles)
    {
        foreach ($fullPathNewFiles as $fullPathNewFile) {
            // get Full Path of Parent directory
            $parentId = self::getParentId($db, $sender, $terminal_mac, $data, $fullPathNewFile);

            if ($parentId != null) {
                // Get name from created file
                $newFileName = explode("/", $fullPathNewFile)[count(explode("/", $fullPathNewFile)) - 1];

                // Check if file already exists
                if (self::checkDirectoryExistence($newFileName, $parentId, $db) === false && self::checkFileExistence($newFileName, $parentId, $db) === false) {
                    // Create file
                    self::createNewFile($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, $newFileName, $parentId);
                } else {

                    $sender->send("message|<br>" . $newFileName . " : already exists");
                }
            } else {
                $sender->send("message|<br> Path not found");
            }
        }
    }

    /**
     * generate a new File
     */
    public static function createNewFile(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, string $name, int $parentId)
    {
        $basicmod = 777;
        $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

        $stmp->bindParam(":terminal", $terminal_mac);
        $stmp->bindParam(":parent", $parentId);
        $stmp->bindParam(":name", $name);
        $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
        $stmp->bindParam(":owner", $data->user->idterminal_user);

        $stmp->execute();
    }
    //TOUCH USAGES FUNCTIONS -- END
}
