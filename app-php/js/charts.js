// Общие функции для работы с графиками

// Цвета для графиков
const chartColors = {
    primary: 'rgb(102, 126, 234)',
    secondary: 'rgb(118, 75, 162)',
    success: 'rgb(16, 185, 129)',
    danger: 'rgb(239, 68, 68)',
    warning: 'rgb(245, 158, 11)',
    info: 'rgb(59, 130, 246)'
};

// Создание графика
function createChart(ctx, type, data, options = {}) {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed.y.toLocaleString('ru-RU') + ' ₽';
                        return label;
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
    };

    const mergedOptions = { ...defaultOptions, ...options };

    return new Chart(ctx, {
        type: type,
        data: data,
        options: mergedOptions
    });
}

// Загрузка данных для графика
async function loadChartData(url, chartId, chartType) {
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        const ctx = document.getElementById(chartId).getContext('2d');
        
        const chartData = {
            labels: data.labels,
            datasets: [{
                label: chartType === 'line' ? 'Выручка' : 'Продажи',
                data: data.data,
                backgroundColor: chartType === 'bar' ? 
                    'rgba(102, 126, 234, 0.5)' : 'rgba(102, 126, 234, 0.2)',
                borderColor: 'rgb(102, 126, 234)',
                borderWidth: 2,
                tension: 0.1
            }]
        };

        createChart(ctx, chartType, chartData);
        
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
}

// Экспорт данных в CSV
function exportToCSV(data, filename) {
    const csvContent = "data:text/csv;charset=utf-8," 
        + data.map(row => Object.values(row).join(",")).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    
    link.click();
    document.body.removeChild(link);
}

// Фильтрация данных
function filterData(data, filters) {
    return data.filter(item => {
        for (const key in filters) {
            if (filters[key] && item[key] !== filters[key]) {
                return false;
            }
        }
        return true;
    });
}

// Инициализация всех графиков на странице
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех элементов с классом 'chart'
    const charts = document.querySelectorAll('.chart');
    
    charts.forEach(chart => {
        const chartId = chart.id;
        const chartType = chart.dataset.type || 'line';
        const dataUrl = chart.dataset.url;
        
        if (dataUrl) {
            loadChartData(dataUrl, chartId, chartType);
        }
    });
});