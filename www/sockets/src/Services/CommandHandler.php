<?php
namespace Alph\Services;

use Alph\Models\Model;
use Alph\Services\History;
use Alph\Services\SenderData;
use Alph\Services\Session;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class CommandHandler implements MessageComponentInterface
{
    protected $clients;

    /**
     * @var \PDO
     */
    private $db;
    private $commands;
    public $data;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->db = \Alph\Services\Database::connect();
        $this->commands = \Alph\Services\DefinedCommands::get();

        /**
         * @var SenderData[]
         */
        $this->data = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages later
        $this->clients->attach($conn);

        $this->data[$conn->resourceId] = new SenderData;

        $conn->send("message|login as: ");
    }

    public function onMessage(ConnectionInterface $sender, $cmd)
    {
        // Get cookie HTTP header
        $cookies = $sender->httpRequest->getHeader('Cookie');

        if (empty($cookies)) {
            $cookies = $sender->httpRequest->getHeader('cookie');
        }

        // If there is no values in the cookie header, stop the process
        if (!empty($cookies)) {
            // Parse the cookies to obtain each cookies separately
            $parsed_cookies = \GuzzleHttp\Psr7\parse_header($cookies);

            // Check if alph_sess is defined in the sender's cookies
            if (isset($parsed_cookies[0]["alph_sess"]) && isset($parsed_cookies[0]["terminal"])) {
                // Read the sender's session data
                $sender_session = Session::read($parsed_cookies[0]["alph_sess"]);

                // Check if the idaccount is present in the sender's session
                if (!empty($sender_session["account"])) {
                    // Check if the sender is actually connected to an account
                    if ($this->data[$sender->resourceId]->connected) {
                        // Parse the command in 2 parts: the command and the parameters, the '@' remove the error if parameters index is null
                        @list($cmd, $parameters) = explode(' ', $cmd, 2);

                        $lineReturn = true;

                        // Check if the command exists
                        if ($this->data[$sender->resourceId]->controller != null || in_array($cmd, $this->commands)) {
                            $this->callCommand($this->db, $this->clients, $this->data[$sender->resourceId], $sender, $sender_session, $parsed_cookies[0]["alph_sess"], $parsed_cookies[0]["terminal"], $cmd, $parameters, $lineReturn);
                        } else {
                            $sender->send("message|<br><span>-bash: " . $cmd . ": command not found</span>");
                        }

                        if (!$this->data[$sender->resourceId]->private_input) {
                            $sender->send("message|" . ($lineReturn ? "<br>" : "") . "<span>" . $this->data[$sender->resourceId]->user->username . "@54.37.69.220:" . $this->data[$sender->resourceId]->position . "# </span>");
                        }

                        // Push the command into the history
                        History::push($this->db, $this->data[$sender->resourceId]->user->idterminal_user, $sender_session["account"]->idaccount, $cmd . (!empty($parameters) ? ' ' . $parameters : ''));
                    } else {
                        $terminal = str_replace(['.', ':'], '-', strtoupper($parsed_cookies[0]["terminal"]));
                        $this->askForCredentials($this->db, $this->data[$sender->resourceId], $sender, $cmd, $terminal);
                    }
                } else {
                    $sender->send("message|<br><span>alph: account connection error</span>");
                }
            } else {
                $sender->send("message|<br><span>alph: terminal connection error</span>");
            }
        }
    }

    public static function callCommand(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface &$sender, $sender_session, string $sess_id, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        $controller = $data->controller != null ? $data->controller : '\\Alph\\Commands\\' . $cmd . '::call';

        // Call the command with arguments
        \call_user_func_array($controller, [
            $db,
            $clients,
            &$data,
            $sender,
            $sess_id,
            $sender_session,
            $terminal_mac,
            $cmd,
            $parameters,
            &$lineReturn,
        ]);
    }

    public static function askForCredentials(\PDO $db, SenderData &$data, ConnectionInterface &$sender, string $cmd, string $terminal)
    {
        if (!empty($data->user->username) && !isset($data->user->password)) {
            $stmp = $db->prepare("SELECT idterminal_user, password FROM TERMINAL_USER WHERE username = :username AND terminal = :terminal;");

            $stmp->bindParam(":username", $data->user->username);
            $stmp->bindParam(":terminal", $terminal);

            $stmp->execute();

            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

            if (\password_verify($cmd, $row["password"])) {
                $greetings = [
                    "Alph 1.0.6-7 (2018-29-03)",
                    "",
                    "The programs included with a simulated Debian GNU/Linux system;",
                    "the exact distribution terms for each program are described in the",
                    "individual files in /usr/share/doc/*/copyright.",
                    "",
                    "Simulated Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent",
                    "permitted by applicable law.",
                    "Last login: Mon Mar 28 01:54:13 2018 from 54.37.69.220",
                ];

                $data->data = new Model();

                foreach ($greetings as &$greet) {
                    $sender->send("message|<br><span>" . $greet . "</span>");
                }

                $data->position = "/";

                $sender->send("message|<br><span>" . $data->user->username . "@54.37.69.220:" . $data->position . "# </span>");

                $sender->send("action|show input");

                $data->connected = true;
                $data->user->idterminal_user = $row["idterminal_user"];
            } else {
                $sender->send("message|<br><span>Access denied.</span>");
                $sender->send("message|<br><span>" . $data->user->username . "@54.37.69.220's password: <span>");
            }
        } else {
            $data->user->username = $cmd;

            $sender->send("action|hide input");
            $sender->send("message|<br><span>" . $data->user->username . "@54.37.69.220's password: <span>");
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        unset($this->data[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
