<?php
require_once '../../includes/functions.php';
require_once '../../models/Sale.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$saleModel = new Sale();
$sale = $saleModel->getSaleById($_GET['id']);

if (!$sale) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        if ($saleModel->deleteSale($_GET['id'])) {
            $_SESSION['flash_message'] = showMessage('success', 'Продажа успешно удалена!');
        } else {
            $_SESSION['flash_message'] = showMessage('error', 'Ошибка при удалении продажи!');
        }
        header('Location: index.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

// Получаем детали продажи для отображения
require_once '../models/Product.php';
$productModel = new Product();
$product = $productModel->getProductById($sale['id_артикулы']);
$group = null;
if ($product && isset($product['id_группы'])) {
    $groups = $productModel->getAllGroups();
    foreach($groups as $g) {
        if ($g['id'] == $product['id_группы']) {
            $group = $g;
            break;
        }
    }
}

// Получаем информацию о скидке
$referenceData = $saleModel->getReferenceData();
$discountInfo = null;
foreach($referenceData['скидки'] as $discount) {
    if ($discount['id'] == $sale['id_скидки']) {
        $discountInfo = $discount;
        break;
    }
}

// Получаем информацию о единице измерения
$unitInfo = null;
foreach($referenceData['единицы_измерения'] as $unit) {
    if ($unit['id'] == $sale['id_ед_измерения']) {
        $unitInfo = $unit;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление продажи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        .sale-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        .detail-value {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Подтверждение удаления</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-circle"></i> Внимание!</h5>
                            <p class="mb-0">Вы собираетесь удалить продажу. Это действие невозможно отменить.</p>
                        </div>
                        
                        <h5 class="mb-3">Детали продажи:</h5>
                        
                        <div class="sale-details">
                            <div class="detail-item">
                                <div class="detail-label">ID продажи:</div>
                                <div class="detail-value"><?php echo e($sale['id']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Дата продажи:</div>
                                <div class="detail-value"><?php echo formatDate($sale['Дата_продажи']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Клиент:</div>
                                <div class="detail-value"><?php echo e($sale['Номер_клиента']); ?></div>
                            </div>
                            
                            <?php if ($product): ?>
                            <div class="detail-item">
                                <div class="detail-label">Товар:</div>
                                <div class="detail-value">
                                    <strong><?php echo e($product['наименование']); ?></strong><br>
                                    <small class="text-muted">Артикул: <?php echo e($product['артикул']); ?></small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($group): ?>
                            <div class="detail-item">
                                <div class="detail-label">Группа товара:</div>
                                <div class="detail-value">
                                    <?php echo e($group['Наименование']); ?><br>
                                    <small class="text-muted">Общая группа: <?php echo e($group['общая_группа']); ?></small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <div class="detail-label">Количество:</div>
                                <div class="detail-value">
                                    <?php echo e($sale['количество']); ?>
                                    <?php if ($unitInfo): ?>
                                    <span class="text-muted">(<?php echo e($unitInfo['сокращение'] ?? $unitInfo['Наименование']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Цена за единицу:</div>
                                <div class="detail-value"><?php echo formatPrice($sale['цена_за_ед']); ?></div>
                            </div>
                            
                            <?php if ($discountInfo): ?>
                            <div class="detail-item">
                                <div class="detail-label">Скидка:</div>
                                <div class="detail-value">
                                    <span class="badge bg-info"><?php echo formatPercent($discountInfo['общая_скидка']); ?></span><br>
                                    <small class="text-muted">
                                        Тип клиента: <?php echo e($discountInfo['тип_клиента']); ?><br>
                                        Город: <?php echo e($discountInfo['Город']); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <div class="detail-label">Сумма без скидки:</div>
                                <div class="detail-value"><?php echo formatPrice($sale['сумма_без_скидки']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Сумма со скидкой:</div>
                                <div class="detail-value">
                                    <strong><?php echo formatPrice($sale['сумма_со_скидкой']); ?></strong>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Экономия от скидки:</div>
                                <div class="detail-value text-success">
                                    <?php 
                                    $discountAmount = floatval($sale['сумма_без_скидки']) - floatval($sale['сумма_со_скидкой']);
                                    echo formatPrice($discountAmount);
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" name="confirm" value="1" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> Да, удалить продажу
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="index.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-times"></i> Отмена
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Дополнительная информация</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Что произойдет после удаления:</strong></p>
                        <ul>
                            <li>Продажа будет полностью удалена из базы данных</li>
                            <li>Статистика продаж будет пересчитана</li>
                            <li>Это действие повлияет на все отчеты и аналитику</li>
                            <li>Восстановление данных невозможно</li>
                        </ul>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fas fa-lightbulb"></i> 
                                <strong>Рекомендация:</strong> Вместо удаления можно отредактировать продажу или создать новую.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>