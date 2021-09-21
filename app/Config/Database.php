<?php

namespace App\Config;

use \PDO;

class Database {

    public static $api_key = "api_key";
    public static $api_secret = "api_secret";

    public static $host = "localhost";

    private $driver = "mysql";
    private $port = "3325";
    private $db_name = "paintopaedia";

    private $username = "root";
    private $password = "";

    private $options = [
        /*PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF-8',*/
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NAMED
    ];

    private $connection;

    public function getConnection() {

        $dsn = $this->driver . ':host=' . Database::$host . ':' . $this->port . ';dbname=' . $this->db_name;

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            return null;
        }

        return $this->connection;
    }
}
