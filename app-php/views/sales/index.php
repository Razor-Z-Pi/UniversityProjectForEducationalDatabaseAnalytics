<?php
require_once '../../includes/header.php';
require_once '../../models/Sale.php';

$saleModel = new Sale();
$sales = $saleModel->getAllSales();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Управление продажами</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Новая продажа
        </a>
    </div>
</div>

<!-- Статистика продаж -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <h5 class="card-title">Всего продаж</h5>
                <h2><?php echo count($sales); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Общая выручка</h5>
                <h2>
                    <?php 
                    $totalRevenue = 0;
                    foreach($sales as $sale) {
                        $totalRevenue += floatval($sale['сумма_со_скидкой'] ?? 0);
                    }
                    echo formatPrice($totalRevenue);
                    ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Средняя скидка</h5>
                <h2>
                    <?php 
                    $avgDiscount = 0;
                    if (!empty($sales)) {
                        $totalDiscount = 0;
                        foreach($sales as $sale) {
                            $totalDiscount += floatval($sale['общая_скидка'] ?? 0);
                        }
                        $avgDiscount = $totalDiscount / count($sales);
                    }
                    echo formatPercent($avgDiscount);
                    ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Общее количество</h5>
                <h2>
                    <?php 
                    $totalQuantity = 0;
                    foreach($sales as $sale) {
                        $totalQuantity += floatval($sale['количество'] ?? 0);
                    }
                    echo number_format($totalQuantity, 0, ',', ' ');
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Дата от</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Дата до</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-3">
                        <label for="client" class="form-label">Клиент</label>
                        <input type="text" class="form-control" id="client" name="client" placeholder="Номер клиента">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Фильтровать
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Сбросить
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Таблица продаж -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата продажи</th>
                    <th>Клиент</th>
                    <th>Товар</th>
                    <th>Количество</th>
                    <th>Цена за ед.</th>
                    <th>Сумма без скидки</th>
                    <th>Скидка</th>
                    <th>Сумма со скидкой</th>
                    <th>Город</th>
                    <th>Тип клиента</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr>
                    <td colspan="12" class="text-center">Продажи не найдены</td>
                </tr>
                <?php else: ?>
                <?php foreach($sales as $sale): ?>
                <tr>
                    <td><?php echo e($sale['id']); ?></td>
                    <td><?php echo formatDate($sale['Дата_продажи']); ?></td>
                    <td><?php echo e($sale['Номер_клиента']); ?></td>
                    <td>
                        <div><strong><?php echo e($sale['артикул_наименование']); ?></strong></div>
                        <small class="text-muted">Группа: <?php echo e($sale['группа_наименование']); ?></small>
                    </td>
                    <td><?php echo e($sale['количество']); ?> <?php echo e($sale['единица_измерения'] ?? 'шт.'); ?></td>
                    <td><?php echo formatPrice($sale['цена_за_ед']); ?></td>
                    <td><?php echo formatPrice($sale['сумма_без_скидки']); ?></td>
                    <td>
                        <span class="badge bg-info"><?php echo formatPercent($sale['общая_скидка']); ?></span>
                    </td>
                    <td>
                        <strong><?php echo formatPrice($sale['сумма_со_скидкой']); ?></strong>
                    </td>
                    <td>
                        <div><?php echo e($sale['Город']); ?></div>
                        <small class="text-muted"><?php echo e($sale['Федеральный_округ']); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?php echo e($sale['тип_клиента']); ?></span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="edit.php?id=<?php echo $sale['id']; ?>" 
                               class="btn btn-outline-primary" title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $sale['id']; ?>" 
                               class="btn btn-outline-danger" 
                               onclick="return confirm('Удалить продажу?')" title="Удалить">
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
    
    <!-- Пагинация -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Предыдущая</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Следующая</a>
            </li>
        </ul>
    </nav>
</div>

<?php require_once '../../includes/footer.php'; ?>