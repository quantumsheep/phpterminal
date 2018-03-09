<?php
namespace Alph\Services;

use Alph\Services\Database;

class SessionHandler
{
    public $db;

    public function __construct()
    {
        session_name("alph_sess");

        // Instantiate new connection
        $this->db = Database::connect();

        // Set handler to overide session
        session_set_save_handler(
            array($this, "_open"),
            array($this, "_close"),
            array($this, "_read"),
            array($this, "_write"),
            array($this, "_destroy"),
            array($this, "_gc")
        );
        
        // Start the session
        session_start();
    }

    public function _open()
    {
        // Return false if the db is in false state
        return $this->db !== false;
    }

    public function _close()
    {
        // Close the database connection
        $db = null;
        return true;
    }

    public function _read(string $id)
    {
        // Prapare the query
        $stmp = $this->db->prepare('SELECT data FROM session WHERE id = :id');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);

        // If the query was successful
        if ($stmp->execute()) {
            // Save returned row
            $row = $stmp->fetch();

            // Return an empty string if $row returns nothing (false)
            if($row === false) {
                return '';
            }

            // Return the data
            return $row['data'];
        } else {
            // Return an empty string
            return '';
        }
    }

    public function _write(string $id, $data)
    {
        // Timestamp creation for session duration timeout
        $access = time();

        // Prapare the query
        $stmp = $this->db->prepare('REPLACE INTO session VALUES (:id, :access, :data)');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);
        $stmp->bindParam(':access', $access);
        $stmp->bindParam(':data', $data);

        // Returns TRUE on success or FALSE on failure
        return $stmp->execute();
    }

    public function _destroy($id)
    {
        // Prapare the query
        $stmp = $this->db->prepare('DELETE FROM session WHERE id = :id');

        // Bind query's parameters
        $stmp->bindParam(':id', $id);

        // Returns TRUE on success or FALSE on failure
        return $stmp->execute();
    }

    public function _gc($max)
    {
        // Calculate the end sessions timeout
        $old = time() - $max;

        // Prapare the query
        $stmp = $this->db->prepare('DELETE * FROM session WHERE access < :old');

        // Bind query's parameters
        $stmp->bindParam(':old', $old);

        // Returns TRUE on success or FALSE on failure
        return $stmp->execute();
    }
}