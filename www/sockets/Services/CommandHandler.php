<?php
namespace Alph\Services;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class CommandHandler implements MessageComponentInterface
{
    protected $clients;
    private $db;
    private $commands;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->db = \Alph\Services\Database::connect();
        $this->commands = \Alph\Services\DefinedCommands::get();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $sender, $cmd)
    {
        // Get cookie HTTP header
        $cookies = $sender->httpRequest->getHeader('Cookie');

        // If there is no values in the cookie header, stop the process
        if (!empty($cookies)) {
            // Parse the command in 2 parts: the command and the parameters, the '@' remove the error if parameters index is null
            @list($cmd, $parameters) = explode(' ', $cmd, 2);

            // Check if the command exists
            if (in_array($cmd, $this->commands)) {
                // Parse the cookies to obtain each cookies separately
                $parsed_cookies = \GuzzleHttp\Psr7\parse_header($cookies);

                // Check if alph_sess is defined in the sender's cookies
                if (isset($parsed_cookies[0]["alph_sess"])) {
                    // Read the sender's session data
                    $sender_session = \Alph\Services\Session::read($this->db, $parsed_cookies[0]["alph_sess"]);

                    // Call the command with arguments
                    \call_user_func_array('\\Alph\\Commands\\' . $cmd . '::call', [$this->db, $this->clients, $sender, $parsed_cookies[0]["alph_sess"], $cmd, $parameters]);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
