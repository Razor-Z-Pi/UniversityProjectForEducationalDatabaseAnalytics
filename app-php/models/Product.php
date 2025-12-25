<?php
require_once 'Database.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Получить все артикулы
    public function getAllProducts() {
        $sql = "SELECT a.*, g.Наименование as группа, og.Наименование as общая_группа 
                FROM Артикулы a
                JOIN Группа g ON a.id_группы = g.id
                JOIN Обощённая_группа_товаров og ON g.id_обобщ_группа = og.id
                ORDER BY a.наименование";
        
        $result = $this->db->query($sql);
        $products = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        return $products;
    }
    
    // Получить артикул по ID
    public function getProductById($id) {
        $id = $this->db->real_escape_string($id);
        $sql = "SELECT * FROM Артикулы WHERE id = '$id'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Создать новый артикул
    public function createProduct($data) {
        $артикул = $this->db->real_escape_string($data['артикул']);
        $наименование = $this->db->real_escape_string($data['наименование']);
        $id_группы = $this->db->real_escape_string($data['id_группы']);
        
        $sql = "INSERT INTO Артикулы (артикул, наименование, id_группы, created_at) 
                VALUES ('$артикул', '$наименование', '$id_группы', NOW())";
        
        return $this->db->query($sql);
    }
    
    // Обновить артикул
    public function updateProduct($id, $data) {
        $артикул = $this->db->real_escape_string($data['артикул']);
        $наименование = $this->db->real_escape_string($data['наименование']);
        $id_группы = $this->db->real_escape_string($data['id_группы']);
        
        $sql = "UPDATE Артикулы SET 
                артикул = '$артикул',
                наименование = '$наименование',
                id_группы = '$id_группы',
                updated_at = NOW()
                WHERE id = '$id'";
        
        return $this->db->query($sql);
    }
    
    // Удалить артикул
    public function deleteProduct($id) {
        $id = $this->db->real_escape_string($id);
        $sql = "DELETE FROM Артикулы WHERE id = '$id'";
        return $this->db->query($sql);
    }
    
    // Получить все группы
    public function getAllGroups() {
        $sql = "SELECT g.*, og.Наименование as общая_группа 
                FROM Группа g
                JOIN Обощённая_группа_товаров og ON g.id_обобщ_группа = og.id
                ORDER BY g.Наименование";
        
        $result = $this->db->query($sql);
        $groups = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $groups[] = $row;
            }
        }
        
        return $groups;
    }
    
    // Получить общие группы товаров
    public function getGeneralGroups() {
        $sql = "SELECT * FROM Обощённая_группа_товаров ORDER BY Наименование";
        $result = $this->db->query($sql);
        $groups = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $groups[] = $row;
            }
        }
        
        return $groups;
    }
    
    // Получить статистику по товарам
    public function getProductStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_products,
                COUNT(DISTINCT g.id) as total_groups,
                COUNT(DISTINCT og.id) as total_general_groups
                FROM Артикулы a
                JOIN Группа g ON a.id_группы = g.id
                JOIN Обощённая_группа_товаров og ON g.id_обобщ_группа = og.id";
        
        $result = $this->db->query($sql);
        return $result->fetch_assoc();
    }
}
?>