<?php
require_once 'includes/functions.php';
require_once 'models/Statistic.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$statistic = new Statistic();
$analytics = $statistic->getAnalyticsData();
?>

<?php require_once 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Аналитика и статистика</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportData()">
            <i class="fas fa-download"></i> Экспорт
        </button>
    </div>
</div>

<!-- Фильтры и выбор графиков -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" onclick="loadChart('monthly')">
                        <i class="fas fa-calendar-alt"></i> По месяцам
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('groups')">
                        <i class="fas fa-boxes"></i> По группам
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="loadChart('cities')">
                        <i class="fas fa-city"></i> По городам
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 id="chartTitle">Продажи по месяцам</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeChartType('line')">
                        <i class="fas fa-chart-line"></i> Линия
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changeChartType('bar')">
                        <i class="fas fa-chart-bar"></i> Столбцы
                    </button>
                </div>
            </div>
            <canvas id="analyticsChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Статистические таблицы -->
<div class="row">
    <!-- Продажи по месяцам -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5><i class="fas fa-calendar me-2"></i>Продажи по месяцам</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Месяц</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                            <th>Средний чек</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics['monthly_sales'])): ?>
                        <?php foreach($analytics['monthly_sales'] as $month): ?>
                        <tr>
                            <td><strong><?php echo e($month['month']); ?></strong></td>
                            <td><?php echo e($month['sales_count']); ?></td>
                            <td><?php echo formatPrice($month['monthly_revenue']); ?></td>
                            <td><?php echo formatPrice($month['avg_sale_amount']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Нет данных</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Продажи по городам -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5><i class="fas fa-map-marker-alt me-2"></i>Продажи по городам</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Город</th>
                            <th>Округ</th>
                            <th>Выручка</th>
                            <th>Скидка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics['sales_by_city'])): ?>
                        <?php foreach($analytics['sales_by_city'] as $city): ?>
                        <tr>
                            <td><?php echo e($city['Город']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e($city['Федеральный_округ']); ?></span></td>
                            <td><?php echo formatPrice($city['total_revenue']); ?></td>
                            <td><span class="badge bg-info"><?php echo formatPercent($city['avg_discount']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Нет данных</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Продажи по типам клиентов -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5><i class="fas fa-users me-2"></i>Продажи по типам клиентов</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Тип клиента</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                            <th>Средний чек</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics['sales_by_client_type'])): ?>
                        <?php foreach($analytics['sales_by_client_type'] as $client): ?>
                        <tr>
                            <td><?php echo e($client['client_type']); ?></td>
                            <td><?php echo e($client['sales_count']); ?></td>
                            <td><?php echo formatPrice($client['total_revenue']); ?></td>
                            <td><?php echo formatPrice($client['avg_check']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Нет данных</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Динамика продаж по неделям -->
    <div class="col-lg-6 mb-4">
        <div class="table-container">
            <h5><i class="fas fa-chart-line me-2"></i>Динамика продаж по неделям</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Неделя</th>
                            <th>Кол-во продаж</th>
                            <th>Выручка</th>
                            <th>Тенденция</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics['weekly_sales'])): ?>
                        <?php 
                        $previousRevenue = null;
                        foreach($analytics['weekly_sales'] as $index => $week): 
                            $trend = '';
                            if ($previousRevenue !== null) {
                                if ($week['weekly_revenue'] > $previousRevenue) {
                                    $trend = '<span class="badge bg-success"><i class="fas fa-arrow-up"></i></span>';
                                } elseif ($week['weekly_revenue'] < $previousRevenue) {
                                    $trend = '<span class="badge bg-danger"><i class="fas fa-arrow-down"></i></span>';
                                } else {
                                    $trend = '<span class="badge bg-secondary"><i class="fas fa-minus"></i></span>';
                                }
                            }
                            $previousRevenue = $week['weekly_revenue'];
                        ?>
                        <tr>
                            <td><?php echo e($week['week']); ?></td>
                            <td><?php echo e($week['sales_count']); ?></td>
                            <td><?php echo formatPrice($week['weekly_revenue']); ?></td>
                            <td><?php echo $trend; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Нет данных</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let analyticsChart = null;
let currentChartType = 'line';
let currentDataType = 'monthly';

function loadChart(chartType) {
    currentDataType = chartType;
    
    // Обновляем активную кнопку
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
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
            const displayChartType = currentChartType;
            
            // Подготавливаем данные
            const chartData = {
                labels: data.labels,
                datasets: [{
                    label: title,
                    data: data.data,
                    backgroundColor: displayChartType === 'bar' ? 
                        'rgba(102, 126, 234, 0.7)' : 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: displayChartType === 'line'
                }]
            };
            
            // Создаем новый график
            analyticsChart = new Chart(ctx, {
                type: displayChartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + 
                                           context.parsed.y.toLocaleString('ru-RU') + ' ₽';
                                }
                            }
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
        })
        .catch(error => {
            console.error('Error loading chart:', error);
            alert('Ошибка загрузки данных для графика');
        });
}

function changeChartType(type) {
    currentChartType = type;
    loadChart(currentDataType);
}

function exportData() {
    // Простой экспорт данных таблицы
    const tables = document.querySelectorAll('table');
    let csvData = [];
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = Array.from(cols).map(col => col.textContent.trim());
            csvData.push(rowData.join(';'));
        });
        csvData.push('\n');
    });
    
    const blob = new Blob([csvData.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'analytics_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Загружаем график по умолчанию
document.addEventListener('DOMContentLoaded', function() {
    loadChart('monthly');
});
</script>

<?php require_once 'includes/footer.php'; ?>