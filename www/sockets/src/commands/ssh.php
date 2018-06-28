<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Alph\Models\Model;
use Ratchet\ConnectionInterface;
use WebSocket\Client;

class ssh implements CommandInterface
{
    const USAGE = "ssh user@host";

    const OPTIONS = [
        "-p" => "Select a specific port",
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
        // if(!empty($data->data->ssh->options["host"])) {
            
        // }

        if (empty($parameters)) {
            return help::call(...\func_get_args());
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
            return help::call(...\func_get_args());
        }

        $data->controller = "\\Alph\\Commands\\ssh::call";
        
        $data->data->ssh = new Model();

        $data->data->ssh->options = $options;
        // $data->data["ssh"]->conn->data = new SenderData();
    
        $data->data->ssh->client = new Client("ws://localhost:800/", [
            "headers" => [
                "cookie" => 'alph_sess=' . $sess_id . '; terminal=FD-7A-FF-CE-47-AD'
            ]
        ]);

        //$data->data->ssh->client->connect();

        var_dump($data->data->ssh->client);
    }
}
