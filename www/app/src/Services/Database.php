<?php
namespace Alph\Services;

class Database {
    public static function connect() {
        try {
            return new \PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        } catch(\PDOException $e) {
            die("Database connexion failed.");
        }
    }
}