<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\DefinedCommands;
use Ratchet\ConnectionInterface;

class help implements CommandInterface
{
    const USAGE = "help [-dms] [pattern ...]";

    const SHORT_DESCRIPTION = "Display information about builtin commands.";
    const FULL_DESCRIPTION = "Displays brief summaries of builtin commands.  If PATTERN is specified, gives detailed help on all commands matching PATTERN, otherwise the list of help topics is printed.";

    const OPTIONS = [
        "-d" => "output short description for each topic",
        "-s" => "output only a short usage synopsis for each topic matching PATTERN",
    ];

    const ARGUMENTS = [
        "PATTERN" => "Pattern specifiying a help topic",
    ];

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
    public static function call(\PDO $db, \SplObjectStorage $clients, ConnectionInterface $sender, string $sess_id, string $cmd, $parameters)
    {
        $topics = [];
        $done = false;
        $option_short = false;
        $option_usageonly = false;
        $empty_topics = false;

        if ($parameters != null) {
            $params_parts = explode(' ', $parameters);

            if (in_array('-d', $params_parts)) {
                $option_short = true;
            } else if (in_array('-s', $params_parts)) {
                $option_usageonly = true;
            }

            foreach ($params_parts as &$params_part) {
                if (!isset(self::OPTIONS[$params_part])) {
                    $topics[] = $params_part;
                }
            }
        }

        if (empty($topics)) {
            $topics = DefinedCommands::get();
            $empty_topics = true;
        }

        foreach ($topics as &$topic) {
            $infos = [];

            if ($empty_topics || (!$option_short && $option_usageonly)) {
                $infos["USAGE"] = "\\Alph\\Commands\\" . $topic . "::USAGE";
            } else if ($option_short) {
                $infos["SHORT_DESCRIPTION"] = "\\Alph\\Commands\\" . $topic . "::SHORT_DESCRIPTION";
            } else {
                $infos = [
                    "USAGE" => "\\Alph\\Commands\\" . $topic . "::USAGE",
                    "SHORT_DESCRIPTION" => "\\Alph\\Commands\\" . $topic . "::SHORT_DESCRIPTION",
                    "FULL_DESCRIPTION" => "\\Alph\\Commands\\" . $topic . "::FULL_DESCRIPTION",
                    "OPTIONS" => "\\Alph\\Commands\\" . $topic . "::OPTIONS",
                    "ARGUMENTS" => "\\Alph\\Commands\\" . $topic . "::ARGUMENTS",
                    "EXIT_STATUS" => "\\Alph\\Commands\\" . $topic . "::EXIT_STATUS",
                ];
            }
            
            foreach ($infos as $key => $info) {
                if (defined($info)) {
                    $info = constant($info);
                    if ($key == "SHORT_DESCRIPTION" && $option_short) {
                        $sender->send($topic . " - " . $info);
                    } else if ($key == "USAGE" && !$empty_topics) {
                        $sender->send($topic . ": " . $info);
                    } else if ($key == "OPTIONS") {
                        $sender->send("Options:");

                        foreach ($info as $option_key => &$option) {
                            $sender->send($option_key . "        " . $option);
                        }
                    } else if ($key == "ARGUMENTS") {
                        $sender->send("Arguments:");

                        foreach ($info as $argument_key => &$argument) {
                            $sender->send($argument_key . "   " . $argument);
                        }
                    } else if ($key == "EXIT_STATUS") {
                        $sender->send("Exit Status:");
                        $sender->send($info);
                    } else {
                        $sender->send($info);
                    }

                    $sender->send("");
                    $done = true;
                }
            }
        }

        if (!$done) {
            $last_topic = $topics[count($topics) - 1];
            $sender->send("-bash: help: no help topics match '" . $last_topic . "'. Try 'help help' or 'man -k " . $last_topic . "' or 'info " . $last_topic . "'.");
        }
    }
}
