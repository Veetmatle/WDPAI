<?php

// TODO tu trzeba zamienic config na zastosowanie zmiennych .env
require_once "config.php";

class Database {
    private $username;
    private $password;
    private $host;
    private $database;

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    public function connect()
    {
        try {
            $conn = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }

        // TODO zmienic na strone z bledem z kodem 500, zamiast die
        catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // TODO napisac też metodke disconnect (zeby czyscic polaczenie, a ogolnie to ma byc ingleton baza)
}