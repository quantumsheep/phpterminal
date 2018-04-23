<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class cd implements CommandInterface
{
    const USAGE = "cd [-L|[-P [-e]] [-@]] [dir]";

    const SHORT_DESCRIPTION = "Change the shell working directory.";
    const FULL_DESCRIPTION = "Change the current directory to DIR.  The default DIR is the value of the HOME shell variable.
    <br>
    The variable CDPATH defines the search path for the directory containing
    DIR.  Alternative directory names in CDPATH are separated by a colon (:).
    A null directory name is the same as the current directory.  If DIR begins
    with a slash (/), then CDPATH is not used.
    <br>
    If the directory is not found, and the shell option `cdable_vars' is set,
    the word is assumed to be  a variable name.  If that variable has a value,
    its value is used for DIR.";

    const OPTIONS = [
        "-L" => "force symbolic links to be followed: resolve symbolic links in DIR after processing instances of `..'",
        "-P" => "use the physical directory structure without following symbolic links: resolve symbolic links in DIR before processing instances of `..'",
        "-e" => "if the -P option is supplied, and the current working directory cannot be determined successfully, exit with a non-zero status",
        "-@" => "on systems that support it, present a file with extended attributes as a directory containing the file attributes",
    ];

    const EXIT_STATUS = "Returns 0 if the directory is changed, and if \$PWD is set successfully when -P is used; non-zero otherwise.";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $goPath = true;

        // cd by himself return to root
        if (empty($parameters)) {
            return $data->position = '/';
        }

        // Get element
        $path = explode(' ', $parameters);

        // Test if multi argument
        if(isset($path[1])){
            $sender->send("<br>Error : Multiple argument");
            return;
        }

        // 
        $path = $path[0];
        if (empty($path)) {
            return;
        }

        // case parameters is help
        if ($path == '--help') {
            $parameters = 'cd';
            return help::call(...\func_get_args());
        }

        // Get each element of path
        $path = explode('/', $path);

        if ($path[0] == '') {
            $data->position = join('/', $path);
        } else {
            if ($path[0] == '.') {
                $path = array_slice($path, 1);
            }
        }

        for ($i = 0; $i < count($path); $i++) {

            // Check if directory exists
            $name = $path[$i];
            $check = $db->prepare("SELECT name FROM terminal_directory WHERE name = :name");
            $check->bindParam(":name", $name);
            $check->execute();
            if ($check->rowCount() == 0 && $data->position != "/") {
                $sender->send("<br>Error : " . $name . " directory doesn't exists");
                $goPath = false;

            }
        }

        if($goPath == true){
            // Modify position
            $data->position .= ($data->position[\strlen($data->position) - 1] == '/' ? '' : '/') . join('/', $path);
        }
        
    
    }
}
