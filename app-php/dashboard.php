<?php
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'models/Statistic.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$statistic = new Statistic();
$stats = $statistic->getDashboardStats();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Панель:)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Сегодня</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Неделя</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Месяц</button>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Всего продаж</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['general']['total_sales']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Общая выручка</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatPrice($stats['general']['total_revenue']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Средняя скидка</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['general']['avg_discount'], 2); ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percent fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Общее количество</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['general']['total_quantity']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Графики и таблицы -->
<div class="row">
    <!-- График продаж по дням -->
    <div class="col-lg-8 mb-4">
        <div class="chart-container">
            <h5>Продажи за последние 7 дней</h5>
            <canvas id="dailySalesChart" height="150"></canvas>
        </div>
    </div>
    
    <!-- Топ товаров -->
    <div class="col-lg-4 mb-4">
        <div class="chart-container">
            <h5>Топ товаров по выручке</h5>
            <div class="list-group">
                <?php foreach($stats['top_products'] as $product): ?>
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1"><?php echo htmlspecialchars($product['Наименование']); ?></h6>
                        <small><?php echo formatPrice($product['total_revenue']); ?></small>
                    </div>
                    <small>Продаж: <?php echo $product['sales_count']; ?>, 
                           Количество: <?php echo $product['total_quantity']; ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Последние продажи -->
<div class="row">
    <div class="col-12">
        <div class="table-container">
            <h5>Последние продажи</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Клиент</th>
                            <th>Товар</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                            <th>Скидка</th>
                            <th>Город</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once 'models/Sale.php';
                        $saleModel = new Sale();
                        $recentSales = $saleModel->getAllSales();
                        $counter = 0;
                        foreach($recentSales as $sale):
                            if ($counter++ >= 10) break;
                        ?>
                        <tr>
                            <td><?php echo formatDate($sale['Дата_продажи']); ?></td>
                            <td><?php echo htmlspecialchars($sale['Номер_клиента']); ?></td>
                            <td><?php echo htmlspecialchars($sale['артикул_наименование']); ?></td>
                            <td><?php echo $sale['количество']; ?></td>
                            <td><?php echo formatPrice($sale['сумма_со_скидкой']); ?></td>
                            <td><span class="badge bg-info"><?php echo $sale['общая_скидка']; ?>%</span></td>
                            <td><?php echo htmlspecialchars($sale['Город']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// График продаж по дням
const dailyLabels = <?php echo json_encode(array_column($stats['daily_sales'], 'date')); ?>;
const dailyRevenue = <?php echo json_encode(array_column($stats['daily_sales'], 'daily_revenue')); ?>;

const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
const dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Выручка (₽)',
            data: dailyRevenue,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('ru-RU') + ' ₽';
                    }
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>