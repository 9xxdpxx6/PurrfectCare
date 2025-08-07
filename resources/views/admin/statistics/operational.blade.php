@extends('layouts.admin')

@section('title', 'Операционная статистика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-calendar-check"></i> Операционная статистика
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
                    <input type="text" id="date_range" class="form-control" placeholder="С по" style="max-width: 260px;" readonly>
                </div>
                <span class="text-muted">Период: с {{ isset($startDate) ? $startDate->format('d.m.Y') : '' }} по {{ isset($endDate) ? $endDate->format('d.m.Y') : '' }}</span>
            </div>
        </form>
    </div>
</div>

<!-- Основные операционные метрики -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-check text-primary fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-primary">{{ number_format($visitsData['total']) }}</h3>
                <p class="card-text text-muted">Всего приёмов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people text-success fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-success">{{ $employeeLoad->count() }}</h3>
                <p class="card-text text-muted">Активных ветеринаров</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-building text-info fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-info">{{ $scheduleStats['total_schedules'] }}</h3>
                <p class="card-text text-muted">Расписаний</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-event text-warning fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-warning">{{ $scheduleStats['schedules_with_visits'] }}</h3>
                <p class="card-text text-muted">Расписаний с приёмами</p>
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Приёмы по статусам
                </h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
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
                                    <th class="d-none-mobile">Количество приёмов</th>
                                    <th class="d-none-mobile">Процент от общего</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalVisits = $visitsData['total'];
                                @endphp
                                @foreach($employeeLoad as $employee)
                                    @php
                                        $percentage = $totalVisits > 0 ? round(($employee['visits_count'] / $totalVisits) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $employee['employee']->name }}</strong>
                                            @if($employee['employee']->specialties->count() > 0)
                                                <br><small class="text-muted d-none-mobile">
                                                    {{ $employee['employee']->specialties->pluck('name')->join(', ') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="d-none-mobile">{{ $employee['visits_count'] }}</td>
                                        <td class="d-none-mobile">{{ $percentage }}%</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%"
                                                     aria-valuenow="{{ $percentage }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <span class="d-none-mobile">{{ $percentage }}%</span>
                                                </div>
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
                                @php
                                    $totalStatusVisits = $statusStats->sum();
                                @endphp
                                @foreach($statusStats as $status => $count)
                                    @php
                                        $percentage = $totalStatusVisits > 0 ? round(($count / $totalStatusVisits) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $status }}</td>
                                        <td class="d-none-mobile">{{ $count }}</td>
                                        <td>{{ $percentage }}%</td>
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
                @endphp
                
                <div class="mt-3">
                    <h6>Использование расписания</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $utilizationRate }}%"
                             aria-valuenow="{{ $utilizationRate }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $utilizationRate }}%
                        </div>
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
            labels: Object.keys(visitsData.by_day),
            datasets: [{
                label: 'Количество приёмов',
                data: Object.values(visitsData.by_day),
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
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Количество приёмов'
                    },
                    beginAtZero: true
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
                data: Object.values(statusStats),
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