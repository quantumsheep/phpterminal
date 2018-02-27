<?php
namespace Alph\Sockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class App implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        ini_set('session.save_path', "D:\\Projets webs\\phpterminal\\www\\session");
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        session_id(\GuzzleHttp\Psr7\parse_header($conn->httpRequest->getHeader('Cookie'))[0]['PHPSESSID']);
        session_start();

        var_dump($_SESSION);
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($_SESSION["hi"]);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}