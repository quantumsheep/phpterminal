<?php
namespace Alph\Commands;

use Alph\Models\Model;
use Alph\Models\ViewTerminal_InfoModel;
use Alph\Services\CommandHandler;
use Alph\Services\CommandInterface;
use Alph\Services\History;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class ssh implements CommandInterface
{
    const USAGE = "ssh [user@host] -p [PORT]";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Connect to a host at the specified port.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "Connect to a host at the specified port.";

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
        if ($data->controller == "\\Alph\\Commands\\ssh::call") {
            if ($data->data->ssh->data->connected) {
                if ($cmd == 'exit') {
                    $data->controller = null;
                    $data->private_input = false;
                    unset($data->data->ssh);

                    return;
                }

                if ($data->data->ssh->data->controller != null || in_array($cmd, \Alph\Services\DefinedCommands::get())) {
                    CommandHandler::callCommand($db, $clients, $data->data->ssh->data, $sender, $sender_session, $sess_id, $cmd, $parameters, $data->data->ssh->lineReturn);
                } else {
                    $sender->send("message|<br><span>-bash: " . $cmd . ": command not found</span>");
                }

                if (!$data->data->ssh->data->private_input) {
                    $sender->send("message|" . ($data->data->ssh->lineReturn ? "<br>" : "") . "<span>" . $data->data->ssh->data->user->username . "@" . $data->data->ssh->data->terminal->publicipv4 . ":" . $data->data->ssh->data->position . "# </span>");
                }

                // Push the command into the history
                History::push($db, $data->data->ssh->data->user->idterminal_user, $sender_session["account"]->idaccount, $cmd . (!empty($parameters) ? ' ' . $parameters : ''));
            } else {
                CommandHandler::askForCredentials($db, $data->data->ssh->data, $sender, $cmd);
            }

            return;
        }

        if (empty($parameters)) {
            return help::call($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, "ssh", $lineReturn);
        }

        $options = [
            "port" => 22,
        ];

        $splitedParameters = explode(' ', $parameters);

        foreach ($splitedParameters as $key => $param) {
            if ($param == '-p') {
                $nextIndex = $key + 1;

                if (!isset($splitedParameters[$nextIndex])) {
                    return $sender->send("message|<span>option requires an argument -- p</span>");
                }

                $options["port"] = intval($splitedParameters[$nextIndex]);

                if ($options["port"] <= 0) {
                    return $sender->send("message|<span>Bad port '" . $options["port"] . "'</span>");
                }

                unset($splitedParameters[$nextIndex]);
            } else {
                @list($user, $host) = explode('@', $param);

                if (!empty($user) && !empty($host)) {
                    $options["user"] = $user;
                    $options["host"] = $host;
                }
            }
        }

        if (empty($options["port"])) {
            return $sender->send("message|<span>Parameter -p required</span>");
        }

        if (empty($options["user"]) && empty($options["host"])) {
            return help::call($db, $clients, $data, $sender, $sess_id, $sender_session, $terminal_mac, $cmd, "ssh", $lineReturn);
        }

        $stmp = $db->prepare("SELECT terminalmac, networkmac, privateipv4, publicipv4, sshport FROM TERMINAL_INFO WHERE terminalmac = (SELECT terminal FROM PRIVATEIP, (SELECT mac FROM NETWORK WHERE ipv4 = :ipv4) as net WHERE ip = (SELECT ip FROM PORT WHERE network = net.mac AND port = :port AND ipport = 22) AND PRIVATEIP.network = net.mac)");

        $stmp->bindParam(':ipv4', $options["host"]);
        $stmp->bindParam(':port', $options["port"]);

        $stmp->execute();

        if ($stmp->rowCount() === 0) {
            return $sender->send('message|<span>ssh: connect to host ' . $options["host"] . ' port ' . $options["port"] . ': Connection timed out</span><br>');
        }

        $data->controller = "\\Alph\\Commands\\ssh::call";

        $data->data->ssh = new Model();

        $data->data->ssh->options = $options;

        $data->data->ssh->data = new SenderData();
        $data->data->ssh->data->terminal = ViewTerminal_InfoModel::map($stmp->fetch());

        $data->data->ssh->data->private_input = false;
        $data->data->ssh->lineReturn = true;

        $data->private_input = true;

        CommandHandler::askForCredentials($db, $data->data->ssh->data, $sender, $data->data->ssh->options["user"], $data->data->ssh->data->terminal->terminalmac);
    }
}
