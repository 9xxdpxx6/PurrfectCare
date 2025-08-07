@extends('layouts.admin')

@section('title', 'Статистика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-graph-up"></i> Статистика
    </h1>
</div>

<!-- Фильтр периода -->
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" id="period-form">
            <input type="hidden" name="period" id="hidden-period" value="{{ $period }}">
            <input type="hidden" name="start_date" id="hidden-start" value="{{ isset($startDate) ? $startDate->format('d.m.Y') : '' }}">
            <input type="hidden" name="end_date" id="hidden-end" value="{{ isset($endDate) ? $endDate->format('d.m.Y') : '' }}">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group" role="group" aria-label="Период">
                    <button type="button" class="btn btn-outline-secondary @if($period==='week') active @endif" onclick="setPeriod('week')">Неделя</button>
                    <button type="button" class="btn btn-outline-secondary @if($period==='month') active @endif" onclick="setPeriod('month')">Месяц</button>
                    <button type="button" class="btn btn-outline-secondary @if($period==='quarter') active @endif" onclick="setPeriod('quarter')">Квартал</button>
                    <button type="button" class="btn btn-outline-secondary @if($period==='year') active @endif" onclick="setPeriod('year')">Год</button>
                    <button type="button" class="btn btn-outline-secondary @if($period==='all') active @endif" onclick="setPeriod('all')">За всё время</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="date_range" class="form-control" placeholder="Интервал" style="max-width: 260px;" readonly value="{{ isset($startDate) && isset($endDate) ? $startDate->format('d.m.Y') . ' по ' . $endDate->format('d.m.Y') : '' }}">
                </div>
                <span class="text-muted">Период: с {{ isset($startDate) ? $startDate->format('d.m.Y') : '' }} по {{ isset($endDate) ? $endDate->format('d.m.Y') : '' }}</span>
            </div>
        </form>
    </div>
</div>

<!-- Навигация по разделам статистики -->
<h5 class="text-muted mb-2">Разделы</h5>
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('admin.statistics.financial') }}" class="btn btn-outline-primary h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-cash-coin fs-1"></i>
            </div>
            <h6 class="mb-1">Финансы</h6>
            <small class="text-muted">Финансовая статистика и аналитика</small>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('admin.statistics.operational') }}" class="btn btn-outline-success h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-calendar-check fs-1"></i>
            </div>
            <h6 class="mb-1">Операции</h6>
            <small class="text-muted">Операционная статистика и загруженность</small>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('admin.statistics.clients') }}" class="btn btn-outline-info h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-people fs-1"></i>
            </div>
            <h6 class="mb-1">Клиенты</h6>
            <small class="text-muted">Статистика клиентов и питомцев</small>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('admin.statistics.medical') }}" class="btn btn-outline-warning h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-heart-pulse fs-1"></i>
            </div>
            <h6 class="mb-1">Медицина</h6>
            <small class="text-muted">Медицинская статистика и аналитика</small>
        </a>
    </div>
</div>

<!-- Основные метрики -->
<h5 class="text-muted mb-2">Ключевые показатели</h5>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-check text-primary fs-1 me-2"></i>
                </div>
                <h3 class="card-title gradient-text gradient-text-primary">{{ number_format($metrics['total_visits']) }}</h3>
                <p class="card-text text-muted">Приёмов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cart-check text-success fs-1 me-2"></i>
                </div>
                <h3 class="card-title gradient-text gradient-text-success">{{ number_format($metrics['total_orders']) }}</h3>
                <p class="card-text text-muted">Заказов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack text-info fs-1 me-2"></i>
                </div>
                <h3 class="card-title gradient-text gradient-text-info">{{ number_format($metrics['total_revenue'], 0, ',', ' ') }} ₽</h3>
                <p class="card-text text-muted">Выручка</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people text-warning fs-1 me-2"></i>
                </div>
                <h3 class="card-title gradient-text gradient-text-warning">{{ number_format($metrics['total_clients']) }}</h3>
                <p class="card-text text-muted">Новых клиентов</p>
            </div>
        </div>
    </div>
</div>

<!-- Дополнительные метрики -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline success">
            <div class="card-body text-center">
                <h5 class="card-title">Средний чек</h5>
                <h3 class="gradient-text gradient-text-success">{{ number_format($metrics['average_order'], 0, ',', ' ') }} ₽</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline info">
            <div class="card-body text-center">
                <h5 class="card-title">Конверсия приёмов в заказы</h5>
                <h3 class="gradient-text gradient-text-info">{{ $metrics['conversion_rate'] }}%</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline primary">
            <div class="card-body text-center">
                <h5 class="card-title">Питомцев</h5>
                <h3 class="gradient-text gradient-text-primary">{{ number_format($metrics['total_pets']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- График выручки за последние 7 дней -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика за последние 7 дней
                </h5>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Топ услуг -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-star"></i> Топ-5 услуг
                </h5>
            </div>
            <div class="card-body">
                @if($topServices->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topServices as $service)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <a href="{{ route('admin.services.show', $service['service']->id) }}" class="text-decoration-underline text-body">
                                            {{ $service['service']->name }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ number_format($service['revenue'], 0, ',', ' ') }} ₽</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    {{ $service['count'] }} заказов
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">Нет данных</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Дополнительная статистика -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Общая информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Филиалов:</strong></p>
                        <h4 class="text-primary">{{ $metrics['total_branches'] }}</h4>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><strong>Всего сотрудников:</strong></p>
                        <h4 class="text-success">{{ $metrics['total_employees'] }}</h4>
                    </div>
                    <div class="col-6 mt-2">
                        <p class="mb-1"><strong>Услуг:</strong></p>
                        <h4 class="text-info">{{ $metrics['total_services'] }}</h4>
                    </div>
                    <div class="col-6 mt-2">
                        <p class="mb-1"><strong>Ветеринаров:</strong></p>
                        <h4 class="text-warning">{{ $metrics['total_veterinarians'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Распределение по дням недели
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="chart-container mx-auto" style="position: relative; width: 100%; max-width: 280px; height: 280px;">
                    <canvas id="weekdayChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hiddenPeriod = document.getElementById('hidden-period');
    const hiddenStart = document.getElementById('hidden-start');
    const hiddenEnd = document.getElementById('hidden-end');

    window.setPeriod = function(p) {
        hiddenPeriod.value = p;
        if (p !== 'custom') {
            document.getElementById('period-form').submit();
        }
    };

    // Один пикер для интервала "с — по"
    const rangePicker = new AirDatepicker('#date_range', {
        range: true,
        multipleDatesSeparator: ' по ',
        dateFormat: 'dd.MM.yyyy',
        autoClose: true,
        onSelect({formattedDate}) {
            if (!formattedDate || formattedDate.length < 2) return;
            hiddenPeriod.value = 'custom';
            hiddenStart.value = formattedDate[0];
            hiddenEnd.value = formattedDate[1];
            document.getElementById('period-form').submit();
        }
    });

    // Данные для графика за неделю
    const weeklyData = @json($weeklyStats);
    
    // График статистики за неделю
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: Object.keys(weeklyData),
            datasets: [{
                label: 'Приёмы',
                data: Object.values(weeklyData).map(day => day.visits),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Заказы',
                data: Object.values(weeklyData).map(day => day.orders),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }, {
                label: 'Выручка (тыс. ₽)',
                data: Object.values(weeklyData).map(day => day.revenue / 1000),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'День недели'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Количество'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Выручка (тыс. ₽)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    
    // График по дням недели - оптимизированный
    const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
    const weekdayChart = new Chart(weekdayCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(weeklyData),
            datasets: [{
                data: Object.values(weeklyData).map(day => day.visits),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6B9D'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        usePointStyle: true,
                        font: {
                            size: 10
                        },
                        // Убираем кастомную функцию, пусть Chart.js сам генерирует легенду
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '50%',
            layout: {
                padding: {
                    top: 5,
                    bottom: 5
                }
            }
        }
    });
});
</script>
@endpush 