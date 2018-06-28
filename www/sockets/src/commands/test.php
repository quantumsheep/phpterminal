<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

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
        $absolutePathFullParameters = [];

        $fullParameters = CommandAsset::mvIsolateElement($parameters);
        if (count($fullParameters) < 2) {
            return $sender->send("message|<br>mv: target operand missing" . (count($fullParameters) == 1 ? " after " . $fullParameters[0] . "." : "."));
        }
        $options = CommandAsset::mvGetOptions($fullParameters);

        $target = CommandAsset::getTarget($parameters, $fullParameters);

        foreach ($fullParameters as $parameter) {
            $cleanedparameter = CommandAsset::cleanQuote($parameter);
            $absolutePathFullParameters[] = CommandAsset::getAbsolute($data->position, $cleanedparameter);
        }

        $cleanedTarget = CommandAsset::cleanQuote($target);
        $absolutePathTarget = CommandAsset::getAbsolute($data->position, $cleanedTarget);

        // First check what will be the action depending on the target essence
        var_dump(CommandAsset::getParentId($db, $terminal_mac, $absolutePathTarget));
        var_dump(CommandAsset::checkDirectoryExistence($terminal_mac, $cleanedTarget, CommandAsset::getParentId($db, $terminal_mac, $absolutePathTarget), $db));

        // if Target is an actual directory
        if (CommandAsset::checkDirectoryExistence($terminal_mac, $cleanedTarget, CommandAsset::getParentId($db, $terminal_mac, $absolutePathTarget), $db)) {
            foreach($absolutePathFullParameters as $absolutePathParameter){
                CommandAsset::updatePosition($db, $terminal_mac, $absolutePathParameter, $sender);
            }   
        }

    }
}
