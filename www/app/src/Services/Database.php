<?php
namespace Alph\Services;

class Database {
    /**
     * Connect to the database
     */
    public static function connect() {
        try {
            // Return a new PDO instance with the database's parameters
            return new \PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        } catch(\PDOException $e) {
            die("Database connexion failed.");
        }
    }
}