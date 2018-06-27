<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;
use Alph\Services\CommandAsset;

class test implements CommandInterface
{
    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     */

    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        $fullParameters = CommandAsset::mvIsolateElement($parameters);
        if(count($fullParameters) < 2){
            return $sender->send("message|<br>mv: target operand missing" . (count($fullParameters) == 1? " after " . $fullParameters[0] . ".": "."));
        }

        $options = CommandAsset::mvGetOptions($fullParameters);
        $target = CommandAsset::getTarget($parameters, $fullParameters);
        var_dump($target);
    }
}
