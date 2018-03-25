<?php
use Alph\Services\CommandHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

$server = IoServer::factory(new HttpServer(new WsServer(new CommandHandler())), 810);

$server->run();