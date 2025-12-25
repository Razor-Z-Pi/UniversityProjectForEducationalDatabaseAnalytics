<?php
require_once 'includes/header.php';
require_once 'models/Statistic.php';

$statistic = new Statistic();
$analytics = $statistic->getAnalyticsData();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Аналитика и статистика</h1>
</div>

<!-- Фильтры и выбор графиков -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('monthly')">
                        По месяцам
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('groups')">
                        По группам товаров
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('cities')">
                        По городам
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- График -->
<div class="row mb-4">
    <div class="col-12">
        <div class="chart-container">
            <h5 id="chartTitle">Продажи по месяцам</h5>
            <canvas id="analyticsChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Статистические таблицы -->
<div class="row">
    <!-- Продажи по месяцам -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5>Продажи по месяцам</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Месяц</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                            <th>Средний чек</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['monthly_sales'] as $month): ?>
                        <tr>
                            <td><?php echo $month['month']; ?></td>
                            <td><?php echo $month['sales_count']; ?></td>
                            <td><?php echo formatPrice($month['monthly_revenue']); ?></td>
                            <td><?php echo formatPrice($month['avg_sale_amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Продажи по городам -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5>Продажи по городам</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Город</th>
                            <th>Округ</th>
                            <th>Выручка</th>
                            <th>Скидка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['sales_by_city'] as $city): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($city['Город']); ?></td>
                            <td><?php echo htmlspecialchars($city['Федеральный_округ']); ?></td>
                            <td><?php echo formatPrice($city['total_revenue']); ?></td>
                            <td><?php echo number_format($city['avg_discount'], 2); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Продажи по типам клиентов -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5>Продажи по типам клиентов</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Тип клиента</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                            <th>Средний чек</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['sales_by_client_type'] as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['client_type']); ?></td>
                            <td><?php echo $client['sales_count']; ?></td>
                            <td><?php echo formatPrice($client['total_revenue']); ?></td>
                            <td><?php echo formatPrice($client['avg_check']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Динамика продаж по неделям -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5>Динамика продаж по неделям</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Неделя</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['weekly_sales'] as $week): ?>
                        <tr>
                            <td><?php echo $week['week']; ?></td>
                            <td><?php echo $week['sales_count']; ?></td>
                            <td><?php echo formatPrice($week['weekly_revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let analyticsChart = null;

function loadChart(chartType) {
    fetch('api/chart.php?type=' + chartType)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            
            // Удаляем старый график если есть
            if (analyticsChart) {
                analyticsChart.destroy();
            }
            
            // Устанавливаем заголовок
            let title = 'Продажи по месяцам';
            if (chartType === 'groups') title = 'Продажи по группам товаров';
            if (chartType === 'cities') title = 'Продажи по городам';
            document.getElementById('chartTitle').textContent = title;
            
            // Определяем тип графика
            const chartTypeConfig = chartType === 'monthly' ? 'line' : 'bar';
            
            // Создаем новый график
            analyticsChart = new Chart(ctx, {
                type: chartTypeConfig,
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: chartType === 'monthly' ? 'Выручка (₽)' : 'Выручка по группам (₽)',
                        data: data.data,
                        backgroundColor: chartType !== 'monthly' ? 
                            'rgba(54, 162, 235, 0.5)' : 'rgba(75, 192, 192, 0.2)',
                        borderColor: chartType !== 'monthly' ? 
                            'rgba(54, 162, 235, 1)' : 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
        })
        .catch(error => console.error('Error loading chart:', error));
}

// Загружаем график по умолчанию
document.addEventListener('DOMContentLoaded', function() {
    loadChart('monthly');
});
</script>

<?php require_once 'includes/footer.php'; ?>