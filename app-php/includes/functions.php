<?php
// Функция для вывода сообщений
function showMessage($type, $message) {
    $alertClass = '';
    switch($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
        default:
            $alertClass = 'alert-info';
    }
    
    return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
}

// Функция для форматирования даты
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date) || $date == '0000-00-00') return '';
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

// Функция для форматирования суммы
function formatPrice($amount, $decimals = 2) {
    if ($amount === null) return '0,00 ₽';
    $amount = floatval($amount);
    return number_format($amount, $decimals, ',', ' ') . ' ₽';
}

// Функция для форматирования процентов
function formatPercent($value, $decimals = 2) {
    if ($value === null) return '0%';
    return number_format($value, $decimals, ',', ' ') . '%';
}

// Получить текущий URL
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Экранирование вывода
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Проверка POST запроса
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Редирект
function redirect($url) {
    header("Location: $url");
    exit();
}
?>