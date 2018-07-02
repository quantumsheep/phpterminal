<?php
namespace Alph\Commands;

use Alph\Models\Model;
use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class nano implements CommandInterface
{
    const USAGE = "nano FILE...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Create a specified file with a little interface.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "Create a specified file with a little interface.";

    /**
     * Command's exit status
     */
    const EXIT_STATUS = "Returns exit status of command or success if command is null.";

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
        if ($cmd == "exit") {
            $data->controller = null;
            $data->private_input = false;

            $data->data->nano->pending = [];

            return;
        } else if ($cmd == "save") {
            @list($path, $content) = explode('|', $parameters, 2);

            var_dump($parameters);

            CommandAsset::createOrUpdateFile($db, $data, $sender, $path, $terminal_mac, $content);
        } else {
            if (isset($data->data->nano) && !empty($data->data->nano->pending)) {
                do {
                    $absolute_path = CommandAsset::getAbsolute($data->position, $data->data->nano->pending[0]);
                    $file = CommandAsset::getFile($db, $absolute_path, $terminal_mac);

                    $file->name = $absolute_path;

                    $sender->send('action|nano|' . \json_encode($file));

                    \array_shift($data->data->nano->pending);
                } while (empty($file->idfile) && !empty($data->data->nano->pending) && isset($data->data->nano->pending[0]));
            } else {
                $data->controller = "\\Alph\\Commands\\nano::call";
                $data->private_input = true;

                if ($parameters) {
                    $data->data->nano = new Model();
                    $data->data->nano->pending = CommandAsset::getPathParameters($parameters, $data->position);

                    $options = CommandAsset::getOptions($parameters);

                    if (!empty($parameters)) {
                        $remaining = explode(' ', $parameters);

                        foreach ($remaining as &$part) {
                            $data->data->nano->pending[] = trim($part);
                        }
                    }

                    do {

                        $absolute_path = CommandAsset::getAbsolute($data->position, $data->data->nano->pending[0]);
                        $parentId = CommandAsset::getParentId($db, $terminal_mac, $absolute_path);
                        $elementName = explode("/", $absolute_path)[count(explode("/", $absolute_path)) - 1];
                        if (!CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $absolute_path, CommandAsset::getChmod($db, $terminal_mac, $elementName, $parentId), 2)) {
                            return $sender->send("message|<br>You can't write this file");
                        }
                        $file = CommandAsset::getFile($db, $absolute_path, $terminal_mac);

                        $file->name = $absolute_path;

                        $sender->send('action|nano|' . \json_encode($file));

                        \array_shift($data->data->nano->pending);
                    } while (empty($file->idfile) && !empty($data->data->nano->pending) && isset($data->data->nano->pending[0]));
                }
            }
        }
    }
}
