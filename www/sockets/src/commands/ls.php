<?php
namespace Alph\Commands;

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

    Using color to distinguish file types is disabled both by default and
    with --color=never.  With --color=auto, ls emits color codes only when
    standard output is connected to a terminal.  The LS_COLORS environment
    variable can change the settings.  Use the dircolors command to set it.

    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/ls>
    or available locally via: info '(coreutils) ls invocation'";

    const OPTIONS = [
        "-a, --all" => "do not ignore entries starting with .",
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
     * @param string $cmd   ²
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        $str = "";
        $lineReturn = false;
        //Get the curent id form the actual position of the user in a var
        $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, $data->position);

        //Get the files in the actual directory in an array
        $files = CommandAsset::getFiles($db, $terminal_mac, $currentPath);

        //Get the dirs in the actual directory in an array
        $dirs = CommandAsset::getDirectories($db, $terminal_mac, $currentPath);

        if (!empty($parameters)) {
            $options = CommandAsset::getOptions($parameters);
        }

        if (empty($options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<br><div class='container flex' style='flex:wrap; padding: 0;'>";
            }
            foreach ($files as $file) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name);
                if ($chmod == 777) {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:yellow;">' . $file->name . '</span></span>';
                } else {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;">' . $file->name . '</span>';
                }
            }
            foreach ($dirs as $dir) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name);
                if ($chmod == 777) {
                    $str = $str . '<span style="padding-left: 0; padding-top: 20px; padding-right:20px;"><span style="color:blue; background-color:green;">' . $dir->name . ' </span></span>';
                } else {
                    $str = $str . '<span style="color:blue; padding-left: 0; padding-top: 20px; padding-right:20px;">' . $dir->name . ' </span>';
                }
            }
            if ($files !== null || $dir !== null) {
                $str = $str . '</div>';
            }
            $sender->send("message|" . $str);
        } else if (\in_array("l", $options)) {
            if ($files !== null || $dir !== null) {
                $str = $str . "<br><tr style='padding: 0;'>";
            }
            //Return the files and the dirs to the user
            foreach ($files as $file) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $file->name);
                $str = $str . '<td style="padding-right: 0;">' . $file->chmod . $file->chmod . $file->owner . $file->owner . $file->owner . $file->editeddate . $file->name . '</td><br>';
            }
            foreach ($dirs as $dir) {
                $chmod = CommandAsset::getChmod($db, $terminal_mac, $dir->name);
                $str = $str . '<td style="padding-right: 0;">' . $dir->chmod . $dir->chmod . $dir->owner . $dir->owner . $dir->owner . $dir->editeddate . $dir->name . ' </td><br>';
            }

            if ($files !== null || $dir !== null) {
                $str = $str . '</tr>';
            }
            $sender->send("message|" . $str);
        }
    }
}
