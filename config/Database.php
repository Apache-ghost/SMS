<?php
class Database {
    private $host = 'localhost:5333';
    private $db_name = 'erp_system';
    private $user = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->password, $this->db_name);
            
            if ($this->conn->connect_error) {
                throw new Exception('Connection Error: ' . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            return $this->conn;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>