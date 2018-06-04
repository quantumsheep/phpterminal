<?php
namespace Alph\Services;

use Alph\Services\CommandAsset;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class CommandAsset
{

    /**
     * get quoted Parameters and return full Path of those
     */
    public static function getQuotedParameters(string &$parameters, $position)
    {
        $pattern = "/(\"([^\"]+)\") /";
        $fullPathQuotedParameters = [];
        // Get quoted element with the pattern
        preg_match_all($pattern, $parameters . " ", $quotedParameters);

        // Use 2 position of array, to exclude " "
        if (!empty($quotedParameters[1])) {
            foreach ($quotedParameters[1] as $quotedParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $quotedParameter, "", $parameters);

                $fullPathQuotedParameters[] = self::GetAbsolute($position, str_replace('"', "", $quotedParameter));
            }
        }

        return $fullPathQuotedParameters;
    }

    /**
     * get command options
     */
    public static function getOptions(string &$parameters)
    {

        $pattern = "/(-[a-zA-Z\d]+) /";
        $finalOptions = [];

        // Get options with the pattern
        preg_match_all($pattern, $parameters . " ", $options);

        if (!empty($options[1])) {
            foreach ($options[1] as $option) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $option, "", $parameters);
                // remove "-" from option for easiest treatment
                $finalOptions[] = str_replace("-", "", $option);
            }
        }

        return $finalOptions;
    }

    /**
     * get path parameters and return full path of both relative and absolute one
     */
    public static function getPathParameters(string &$parameters, string $position)
    {
        $fullPathParameters = [];

        // Get absolute Path parameters
        $absolutePathParameters = self::getAbsolutePathParameters($parameters);

        // Get relative Path parameters
        $relativePathParameters = self::getRelativePathParameters($parameters, $position);

        // Check empty array case
        if (!empty($relativePathParameters) && !empty($absolutePathParameters)) {
            $fullPathParameters = array_merge($relativePathParameters, $absolutePathParameters);
        } else if (empty($relativePathParameters) && !empty($absolutePathParameters)) {
            // If no relative Parameters, $fullPath = absolute path parameters
            $fullPathParameters = $absolutePathParameters;
        } else if (empty($absolutePathParameters) && !empty($relativePathParameters)) {
            // If no absolute Parameters, $fullPath = relative path parameters
            $fullPathParameters = $relativePathParameters;
        }

        // remove "" element, in order to get easier element to work on
        $fullPathParameters = str_replace('"', "", $fullPathParameters);
        return $fullPathParameters;
    }

    /**
     * get absolute path parameters
     */
    public static function getAbsolutePathParameters(string &$parameters)
    {

        $pattern = "/ ((\/+((\"[^\"]*\")|[^\/ ]+))+)/";

        // Get path parameters with the pattern
        preg_match_all($pattern, " " . $parameters, $pathParameters);

        if (!empty($pathParameters[1])) {
            foreach ($pathParameters[1] as $pathParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $pathParameter, "", $parameters);
            }
            return $pathParameters[1];
        }

        return;

    }

    /**
     * localize relative path parameters and return absolute path
     */
    public static function getRelativePathParameters(string &$parameters, string $position)
    {

        $FinalPathParameters = [];
        $pattern = "/ (((\"[^\"]*\")|([^\/ ]))+\/((\"[^\"]*\")|([^\/ ]+\/?))*)+/";

        // Get path parameters with the pattern
        preg_match_all($pattern, " " . $parameters, $pathParameters);

        if (!empty($pathParameters[1])) {
            foreach ($pathParameters[1] as $pathParameter) {
                // Update the whole parameters for further treatments
                $parameters = str_replace(" " . $pathParameter, "", $parameters);

                $FinalPathParameters[] = self::getAbsolute($position, $pathParameter);

            }
            return $FinalPathParameters;
        }

        return;
    }

    /**
     * Give absolute path from any relative path and the actual position
     */
    public static function getAbsolute(string...$path)
    {
        $absolute = "";

        $absolute_parts = [];

        if (count($path) <= 0) {
            return "/";
        }

        if ($path[0][0] !== '/') {
            throw new \Exception("The first path given to getAbsolute function must be an absolute path.");
        }

        $i = 0;

        foreach ($path as $p) {
            $n = 0;
            $part = explode('/', $p);
            foreach ($part as $partofpart) {
                if ($partofpart == "" && $n == 0) {
                    $absolute_parts = [];
                    $i = 0;
                    $n++;
                } else if ($partofpart == ".") {
                    $i--;
                    $n++;
                } else if ($partofpart == "..") {
                    if (!isset($absolute_parts[$i - 1])) {
                        throw new \Exception("Wrong path value.");
                        return false;
                    }

                    array_splice($absolute_parts, --$i, 1);
                    $n++;
                } else {
                    $absolute_parts[] = $partofpart;
                    $i++;
                    $n++;
                }
            }
        }

        for ($j = 0; $j <= $i; $j++) {
            if (isset($absolute_parts[$j]) && $absolute_parts[$j] == "") {
                \array_splice($absolute_parts, $j, 1);
            }
        }

        return '/' . join('/', $absolute_parts);
    }

    /**
     * Generate new directories from array of Full Path given
     */
    public static function createNewDirectories(array $fullPathNewDirectories, \PDO $db, ConnectionInterface $sender, string $terminal_mac, SenderData &$data){

    }




    /**
     * Automatically generate directory if it doesn't exist
     * -d's mkdir option
     */
    public static function mkdirDOption(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $fullPathParameters)
    {
        $sender->send("message|salt");
    }

    /**
     * return array of fullPath from array of parameters
     */
    public static function fullPathFromParameters(array $parameters, string $position)
    {
        $fullPathParameters = [];
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $fullPathParameters[] = self::getAbsolute($position, $parameter);
            }
            return $fullPathParameters;
        }
        return;
    }

    /**
     * Concatenate Parameters
     */
    public static function concatenateParameters(array &$hostArray, array...$parameters)
    {
        if (!empty($parameters)) {
            for ($i = 0; $i < count($parameters); $i++) {
                for ($j = 0; $j < count($parameters[$i]); $j++) {
                    $hostArray[] = $parameters[$i][$j];
                }
            }
        }
    }
}
