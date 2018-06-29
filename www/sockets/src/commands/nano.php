<?php
namespace Alph\Commands;

use Alph\Models\Model;
use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class nano implements CommandInterface
{
    const USAGE = "nano [OPTIONS] [[+LINE,COLUMN] FILE]...";

    const OPTIONS = [
        "+LINE,COLUMN" => "Start at line LINE, column COLUMN",
        "-A             --smarthome" => "Enable smart home key",
        "-B             --backup" => "Save backups of existing files",
        "-C <dir>       --backupdir=<dir>" => "Directory for saving unique backup files",
        "-D             --boldtext" => "Use bold instead of reverse video text",
        "-E             --tabstospaces" => "Convert typed tabs to spaces",
        "-F             --multibuffer" => "Read a file into a new buffer by default",
        "-G             --locking" => "Use (vim-style) lock files",
        "-H             --historylog" => "Log & read search/replace string history",
        "-I             --ignorercfiles" => "Don't look at nanorc files",
        "-K             --rebindkeypad" => "Fix numeric keypad key confusion problem",
        "-L             --nonewlines" => "Don't add newlines to the ends of files",
        "-N             --noconvert" => "Don't convert files from DOS/Mac format",
        "-O             --morespace" => "Use one more line for editing",
        "-P             --positionlog" => "Log & read location of cursor position",
        "-Q <str>       --quotestr=<str>" => "Quoting string",
        "-R             --restricted" => "Restricted mode",
        "-S             --smooth" => "Scroll by line instead of half-screen",
        "-T <#cols>     --tabsize=<#cols>" => "Set width of a tab to #cols columns",
        "-U             --quickblank" => "Do quick statusbar blanking",
        "-V             --version" => "Print version information and exit",
        "-W             --wordbounds" => "Detect word boundaries more accurately",
        "-X <str>       --wordchars=<str>" => "Which other characters are word parts",
        "-Y <str>       --syntax=<str>" => "Syntax definition to use for coloring",
        "-c             --constantshow" => "Constantly show cursor position",
        "-d             --rebinddelete" => "Fix Backspace/Delete confusion problem",
        "-h             --help" => "Show this help text and exit",
        "-i             --autoindent" => "Automatically indent new lines",
        "-k             --cut" => "Cut from cursor to end of line",
        "-l             --linenumbers" => "Show line numbers in front of the text",
        "-m             --mouse" => "Enable the use of the mouse",
        "-n             --noread" => "Do not read the file (only write it)",
        "-o <dir>       --operatingdir=<dir>" => "Set operating directory",
        "-p             --preserve" => "Preserve XON (^Q) and XOFF (^S) keys",
        "-q             --quiet" => "Silently ignore startup issues like rc file errors",
        "-r <#cols>     --fill=<#cols>" => "Set hard-wrapping point at column #cols",
        "-s <prog>      --speller=<prog>" => "Enable alternate speller",
        "-t             --tempfile" => "Auto save on exit, don't prompt",
        "-u             --unix" => "Save a file by default in Unix format",
        "-v             --view" => "View mode (read-only)",
        "-w             --nowrap" => "Don't hard-wrap long lines",
        "-x             --nohelp" => "Don't show the two help lines",
        "-z             --suspend" => "Enable suspension",
        "-$             --softwrap" => "Enable soft line wrapping",
    ];

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
        if ($cmd == "exit") {
            $data->controller = null;
            $data->private_input = false;

            $data->data->nano->pending = [];

            return;
        } else if ($cmd == "save") {
            @list($path, $content) = explode('|', $parameters, 2);

            var_dump($parameters);

            CommandAsset::createOrUpdateFile($db, $data, $sender, $path, $terminal_mac, $content);
        } else {
            if (isset($data->data->nano) && !empty($data->data->nano->pending)) {
                do {
                    $absolute_path = CommandAsset::getAbsolute($data->position, $data->data->nano->pending[0]);
                    $file = CommandAsset::getFile($db, $absolute_path, $terminal_mac);

                    $file->name = $absolute_path;

                    $sender->send('action|nano|' . \json_encode($file));

                    \array_shift($data->data->nano->pending);
                } while (empty($file->idfile) && !empty($data->data->nano->pending) && isset($data->data->nano->pending[0]));
            } else {
                $data->controller = "\\Alph\\Commands\\nano::call";
                $data->private_input = true;

                if ($parameters) {
                    $data->data->nano = new Model();
                    $data->data->nano->pending = CommandAsset::getPathParameters($parameters, $data->position);

                    $options = CommandAsset::getOptions($parameters);

                    if (!empty($parameters)) {
                        $remaining = explode(' ', $parameters);

                        foreach ($remaining as &$part) {
                            $data->data->nano->pending[] = trim($part);
                        }
                    }

                    do {
                        $absolute_path = CommandAsset::getAbsolute($data->position, $data->data->nano->pending[0]);
                        $file = CommandAsset::getFile($db, $absolute_path, $terminal_mac);

                        $file->name = $absolute_path;

                        $sender->send('action|nano|' . \json_encode($file));

                        \array_shift($data->data->nano->pending);
                    } while (empty($file->idfile) && !empty($data->data->nano->pending) && isset($data->data->nano->pending[0]));
                }
            }
        }
    }
}
