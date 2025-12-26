<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/functions.php';
require_once '../models/Sale.php';

// Запускаем сессию если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit();
}

try {
    $required = ['id_город', 'id_типы_клиентов'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Поле '$field' обязательно для заполнения");
        }
    }
    
    $data = [
        'Дата_продажи' => $_POST['Дата_продажи'] ?? date('Y-m-d'),
        'id_город' => $_POST['id_город'],
        'id_типы_клиентов' => $_POST['id_типы_клиентов'],
        'скидки_по_группе_клиентов' => floatval($_POST['скидки_по_группе_клиентов'] ?? 0),
        'скидки_по_группе_сумме_покупки' => floatval($_POST['скидки_по_группе_сумме_покупки'] ?? 0)
    ];
    
    // Проверка процентов скидки
    if ($data['скидки_по_группе_клиентов'] < 0 || $data['скидки_по_группе_клиентов'] > 100) {
        throw new Exception("Скидка по группе клиентов должна быть от 0 до 100%");
    }
    
    if ($data['скидки_по_группе_сумме_покупки'] < 0 || $data['скидки_по_группе_сумме_покупки'] > 100) {
        throw new Exception("Скидка по сумме покупки должна быть от 0 до 100%");
    }
    
    $saleModel = new Sale();
    $result = $saleModel->createDiscount($data);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Скидка успешно создана']);
    } else {
        throw new Exception("Ошибка при создании скидки");
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>