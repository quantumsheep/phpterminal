<?php
namespace Alph\Services;

class Session
{
    public static function read($db, $id)
    {
        // Prapare the query
        $stmp = $db->prepare('SELECT data FROM session WHERE id = :id');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);

        // If the query was successful
        if ($stmp->execute()) {
            // Save returned row
            $row = $stmp->fetch();

            // Return an empty string if $row returns nothing (false)
            if ($row === false) {
                return '';
            }

            // Return the data
            return self::unserialize($row['data']);
        } else {
            // Return an empty string
            return '';
        }
    }

    public static function write($db, $id, $data)
    {
        // Timestamp creation for session duration timeout
        $access = time();

        // Prapare the query
        $stmp = $db->prepare('REPLACE INTO session VALUES (:id, :access, :data)');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);
        $stmp->bindParam(':access', $access);
        $stmp->bindParam(':data', $data);

        // Returns TRUE on success or FALSE on failure
        return $stmp->execute();
    }

    public static function destroy($db, $id)
    {
        // Prapare the query
        $stmp = $db->prepare('DELETE FROM session WHERE id = :id');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);

        // Returns TRUE on success or FALSE on failure
        return $stmp->execute();
    }

    public static function unserialize($session_data)
    {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::unserialize_php($session_data);
                break;
            case "php_binary":
                return self::unserialize_phpbinary($session_data);
                break;
            default:
                throw new Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }

    private static function unserialize_php($session_data)
    {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    private static function unserialize_phpbinary($session_data)
    {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            $num = ord($session_data[$offset]);
            $offset += 1;
            $varname = substr($session_data, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}
