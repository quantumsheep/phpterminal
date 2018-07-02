<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class history implements CommandInterface
{
    const USAGE = "history [-c] [-d offset]";

    const SHORT_DESCRIPTION = "Display or manipulate the history list.";

    const FULL_DESCRIPTION = "Display the history list with line numbers, prefixing each modified
    entry with a `*'.  An argument of N lists only the last N entries.";

    const OPTIONS = [
        "-c" => "clear the history list by deleting all of the entries",
        "-d" => "offset delete the history entry at position OFFSET.",
    ];

    const ARGUMENTS = [
        "PATTERN" => "    If FILENAME is given, it is used as the history file.  Otherwise,
        if HISTFILE has a value, that is used, else ~/.bash_history.

        If the HISTTIMEFORMAT variable is set and not null, its value is used
        as a format string for strftime(3) to print the time stamp associated
        with each displayed history entry.  No time stamps are printed otherwise.",
    ];

    const EXIT_STATUS = "Returns success unless an invalid option is given or an error occurs.";

    /**
     * Call the command
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        //If history is enter without parameters
        if ($parameters == null) {
            //Get all the history from the BDD
            $check = $db->prepare("SELECT command FROM terminal_user_history WHERE terminal_user = :terminal_user AND status='1'");
            $check->bindParam(":terminal_user", $data->user->idterminal_user);
            $check->execute();
            $history = $check->fetchAll();

            $i = 1;

            //Send all the history at 1 to the size of the history
            foreach ($history as $value) {
                $sender->send("message|<br>" . $i++ . " " . $value["command"]);
            }

        } else if ($parameters != null) {
            $params_parts = explode(' ', $parameters);

            //If the user type '-c' after history, set the status to 0 for all the history
            if (in_array('-c', $params_parts) && \count($params_parts) <= 1) {
                $check = $db->prepare("UPDATE terminal_user_history SET status=0 WHERE terminal_user = :terminal_user");
                $check->bindParam(":terminal_user", $data->user->idterminal_user);
                $check->execute();

                //If the user type '-d' and a number after history, set the status to 0 for all the number set by the user
            } else if (in_array('-d', $params_parts) && \count($params_parts) <= 2) {
                $check = $db->prepare("UPDATE terminal_user_history SET status=0 WHERE status=1 AND terminal_user = :terminal_user ORDER BY idhistory ASC LIMIT :num");
                $check->bindParam(":terminal_user", $data->user->idterminal_user);
                $check->bindParam(":num", $params_parts[1], \PDO::PARAM_INT);
                $check->execute();
            }
        }
    }
}
