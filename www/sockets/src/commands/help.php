<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\DefinedCommands;
use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

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
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $topics = [];
        $done = false;
        $option_short = false;
        $option_usageonly = false;
        $empty_topics = false;

        // Check if there is parameters
        if ($parameters != null) {
            // Split the parameters by each space characters
            $params_parts = explode(' ', $parameters);

            // Check if there is the '-d' or '-s' parameter in the command parameters
            if (in_array('-d', $params_parts)) {
                // Defining option_short to true
                $option_short = true;
            } else if (in_array('-s', $params_parts)) {
                // Defining option_usageonly to true
                $option_usageonly = true;
            }

            // Get all the topics from params parts that are not in help's OPTIONS
            foreach ($params_parts as &$params_part) {
                if (!isset(self::OPTIONS[$params_part])) {
                    // Storing the topic in a new topics index
                    $topics[] = $params_part;
                }
            }
        }

        // Check if there is no topics and fill the object with all the commands if not
        if (empty($topics)) {
            // Get all the defined commands
            $topics = DefinedCommands::get();

            // Command were empty
            $empty_topics = true;
        }

        // Looping all the topics
        foreach ($topics as &$topic) {
            // Reseting the info array
            $infos = [];

            // Defining only useful parts of commands depending on the parameters and topics given
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

            // Looping all the infos
            foreach ($infos as $key => $info) {
                // Check if the info is present in the requested command
                if (defined($info)) {
                    // Get the info's constant
                    $info = constant($info);

                    // Doing different formating for each info type and sending them to the command sender
                    if ($key == "SHORT_DESCRIPTION" && $option_short) {
                        $sender->send("message|<br><span>" . $topic . " - " . $info . "</span>");
                    } else if ($key == "USAGE" && !$empty_topics) {
                        $sender->send("message|<br><span>" . $topic . ": " . $info . "</span>");
                    } else if ($key == "OPTIONS") {
                        $sender->send("message|<br><span>Options:</span>");

                        // Looping around all the options
                        foreach ($info as $option_key => &$option) {
                            $sender->send("message|<br><span>" . $option_key . "        " . $option . "</span>");
                        }
                    } else if ($key == "ARGUMENTS") {
                        $sender->send("message|<br><span>Arguments:</span>");

                        // Looping around all the arguments
                        foreach ($info as $argument_key => &$argument) {
                            $sender->send("message|<br><span>" . $argument_key . "   " . $argument . "</span>");
                        }
                    } else if ($key == "EXIT_STATUS") {
                        $sender->send("message|<br><span>Exit Status:</span>");
                        $sender->send("message|<br><span>" . $info . "</span>");
                    } else {
                        $sender->send("message|<br><span>" . $info . "</span>");
                    }

                    // Send a jump space
                    $sender->send("message|");

                    // Defining that there is one topic done
                    $done = true;
                }
            }
        }

        // Check if there is no topic done
        if (!$done) {
            // Recover the last topic from the list
            $last_topic = $topics[count($topics) - 1];

            // Send an error message for topic not found
            $sender->send("message|-bash: help: no help topics match '" . $last_topic . "'. Try 'help help' or 'man -k " . $last_topic . "' or 'info " . $last_topic . "'.");
        }
    }
}
