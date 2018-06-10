<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;
use Alph\Services\CommandAsset;

/**
 * chmod
 */
class chmod implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "";

    /**
     * Command's options
     */
    const OPTIONS = [
        "" => "",
        "" => "",
    ];

    /**
     * Command's arguments
     */
    const ARGUMENTS = [
        "PATTERN" => "",
    ];

    /**
     * Command's exit status
     */
    const EXIT_STATUS = "";

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
        // If no params
        if (empty($parameters)) {
            $sender->send("message|<br>Operand missing <br>please enter chmod --help for more information");
            return;
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::GetPathParameters($parameters, $data->position);

        // Change simple parameters into array for further treatement
        $Files = explode(" ", $parameters);

        for($i=0;$i<count($options);$i++){
            unset($Files[$i]);
        }

        $askedChmod = $Files[count($options)];
        unset($Files[count($options)]);
        
        if (is_numeric($askedChmod)) {
            if (!empty($Files)) {
                $Files = CommandAsset::fullPathFromParameters($Files, $data->position);
            }
            CommandAsset::concatenateParameters($Files, $pathParameters, $quotedParameters);
            if (empty($options)) {
                return CommandAsset::stageChangeChmod($db, $data, $sender, $terminal_mac, $Files, $askedChmod);
            }
        } else {
            $sender->send("message|<br>chmod: missing operand after ‘".$askedChmod."’<br>Try 'chmod --help' for more information.");
        }
    }
}
