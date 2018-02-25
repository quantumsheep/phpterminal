<?php
use Alph\Sockets\App;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;

require __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(new HttpServer(new WsServer(new App())), 810);

$server->run();