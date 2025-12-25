<?php
require_once '../includes/header.php';
require_once '../models/Product.php';

$productModel = new Product();
$products = $productModel->getAllProducts();
$stats = $productModel->getProductStatistics();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Управление товарами</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Добавить товар
        </a>
    </div>
</div>

<!-- Статистика товаров -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Всего товаров</h5>
                <h2><?php echo $stats['total_products']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Групп товаров</h5>
                <h2><?php echo $stats['total_groups']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Общих групп</h5>
                <h2><?php echo $stats['total_general_groups']; ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Таблица товаров -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Артикул</th>
                    <th>Наименование</th>
                    <th>Группа</th>
                    <th>Общая группа</th>
                    <th>Дата добавления</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6" class="text-center">Товары не найдены</td>
                </tr>
                <?php else: ?>
                <?php foreach($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['артикул']); ?></td>
                    <td><?php echo htmlspecialchars($product['наименование']); ?></td>
                    <td><?php echo htmlspecialchars($product['группа']); ?></td>
                    <td><?php echo htmlspecialchars($product['общая_группа']); ?></td>
                    <td><?php echo formatDate($product['created_at']); ?></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-primary" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-danger" 
                               onclick="return confirm('Удалить товар?')" title="Удалить">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>