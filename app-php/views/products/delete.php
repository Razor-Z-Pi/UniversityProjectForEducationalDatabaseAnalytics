<?php
require_once '../includes/functions.php';
require_once '../models/Product.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$productModel = new Product();

// Проверяем, есть ли связанные продажи
$product = $productModel->getProductById($_GET['id']);
if (!$product) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($productModel->deleteProduct($_GET['id'])) {
        $_SESSION['flash_message'] = showMessage('success', 'Товар успешно удален!');
    } else {
        $_SESSION['flash_message'] = showMessage('error', 'Ошибка при удалении товара!');
    }
    header('Location: index.php');
    exit();
}

// Выводим HTML для подтверждения удаления
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление товара</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Подтверждение удаления</h4>
                    </div>
                    <div class="card-body">
                        <p>Вы уверены, что хотите удалить товар <strong><?php echo htmlspecialchars($product['наименование']); ?></strong>?</p>
                        <p>Артикул: <?php echo htmlspecialchars($product['артикул']); ?></p>
                        
                        <form method="POST">
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-danger">Удалить</button>
                                <a href="index.php" class="btn btn-secondary">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>