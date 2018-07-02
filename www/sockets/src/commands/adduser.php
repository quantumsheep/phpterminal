<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class adduser implements CommandInterface
{
    const USAGE = "adduser USER";

    const EXIT_STATUS = "Returns 0 if the directory is changed, and if \$PWD is set successfully.";

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
        if (CommandAsset::isRoot($db, $terminal_mac, $data->user->idterminal_user)) {
            if (!empty($parameters)) {
                $pathsParameters = CommandAsset::getPathParameters($parameters, $data->position);
                $options = CommandAsset::getOptions($parameters);

                $remaining = explode(' ', $parameters);

                if (count($remaining) === 1) {
                    $stmp = $db->prepare('SELECT 1 FROM TERMINAL_USER WHERE terminal = :terminal AND username = :username;');

                    $stmp->bindParam(':terminal', $terminal_mac);
                    $stmp->bindParam(':username', $remaining[0]);

                    $stmp->execute();

                    if ($stmp->rowCount() === 0) {
                        $data->controller = "\\Alph\\Commands\\adduser::call";
                        $data->private_input = true;

                        $data->data->adduser = [
                            'nickname' => $remaining[0],
                        ];

                        $sender->send('action|hide input');
                        $sender->send('message|<br><span>Enter new UNIX password: </span>');
                    } else {
                        $sender->send("message|<br><span>adduser: The user `" . $remaining[0] . "' already exists.</span>");
                    }
                } else {
                    help::call(...\func_get_args());
                }
            } else if (!empty($data->controller)) {
                if (!isset($data->data->adduser['pass1'])) {
                    $data->data->adduser['pass1'] = $cmd;
                    $sender->send('message|<br><span>Retype new UNIX password: </span>');
                } else {
                    if ($cmd !== $data->data->adduser['pass1']) {
                        $sender->send('message|<br><span>Sorry, passwords do not match</span>');
                        $sender->send('message|<br><span>passwd: Authentication token manipulation error</span>');
                        $sender->send('message|<br><span>passwd: password unchanged</span>');

                        unset($data->data->adduser['pass1']);

                        $sender->send('message|<br><span>Enter new UNIX password: </span>');
                    } else {
                        $stmp = $db->prepare('SELECT 1 FROM TERMINAL_USER WHERE terminal = :terminal AND username = :username');

                        $stmp->bindParam(':terminal', $terminal_mac);
                        $stmp->bindParam(':username', $data->data->adduser['nickname']);

                        $stmp->execute();

                        if ($stmp->rowCount() === 0) {
                            $password = \password_hash($cmd, PASSWORD_BCRYPT);
                            $stmp = $db->prepare('CALL NewUser(:terminal_mac, :nickname, :password);');

                            $stmp->bindParam(':terminal_mac', $terminal_mac);
                            $stmp->bindParam(':nickname', $data->data->adduser['nickname']);
                            $stmp->bindParam(':password', $password);

                            $stmp->execute();

                            if ($stmp->rowCount() === 1) {
                                $result = $stmp->fetch(\PDO::FETCH_ASSOC);

                                if (isset($result['gid']) && !empty($result['gid'])) {
                                    $sender->send('message|<br><span>Adding user `' . $data->data->adduser['nickname'] . '\' ...</span>');
                                    $sender->send('message|<br><span>Adding new group `' . $data->data->adduser['nickname'] . '\' (' . $result['gid'] . ') ...</span>');
                                    $sender->send('message|<br><span>Adding new user `' . $data->data->adduser['nickname'] . '\' (' . $result['gid'] . ') with group \`' . $data->data->adduser['nickname'] . '\' ...</span>');

                                    unset($data->data->adduser);

                                    $data->controller = null;
                                    $data->private_input = false;
                                }
                            }
                        } else {
                            $sender->send("message|<br><span>adduser: The user `" . $data->data->adduser['nickname'] . "' already exists.</span>");

                            unset($data->data->adduser);

                            $data->controller = null;
                            $data->private_input = false;
                        }
                    }
                }
            }

        } else {
            return $sender->send("message|<br>You've to be root to add a user.");
        }

    }
}
