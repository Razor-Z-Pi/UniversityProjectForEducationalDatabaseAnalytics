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
    if (empty($date)) return '';
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

// Функция для форматирования суммы
function formatPrice($amount) {
    return number_format($amount, 2, ',', ' ') . ' ₽';
}

// Проверка авторизации (если потребуется)
function checkAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }
}

// Получить текущий URL
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
?>