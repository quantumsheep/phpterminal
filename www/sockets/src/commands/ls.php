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
        $files = CommandAsset::getFiles($db, $terminal_mac, $currentPath);
        $dirs = CommandAsset::getDirectories($db, $terminal_mac, $currentPath);

        if (!empty($parameters)) {
            $options = CommandAsset::getOptions($parameters);
            $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);

            $paramArray = explode(" ", $parameters);

            foreach ($paramArray as $path) {
                $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $path));
                $files = CommandAsset::getFiles($db, $terminal_mac, $currentPath);
                $dirs = CommandAsset::getDirectories($db, $terminal_mac, $currentPath);

                $sender->send("message|<br>" . $path . " :<br>");
                CommandAsset::ls($db, $terminal_mac, $sender, $files, $dirs, $currentPath, $options);
            }
        } else {
            $options = [];
            return CommandAsset::ls($db, $terminal_mac, $sender, $files, $dirs, $currentPath, $options);
        }
    }
}
