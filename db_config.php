<?php
class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';
    private $db = 'lindavista';
    private $user = 'ihc';
    private $pass = 'ihc';
    
    private function __construct() {
        try {
            $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}