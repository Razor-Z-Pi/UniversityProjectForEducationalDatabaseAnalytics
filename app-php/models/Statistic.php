<?php
require_once 'Database.php';

class Statistic {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Основная статистика
    public function getDashboardStats() {
        $stats = [];
        
        // Общая статистика продаж
        $sql = "SELECT 
                COUNT(*) as total_sales,
                SUM(сумма_со_скидкой) as total_revenue,
                AVG(общая_скидка_процент) as avg_discount,
                SUM(количество) as total_quantity
                FROM Клиенты";
        
        $result = $this->db->query($sql);
        $stats['general'] = $result->fetch_assoc();
        
        // Продажи по дням (последние 7 дней)
        $sql = "SELECT 
                DATE(Дата_продажи) as date,
                COUNT(*) as sales_count,
                SUM(сумма_со_скидкой) as daily_revenue,
                SUM(количество) as daily_quantity
                FROM Клиенты
                WHERE Дата_продажи >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(Дата_продажи)
                ORDER BY date DESC";
        
        $result = $this->db->query($sql);
        $stats['daily_sales'] = [];
        while($row = $result->fetch_assoc()) {
            $stats['daily_sales'][] = $row;
        }
        
        // Топ товаров
        $sql = "SELECT 
                a.Наименование,
                COUNT(k.id) as sales_count,
                SUM(k.количество) as total_quantity,
                SUM(k.сумма_со_скидкой) as total_revenue
                FROM Клиенты k
                JOIN Артикулы a ON k.id_артикулы = a.id
                GROUP BY k.id_артикулы, a.Наименование
                ORDER BY total_revenue DESC
                LIMIT 10";
        
        $result = $this->db->query($sql);
        $stats['top_products'] = [];
        while($row = $result->fetch_assoc()) {
            $stats['top_products'][] = $row;
        }
        
        // Продажи по группам
        $sql = "SELECT 
                g.Наименование as group_name,
                COUNT(k.id) as sales_count,
                SUM(k.сумма_со_скидкой) as total_revenue
                FROM Клиенты k
                JOIN Группа g ON k.id_группа = g.id
                GROUP BY k.id_группа, g.Наименование
                ORDER BY total_revenue DESC";
        
        $result = $this->db->query($sql);
        $stats['sales_by_group'] = [];
        while($row = $result->fetch_assoc()) {
            $stats['sales_by_group'][] = $row;
        }
        
        return $stats;
    }
    
    // Аналитика продаж
    public function getAnalyticsData() {
        $analytics = [];
        
        // Продажи по месяцам
        $sql = "SELECT 
                DATE_FORMAT(Дата_продажи, '%Y-%m') as month,
                COUNT(*) as sales_count,
                SUM(сумма_со_скидкой) as monthly_revenue,
                AVG(сумма_со_скидкой) as avg_sale_amount
                FROM Клиенты
                GROUP BY DATE_FORMAT(Дата_продажи, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";
        
        $result = $this->db->query($sql);
        $analytics['monthly_sales'] = [];
        while($row = $result->fetch_assoc()) {
            $analytics['monthly_sales'][] = $row;
        }
        
        // Продажи по городам
        $sql = "SELECT 
                city.Город,
                city.Федеральный_округ,
                COUNT(k.id) as sales_count,
                SUM(k.сумма_со_скидкой) as total_revenue,
                AVG(s.общая_скидка) as avg_discount
                FROM Клиенты k
                JOIN Скидки s ON k.id_скидки = s.id
                JOIN Города city ON s.id_город = city.id
                GROUP BY city.id, city.Город, city.Федеральный_округ
                ORDER BY total_revenue DESC";
        
        $result = $this->db->query($sql);
        $analytics['sales_by_city'] = [];
        while($row = $result->fetch_assoc()) {
            $analytics['sales_by_city'][] = $row;
        }
        
        // Продажи по типам клиентов
        $sql = "SELECT 
                tc.Наименование as client_type,
                COUNT(k.id) as sales_count,
                SUM(k.сумма_со_скидкой) as total_revenue,
                AVG(k.сумма_со_скидкой) as avg_check,
                AVG(s.общая_скидка) as avg_discount
                FROM Клиенты k
                JOIN Скидки s ON k.id_скидки = s.id
                JOIN типы_клиентов tc ON s.id_типы_клиентов = tc.id
                GROUP BY tc.id, tc.Наименование
                ORDER BY tc.приоритет DESC";
        
        $result = $this->db->query($sql);
        $analytics['sales_by_client_type'] = [];
        while($row = $result->fetch_assoc()) {
            $analytics['sales_by_client_type'][] = $row;
        }
        
        // Динамика продаж по неделям
        $sql = "SELECT 
                YEARWEEK(Дата_продажи) as week,
                COUNT(*) as sales_count,
                SUM(сумма_со_скидкой) as weekly_revenue
                FROM Клиенты
                GROUP BY YEARWEEK(Дата_продажи)
                ORDER BY week DESC
                LIMIT 10";
        
        $result = $this->db->query($sql);
        $analytics['weekly_sales'] = [];
        while($row = $result->fetch_assoc()) {
            $analytics['weekly_sales'][] = $row;
        }
        
        return $analytics;
    }
    
    // Получить данные для графиков
    public function getChartData($type = 'monthly') {
        $data = [];
        
        switch ($type) {
            case 'monthly':
                $sql = "SELECT 
                        DATE_FORMAT(Дата_продажи, '%Y-%m') as label,
                        SUM(сумма_со_скидкой) as value
                        FROM Клиенты
                        GROUP BY DATE_FORMAT(Дата_продажи, '%Y-%m')
                        ORDER BY label
                        LIMIT 12";
                break;
                
            case 'groups':
                $sql = "SELECT 
                        g.Наименование as label,
                        SUM(k.сумма_со_скидкой) as value
                        FROM Клиенты k
                        JOIN Группа g ON k.id_группа = g.id
                        GROUP BY k.id_группа, g.Наименование
                        ORDER BY value DESC
                        LIMIT 10";
                break;
                
            case 'cities':
                $sql = "SELECT 
                        city.Город as label,
                        SUM(k.сумма_со_скидкой) as value
                        FROM Клиенты k
                        JOIN Скидки s ON k.id_скидки = s.id
                        JOIN Города city ON s.id_город = city.id
                        GROUP BY city.id, city.Город
                        ORDER BY value DESC
                        LIMIT 10";
                break;
                
            default:
                return ['labels' => [], 'data' => []];
        }
        
        $result = $this->db->query($sql);
        $labels = [];
        $values = [];
        
        while($row = $result->fetch_assoc()) {
            $labels[] = $row['label'];
            $values[] = (float)$row['value'];
        }
        
        return [
            'labels' => $labels,
            'data' => $values
        ];
    }
}
?>