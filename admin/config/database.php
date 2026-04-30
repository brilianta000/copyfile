<?php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $db = 'db_perpustakaan';

    public $conn;

    public function __construct() {
        $this->conn = null;

        try {
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->db
            );

            if ($this->conn->connect_error) {
                die('gagal koneksi'.$this->conn->connect_error);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getconnection() {
        return $this->conn;
    }
}

?>