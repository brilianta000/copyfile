<?php

class Database {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '1deA050806';
    private $db = 'db_perpustakaan';

    public $conn;

    public function __construct() {
        $this->conn = null;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
        echo 'sukses';
    }

    public function getConnection() {
        return $this->conn;
    }
}

?>