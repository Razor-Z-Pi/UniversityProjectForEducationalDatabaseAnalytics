<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(
                DatabaseConfig::HOST,
                DatabaseConfig::USER,
                DatabaseConfig::PASSWORD,
                DatabaseConfig::DATABASE
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset(DatabaseConfig::CHARSET);
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>