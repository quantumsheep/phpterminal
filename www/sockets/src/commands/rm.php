<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class rm implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "rm [OPTION]... [FILE]...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Remove (unlink) the FILE(s).";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "By default, rm does not remove directories.  Use the --recursive (-r or -R)
    option to remove each listed directory, too, along with all of its contents.

    To remove a file whose name starts with a '-', for example '-foo',
    use one of these commands:
      rm -- -foo

      rm ./-foo

    Note that if you use rm to remove a file, it might be possible to recover
    some of its contents, given sufficient expertise and/or time.  For greater
    assurance that the contents are truly unrecoverable, consider using shred.

    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/rm>
    or available locally via: info '(coreutils) rm invocation'";

    /**
     * Command's options
     */
    const OPTIONS = [
        "-f, --force" => "ignore nonexistent files and arguments, never prompt",
        "-i" => "prompt before every removal",
        "-I" => "prompt once before removing more than three files, or
        when removing recursively; less intrusive than -i,
        while still giving protection against most mistakes
--interactive[=WHEN]  prompt according to WHEN: never, once (-I), or
        always (-i); without WHEN, prompt always
--one-file-system  when removing a hierarchy recursively, skip any
        directory that is on a file system different from
        that of the corresponding command line argument
--no-preserve-root  do not treat '/' specially
--preserve-root   do not remove '/' (default)",
        "-r, -R, --recursive" => "remove directories and their contents recursively",
        "-d, --dir" => "remove empty directories",
        "-v" => "explain what is being done",
        "--help" => "display this help and exit",
        "--version" => "output version information and exit",
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
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter rm --help for more information");
            return;
        }

        $currentPosition = CommandAsset::getIdDirectory($db, $terminal_mac, $data->position);
        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        $paramArray = explode(" ", $parameters);

        foreach ($paramArray as $param) {
            $type = CommandAsset::checkBoth($terminal_mac, $param, $currentPosition, $db);

            if ($type == 2) {
                $parentId = CommandAsset::getParentId($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $param));
                CommandAsset::deleteFile($db, $data, $sender, $terminal_mac, $param, $parentId);
            } else if ($type == 1) {
                // $currentPath = CommandAsset::getIdDirectory($db, $terminal_mac, CommandAsset::getAbsolute($data->position, $path));
                // $files = CommandAsset::getFiles($db, $terminal_mac, $currentPath);
                // $dirs = CommandAsset::getDirectories($db, $terminal_mac, $currentPath);
            } else {
                $sender->send('message|<br>' . $param . ' didnt exist.');
            }
        }
    }
}
