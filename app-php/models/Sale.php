<?php
require_once 'Database.php';

class Sale {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Получить продажи с фильтрами
    public function getFilteredSales($filters = []) {
        $sql = "SELECT k.*, 
                a.Наименование as артикул_наименование,
                g.Наименование as группа_наименование,
                og.Наименование as общая_группа_наименование,
                e.Наименование as единица_измерения,
                s.общая_скидка,
                city.Город,
                city.Федеральный_округ,
                tc.Наименование as тип_клиента
                FROM Клиенты k
                JOIN Артикулы a ON k.id_артикулы = a.id
                JOIN Группа g ON k.id_группа = g.id
                JOIN Обощённая_группа_товаров og ON g.id_обобщ_группа = og.id
                JOIN ед_измерения e ON k.id_ед_измерения = e.id
                JOIN Скидки s ON k.id_скидки = s.id
                JOIN Города city ON s.id_город = city.id
                JOIN типы_клиентов tc ON s.id_типы_клиентов = tc.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND k.Дата_продажи >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND k.Дата_продажи <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['client'])) {
            $sql .= " AND k.Номер_клиента LIKE ?";
            $params[] = '%' . $filters['client'] . '%';
        }
        
        if (!empty($filters['product'])) {
            $sql .= " AND a.Наименование LIKE ?";
            $params[] = '%' . $filters['product'] . '%';
        }
        
        if (!empty($filters['city'])) {
            $sql .= " AND city.id = ?";
            $params[] = $filters['city'];
        }
        
        $sql .= " ORDER BY k.Дата_продажи DESC, k.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sales = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $sales[] = $row;
                }
            }
            
            $stmt->close();
            return $sales;
        }
        
        return [];
    }
    // Получить все продажи
    public function getAllSales() {
        $sql = "SELECT k.*, 
                a.Наименование as артикул_наименование,
                g.Наименование as группа_наименование,
                og.Наименование as общая_группа_наименование,
                e.Наименование as единица_измерения,
                s.общая_скидка,
                city.Город,
                city.Федеральный_округ,
                tc.Наименование as тип_клиента
                FROM Клиенты k
                JOIN Артикулы a ON k.id_артикулы = a.id
                JOIN Группа g ON k.id_группа = g.id
                JOIN Обощённая_группа_товаров og ON g.id_обобщ_группа = og.id
                JOIN ед_измерения e ON k.id_ед_измерения = e.id
                JOIN Скидки s ON k.id_скидки = s.id
                JOIN Города city ON s.id_город = city.id
                JOIN типы_клиентов tc ON s.id_типы_клиентов = tc.id
                ORDER BY k.Дата_продажи DESC, k.created_at DESC";
        
        $result = $this->db->query($sql);
        $sales = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $sales[] = $row;
            }
        }
        
        return $sales;
    }
    
    // Получить продажу по ID
    public function getSaleById($id) {
        $id = $this->db->real_escape_string($id);
        $sql = "SELECT * FROM Клиенты WHERE id = '$id'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Создать новую продажу
    public function createSale($data) {
        $fields = [
            'Дата_продажи',
            'Номер_клиента', 
            'id_группа',
            'id_ед_измерения',
            'id_артикулы',
            'id_скидки',
            'цена_за_ед',
            'количество'
        ];
        
        $values = [];
        foreach ($fields as $field) {
            $values[] = "'" . $this->db->real_escape_string($data[$field]) . "'";
        }
        
        $sql = "INSERT INTO Клиенты (" . implode(', ', $fields) . ", created_at) 
                VALUES (" . implode(', ', $values) . ", NOW())";
        
        return $this->db->query($sql);
    }
    
    // Обновить продажу
    public function updateSale($id, $data) {
        $fields = [
            'Дата_продажи',
            'Номер_клиента', 
            'id_группа',
            'id_ед_измерения',
            'id_артикулы',
            'id_скидки',
            'цена_за_ед',
            'количество'
        ];
        
        $updates = [];
        foreach ($fields as $field) {
            $updates[] = $field . " = '" . $this->db->real_escape_string($data[$field]) . "'";
        }
        
        $sql = "UPDATE Клиенты SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = '$id'";
        return $this->db->query($sql);
    }
    
    // Удалить продажу
    public function deleteSale($id) {
        $id = $this->db->real_escape_string($id);
        $sql = "DELETE FROM Клиенты WHERE id = '$id'";
        return $this->db->query($sql);
    }
    
    // Получить справочные данные
    public function getReferenceData() {
        $data = [];
        
        // Единицы измерения
        $sql = "SELECT * FROM ед_измерения ORDER BY Наименование";
        $result = $this->db->query($sql);
        while($row = $result->fetch_assoc()) {
            $data['единицы_измерения'][] = $row;
        }
        
        // Скидки
        $sql = "SELECT s.*, city.Город, tc.Наименование as тип_клиента 
                FROM Скидки s
                JOIN Города city ON s.id_город = city.id
                JOIN типы_клиентов tc ON s.id_типы_клиентов = tc.id
                ORDER BY s.Дата_продажи DESC";
        $result = $this->db->query($sql);
        while($row = $result->fetch_assoc()) {
            $data['скидки'][] = $row;
        }
        
        // Города
        $sql = "SELECT * FROM Города ORDER BY Город";
        $result = $this->db->query($sql);
        while($row = $result->fetch_assoc()) {
            $data['города'][] = $row;
        }
        
        // Типы клиентов
        $sql = "SELECT * FROM типы_клиентов ORDER BY приоритет DESC";
        $result = $this->db->query($sql);
        while($row = $result->fetch_assoc()) {
            $data['типы_клиентов'][] = $row;
        }
        
        return $data;
    }
    
    // Создать скидку
    public function createDiscount($data) {
        $fields = ['Дата_продажи', 'id_город', 'id_типы_клиентов', 'скидки_по_группе_клиентов', 'скидки_по_группе_сумме_покупки'];
        $values = [];
        
        foreach ($fields as $field) {
            $values[] = "'" . $this->db->real_escape_string($data[$field]) . "'";
        }
        
        $sql = "INSERT INTO Скидки (" . implode(', ', $fields) . ", created_at) 
                VALUES (" . implode(', ', $values) . ", NOW())";
        
        return $this->db->query($sql);
    }
}


?>