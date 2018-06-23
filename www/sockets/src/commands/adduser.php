<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class adduser implements CommandInterface
{
    const USAGE = "adduser [--home DIR] [--shell SHELL] [--no-create-home] [--uid ID]
    [--firstuid ID] [--lastuid ID] [--gecos GECOS] [--ingroup GROUP | --gid ID]
    [--disabled-password] [--disabled-login] [--add_extra_groups] USER";

    const OPTIONS = [
        "--quiet | -q" => "don't give process information to stdout",
        "--force-badname" => "allow usernames which do not match the NAME_REGEX configuration variable",
        "--help | -h" => "usage message",
        "--version | -v" => "version number and copyright",
        "--conf | -c FILE" => "use FILE as configuration file",
    ];

    const EXIT_STATUS = "Returns 0 if the directory is changed, and if \$PWD is set successfully when -P is used; non-zero otherwise.";

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
        if (!empty($parameters)) {
            $pathsParameters = CommandAsset::getPathParameters($parameters, $data->position);
            $options = CommandAsset::getOptions($parameters);

            $remaining = explode(' ', $parameters);

            if (count($remaining) === 1) {
                $data->controller = "\\Alph\\Commands\\adduser::call";
                $data->private_input = true;

                $data->data->adduser = [
                    'nickname' => $remaining[0],
                ];

                $sender->send('action|hide input');
                $sender->send('message|<br><span>Enter new UNIX password: </span>');
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
                    $stmp = $db->prepare('CALL NewUser(:terminal_mac, :nickname, :password);');

                    $stmp->bindParam(':terminal_mac', $terminal_mac);
                    $stmp->bindParam(':nickname', $data->data->adduser['nickname']);
                    $stmp->bindParam(':password', $cmd);

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
                }
            }
        }
    }
}
