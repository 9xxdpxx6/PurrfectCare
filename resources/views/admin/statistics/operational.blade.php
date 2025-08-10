@extends('layouts.admin')

@section('title', 'Статистика эффективности')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-calendar-check"></i> Статистика эффективности
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.statistics.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к дашборду
        </a>
    </div>
</div>

<!-- Фильтр периода -->
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" id="period-form">
            <input type="hidden" name="period" id="hidden-period" value="{{ $period }}">
            <input type="hidden" name="start_date" id="hidden-start">
            <input type="hidden" name="end_date" id="hidden-end">
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
            </div>
        </form>
    </div>
</div>

<!-- Основные операционные метрики -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-check fs-1"></i>
                </div>
                <h3>{{ number_format($visitsData['total']) }}</h3>
                <p class="card-text text-muted mb-1">Всего приёмов</p>
                <small class="text-muted d-block">Количество запланированных приёмов</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people fs-1"></i>
                </div>
                <h3>{{ $employeeLoad->count() }}</h3>
                <p class="card-text text-muted mb-1">Активных ветеринаров</p>
                <small class="text-muted d-block">Ветеринары с приёмами</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-building fs-1"></i>
                </div>
                <h3>{{ $scheduleStats['total_schedules'] }}</h3>
                <p class="card-text text-muted mb-1">Расписаний</p>
                <small class="text-muted d-block">Созданных смен</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline warning h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-event fs-1"></i>
                </div>
                <h3>{{ $scheduleStats['schedules_with_visits'] }}</h3>
                <p class="card-text text-muted mb-1">Расписаний с приёмами</p>
                <small class="text-muted d-block">Смен с записанными пациентами</small>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- Статистика приёмов по дням -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика приёмов по дням
                </h5>
            </div>
            <div class="card-body">
                <canvas id="visitsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Статистика по статусам -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Приёмы по статусам
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="chart-container mx-auto" style="position: relative; width: 100%; max-width: 280px; height: 280px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Загруженность ветеринаров -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people"></i> Загруженность ветеринаров
                </h5>
            </div>
            <div class="card-body">
                @if($employeeLoad->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Ветеринар</th>
                                    <th class="d-none-mobile">Приёмов</th>
                                    <th class="d-none-mobile">Средняя загруженность</th>
                                    <th>Загруженность</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalVisits = $visitsData['total'];
                                @endphp
                                @foreach($employeeLoad as $employee)
                                    <tr>
                                        <td>
                                            <strong><a href="{{ route('admin.employees.show', $employee['employee']) }}" class="text-decoration-none">{{ $employee['employee']->name }}</a></strong>
                                            @if($employee['employee']->specialties->count() > 0)
                                                <br><small class="text-muted d-none-mobile">
                                                    {{ $employee['employee']->specialties->pluck('name')->join(', ') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="d-none-mobile">{{ $employee['visits_count'] }}</td>
                                        <td class="d-none-mobile">
                                            <span class="badge bg-{{ $employee['load_color'] }}">{{ $employee['load_level'] }}</span>
                                            <small class="text-muted d-block">{{ number_format($employee['avg_visits_per_day'], 1) }} приёмов/день</small>
                                        </td>
                                        <td>
                                            <div class="progress position-relative" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $employee['load_color'] }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $employee['progress_width'] }}%"
                                                     aria-valuenow="{{ $employee['avg_visits_per_day'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="6">
                                                </div>
                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $employee['progress_width'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                    {{ $employee['progress_percentage'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Нет данных</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Детальная статистика -->
<div class="row">
    <!-- Статистика по статусам -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check"></i> Детальная статистика по статусам
                </h5>
            </div>
            <div class="card-body">
                @if($statusStats->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Статус</th>
                                    <th class="d-none-mobile">Количество</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($statusStats as $status => $data)
                                    <tr>
                                        <td>{{ $status }}</td>
                                        <td class="d-none-mobile">{{ $data['count'] }}</td>
                                        <td>{{ $data['percentage'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Нет данных</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Статистика расписания -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event"></i> Статистика расписания
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $scheduleStats['total_schedules'] }}</h4>
                            <p class="text-muted">Всего расписаний</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">{{ $scheduleStats['schedules_with_visits'] }}</h4>
                            <p class="text-muted">С приёмами</p>
                        </div>
                    </div>
                </div>
                
                @php
                    $utilizationRate = $scheduleStats['total_schedules'] > 0 
                        ? round(($scheduleStats['schedules_with_visits'] / $scheduleStats['total_schedules']) * 100, 1) 
                        : 0;

                    // Определяем цвет в зависимости от эффективности
                    if ($utilizationRate >= 70) {
                        $utilizationColor = 'success';
                        $utilizationLabel = 'Высокая';
                    } elseif ($utilizationRate >= 40) {
                        $utilizationColor = 'warning';
                        $utilizationLabel = 'Средняя';
                    } else {
                        $utilizationColor = 'danger';
                        $utilizationLabel = 'Низкая';
                    }
                @endphp
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Эффективность расписания</h6>
                        <span class="badge bg-{{ $utilizationColor }}">{{ $utilizationLabel }}</span>
                    </div>
                    <div class="progress position-relative" style="height: 25px;">
                        <div class="progress-bar bg-{{ $utilizationColor }}" 
                             role="progressbar" 
                             style="width: {{ $utilizationRate }}%"
                             aria-valuenow="{{ $utilizationRate }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                        <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.875rem; font-weight: 500; color: {{ $utilizationRate > 50 ? 'white' : 'var(--bs-body-color)' }};">
                            {{ $utilizationRate }}%
                        </span>
                    </div>
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
    // Функция для получения цвета сетки, видимого в обеих темах
    function getGridColor() {
        // Используем полупрозрачный серый цвет, который хорошо виден в обеих темах
        return 'rgba(128, 128, 128, 0.3)';
    }
    const hiddenPeriod = document.getElementById('hidden-period');
    const hiddenStart = document.getElementById('hidden-start');
    const hiddenEnd = document.getElementById('hidden-end');

    window.setPeriod = function(p) {
        hiddenPeriod.value = p;
        if (p !== 'custom') {
            document.getElementById('period-form').submit();
        }
    };

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
    // Данные для графиков
    const visitsData = @json($visitsData);
    const statusStats = @json($statusStats);
    
    // График приёмов по дням
    const visitsCtx = document.getElementById('visitsChart').getContext('2d');
    new Chart(visitsCtx, {
        type: 'bar',
        data: {
            labels: visitsData.by_day ? Object.keys(visitsData.by_day) : [],
            datasets: [{
                label: 'Количество приёмов',
                data: visitsData.by_day ? Object.values(visitsData.by_day) : [],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Дата'
                    },
                    grid: {
                        color: getGridColor()
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Количество приёмов'
                    },
                    beginAtZero: true,
                    grid: {
                        color: getGridColor()
                    }
                }
            }
        }
    });
    
    // График по статусам
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusStats),
            datasets: [{
                data: Object.values(statusStats).map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush 