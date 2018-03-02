<?php
namespace Alph\Services;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class CommandHandler implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Get cookie HTTP header
        $cookies = $conn->httpRequest->getHeader('Cookie');

        // If there is no values in the cookie header, stop the process
        if (empty($cookies)) {
            return;
        }

        // Parse the cookies to obtain each cookies separately
        $parsed_cookies = \GuzzleHttp\Psr7\parse_header($cookies);

        // 
        if (!isset($parsed_cookies[0]["alph_sess"])) {
            return;
        }

        session_id($parsed_cookies[0]["alph_sess"]);
        new \Alph\Services\SessionHandler;

        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending command "%s" to %d other connection%s' . "\n", $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($_SESSION["hi"]);
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
