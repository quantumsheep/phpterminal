<?php
namespace Alph\Commands;

use Alph\Models\Terminal_FileModel;
use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class ls implements CommandInterface
{
    const USAGE = "ls [OPTION]... [FILE]...";

    const SHORT_DESCRIPTION = "List information about the FILEs (the current directory by default).
    Sort entries alphabetically if none of -cftuvSUX nor --sort is specified.
    Mandatory arguments to long options are mandatory for short options too.";

    const FULL_DESCRIPTION = "The SIZE argument is an integer and optional unit (example: 10K is 10*1024).
    Units are K,M,G,T,P,E,Z,Y (powers of 1024) or KB,MB,... (powers of 1000).

    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/ls>
    or available locally via: info '(coreutils) ls invocation'";

    const OPTIONS = [
        "-a, --all" => "do not ignore entries starting with .",
        "-l" => "use a long listing format",
    ];

    const EXIT_STATUS = "0  if OK,
    1  if minor problems (e.g., cannot access subdirectory),
    2  if serious trouble (e.g., cannot access command-line argument).";

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
        $str = "";
        $lineReturn = false;

        $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, $data->position);
        $files = self::getFiles($db, $terminal_mac, $currentPath);
        $dirs = self::getDirectories($db, $terminal_mac, $currentPath);

        if (!empty($parameters)) {
            $options = CommandAsset::getOptions($parameters);
            $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);

            if ($parameters != "") {
                $paramArray = explode(" ", $parameters);
            }

            if (!empty($paramArray)) {
                foreach ($paramArray as $path) {

                    if ($path[0] == "/") {
                        $fileType = CommandAsset::checkBoth($terminal_mac, $path, CommandAsset::getParentId($db, $terminal_mac, $path), $db);
                    } else {
                        $fileType = CommandAsset::checkBoth($terminal_mac, $path, CommandAsset::getParentId($db, $terminal_mac, $data->position . '/' . $path), $db);
                    }

                    if ($fileType == 1) {
                        if ($path[0] == "/") {
                            $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, CommandAsset::getAbsolute($path));
                        } else {
                            $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $path));
                        }

                        $files = self::getFiles($db, $terminal_mac, $currentPath);
                        $dirs = self::getDirectories($db, $terminal_mac, $currentPath);

                        if (!empty($path) && count($paramArray) > 1) {
                            $sender->send("message|<br>" . $path . ": <br>");
                        }
                        self::ls($db, $terminal_mac, $sender, $files, $dirs, $currentPath, $options);
                    } else if ($fileType == 2) {
                        $sender->send("message|<br>" . $path . "<br>");
                    } else if ($path == "") {

                    } else {
                        $sender->send("message|<br>ls: cannot access '" . $path . "': No such file or directory<br>");
                    }
                }
            } else {
                return self::ls($db, $terminal_mac, $sender, $files, $dirs, $currentPath, $options);
            }
        } else {
            $options = [];
            return self::ls($db, $terminal_mac, $sender, $files, $dirs, $currentPath, $options);
        }
    }

    public static function ls(\PDO $db, string $terminal_mac, ConnectionInterface $sender, array $files, array $dirs, string $currentPath, array $options)
    {
        $str = "";
        if (empty($options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<div class='flex' style='flex:wrap; padding: 0;'>";
            }

            foreach ($files as $file) {
                if ($file->name[0] !== '.') {
                    $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name, $currentPath);
                    if ($chmod == 777) {
                        $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:#e6ce00;">' . $file->name . '</span></span>';
                    } else {
                        $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;">' . $file->name . '</span>';
                    }
                }
            }

            foreach ($dirs as $dir) {
                if (isset($dir->name[0]) && $dir->name[0] !== '.') {
                    $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name, $currentPath);
                    if ($chmod == 777) {
                        $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:#343862; background-color:#449544;">' . $dir->name . ' </span></span>';
                    } else {
                        $str = $str . '<span style="color:#6871C4; padding-left: 0; padding-top: 20px; padding-right:20px;">' . $dir->name . ' </span>';
                    }
                }
            }

            if ($files !== null || $dir !== null) {
                $str = $str . '</div>';
            }
            $sender->send("message|" . $str);

        } else if (\in_array("l", $options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<table>";
            }

            foreach ($files as $file) {
                if ($file->name[0] !== '.') {
                    $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name, $currentPath);
                    $stringRights = self::lsGetRights($chmod, 2);
                    $str = $str . '<tr><td class="pr-2">' . $stringRights . '</td><td class="pr-2">' . $file->username . '</td><td class="pr-2">' . $file->data . '</td><td class="pr-2">' . $file->editeddate . '</td><td class="pr-2"><span style="color:#e6ce00;">' . $file->name . '</span></td></tr>';

                }
            }

            foreach ($dirs as $dir) {
                if ($dir->name[0] !== '.') {
                    $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name, $currentPath);
                    $stringRights = self::lsGetRights($chmod, 1);
                    $str = $str . '<tr><td class="pr-2">' . $stringRights . '</td><td class="pr-2">' . $dir->username . '</td><td class="pr-2">' . $dir->data . '</td><td class="pr-2">' . $dir->editeddate . '</td><td class="pr-2"><span style="color:#343862; background-color:#449544;">' . $dir->name . '</span></td></tr>';

                }
            }

            if ($files !== null || $dir !== null) {
                $str = $str . '</table>';
            }
            $sender->send("message|" . $str);

        } else if (\in_array("a", $options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<div class='container flex' style='flex:wrap; padding: 0;'>";
            }

            foreach ($files as $file) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name, $currentPath);
                if ($chmod == 777) {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:#e6ce00;">' . $file->name . '</span></span>';
                } else {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;">' . $file->name . '</span>';
                }
            }

            foreach ($dirs as $dir) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name, $currentPath);
                if ($chmod == 777) {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:#343862; background-color:#449544;">' . $dir->name . ' </span></span>';
                } else {
                    $str = $str . '<span style="color:#6871C4; padding-left: 0; padding-top: 20px; padding-right:20px;">' . $dir->name . ' </span>';
                }
            }

            if ($files !== null || $dir !== null) {
                $str = $str . '</div>';
            }
            $sender->send("message|" . $str);

        } else if (\in_array("la", $options) || \in_array("al", $options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<br><table>";
            }

            foreach ($files as $file) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name, $currentPath);
                $stringRights = self::lsGetRights($chmod, 2);
                $str = $str . '<tr><td class="pr-2">' . $stringRights . '</td><td class="pr-2">' . $file->username . '</td><td class="pr-2">' . $file->data . '</td><td class="pr-2">' . $file->editeddate . '</td><td class="pr-2"><span style="color:#e6ce00;">' . $file->name . '</span></td></tr>';

            }

            foreach ($dirs as $dir) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name, $currentPath);
                $stringRights = self::lsGetRights($chmod, 1);
                $str = $str . '<tr><td class="pr-2">' . $stringRights . '</td><td class="pr-2">' . $dir->username . '</td><td class="pr-2">' . $dir->data . '</td><td class="pr-2">' . $dir->editeddate . '</td><td class="pr-2"><span style="color:#343862; background-color:#449544;">' . $dir->name . '</span></td></tr>';

            }

            if ($files !== null || $dir !== null) {
                $str = $str . '</table>';
            }
            $sender->send("message|" . $str);
        }
    }

    public static function getFiles(\PDO $db, string $terminal_mac, $currentPath)
    {
        $stmp = $db->prepare("SELECT name, chmod, editeddate, length(data), username, parent, idfile FROM terminal_file,terminal_user WHERE terminal_file.terminal=:mac AND parent=:parent AND idterminal_user = owner");
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
        $stmp = $db->prepare("SELECT name, chmod, editeddate, username, parent, iddir FROM terminal_directory,terminal_user WHERE terminal_directory.terminal=:mac AND parent=:parent AND idterminal_user = owner");
        $stmp->bindParam(":mac", $terminal_mac);
        $stmp->bindParam(":parent", $currentPath);
        $stmp->execute();
        $dirs = [];

        while ($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
            $dirs[] = Terminal_FileModel::map($row);
        }
        return $dirs;
    }

    public static function lsGetRights($chmod, $type)
    {

        $rightsString = "";
        $reversedRights = "";
        if ($type == 1) {
            $rightsString .= "d";
        } else {
            $rightsString .= "f";
        }

        for ($i = 0; $i < 3; $i++) {
            $rights = $chmod % 10;
            if ($rights == 0) {
                $reversedRights .= "---";
            } else if ($rights == 1) {
                $reversedRights .= "x--";
            } else if ($rights == 2) {
                $reversedRights .= "-r-";
            } else if ($rights == 3) {
                $reversedRights .= "xr-";
            } else if ($rights == 4) {
                $reversedRights .= "--w";
            } else if ($rights == 5) {
                $reversedRights .= "x-w";
            } else if ($rights == 6) {
                $reversedRights .= "-rw";
            } else if ($rights == 7) {
                $reversedRights .= "xwr";
            }
            $chmod = floor($chmod / 10);
        }
        $rightsString .= strrev($reversedRights);

        return $rightsString;
    }
}
