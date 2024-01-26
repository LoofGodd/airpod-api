<?php

class DB{
    public string $db_host = "localhost";
    public string $db_name = "airpod_db";
    public string $db_user = "root";
    public string $db_pass = "";
    private $conn = null;


    public function __construct(){
        try {

            $this->conn = new \PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_pass);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            $this->conn = null;
            echo "connection failed" . $e->getMessage() ."";
            exit;
        }
    }
    public function connect(){
        return $this->conn;
    }
}
