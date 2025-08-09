@extends('layouts.admin')

@section('title', 'Медицинская статистика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-heart-pulse"></i> Медицинская статистика
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

<!-- Основные медицинские метрики -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-clipboard2-pulse fs-1"></i>
                </div>
                <h3>{{ number_format($diagnosesCount) }}</h3>
                <p class="card-text text-muted mb-1">Уникальных диагнозов</p>
                <small class="text-muted d-block">Количество различных диагнозов</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-shield-check fs-1"></i>
                </div>
                <h3>{{ number_format($vaccinationsData->count()) }}</h3>
                <p class="card-text text-muted mb-1">Вакцинаций</p>
                <small class="text-muted d-block">Проведённых прививок</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-droplet fs-1"></i>
                </div>
                <h3>{{ number_format($labTestsData->count()) }}</h3>
                <p class="card-text text-muted mb-1">Видов анализов</p>
                <small class="text-muted d-block">Типов лабораторных исследований</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline warning h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                </div>
                <h3>{{ number_format($totalDiagnosesCount) }}</h3>
                <p class="card-text text-muted mb-1">Всего диагнозов</p>
                <small class="text-muted d-block">Общее количество поставленных диагнозов</small>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- Топ диагнозов -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Топ диагнозов
                </h5>
            </div>
            <div class="card-body">
                <canvas id="diagnosesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Вакцинации по видам животных -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Вакцинации по видам животных
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-7 col-md-12 mb-3 mb-lg-0">
                        <div class="chart-container mx-auto" style="position: relative; width: 100%; max-width: 280px; height: 280px;">
                            <canvas id="vaccinationsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-12">
                        <div class="vaccinations-legend">
                            <!-- Легенда будет добавлена через JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Детальная статистика -->
<div class="row">
    <!-- Топ диагнозов -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check"></i> Топ-10 диагнозов
                </h5>
            </div>
            <div class="card-body">
                @if($diagnosesData->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Диагноз</th>
                                    <th class="d-none-mobile">Количество</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($diagnosesData->take(10) as $diagnosisName => $data)
                                    <tr>
                                        <td>
                                            @if($loop->index == 0)
                                                <i class="bi bi-trophy text-warning"></i>
                                            @elseif($loop->index == 1)
                                                <i class="bi bi-trophy text-secondary"></i>
                                            @elseif($loop->index == 2)
                                                <i class="bi bi-trophy text-danger"></i>
                                            @else
                                                <span class="text-muted">{{ $loop->index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $diagnosisName }}</strong>
                                        </td>
                                        <td class="d-none-mobile">
                                            <span class="badge bg-primary">{{ $data['count'] }}</span>
                                        </td>
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
    
    <!-- Статистика вакцинаций -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> Статистика вакцинаций
                </h5>
            </div>
            <div class="card-body">
                @if($vaccinationsData->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Вид животного</th>
                                    <th>Количество вакцинаций</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalVaccinations = $vaccinationsData->sum();
                                @endphp
                                @foreach($vaccinationsData as $species => $count)
                                    @php
                                        $percentage = $totalVaccinations > 0 ? round(($count / $totalVaccinations) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $species ?? 'Неизвестный вид' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $count }}</span>
                                        </td>
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
</div>

<!-- Статистика анализов -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-droplet"></i> Статистика анализов
                </h5>
            </div>
            <div class="card-body">
                @if($labTestsData->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Тип анализа</th>
                                    <th class="d-none-mobile">Количество</th>
                                    <th class="d-none-tablet">Процент</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalLabTests = $labTestsData->sum();
                                @endphp
                                @foreach($labTestsData->take(10) as $labTestName => $count)
                                    @php
                                        $percentage = $totalLabTests > 0 ? round(($count / $totalLabTests) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($loop->index == 0)
                                                <i class="bi bi-trophy text-warning"></i>
                                            @elseif($loop->index == 1)
                                                <i class="bi bi-trophy text-secondary"></i>
                                            @elseif($loop->index == 2)
                                                <i class="bi bi-trophy text-danger"></i>
                                            @else
                                                <span class="text-muted">{{ $loop->index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $labTestName }}</strong>
                                        </td>
                                        <td class="d-none-mobile">
                                            <span class="badge bg-info">{{ $count }}</span>
                                        </td>
                                        <td class="d-none-tablet">{{ $percentage }}%</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info" 
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

<!-- Сводная медицинская статистика -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data"></i> Сводная медицинская статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $diagnosesCount }}</h4>
                            <p class="text-muted">Уникальных диагнозов</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">{{ $vaccinationsData->count() }}</h4>
                            <p class="text-muted">Видов животных для вакцинации</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ $labTestsData->count() }}</h4>
                            <p class="text-muted">Типов анализов</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">{{ $totalDiagnosesCount + $vaccinationsData->sum() + $labTestsData->sum() }}</h4>
                            <p class="text-muted">Всего медицинских процедур</p>
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
    
    // Устанавливаем значение в датапикер если период custom
    if (hiddenPeriod.value === 'custom' && hiddenStart.value && hiddenEnd.value) {
        rangePicker.selectDate([hiddenStart.value, hiddenEnd.value]);
    }
    // Данные для графиков
    const diagnosesData = @json($diagnosesData);
    const vaccinationsData = @json($vaccinationsData);
    const labTestsData = @json($labTestsData);
    
    // График топ диагнозов
    const diagnosesCtx = document.getElementById('diagnosesChart').getContext('2d');
    new Chart(diagnosesCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(diagnosesData).slice(0, 10),
            datasets: [{
                label: 'Количество диагнозов',
                data: Object.values(diagnosesData).slice(0, 10).map(item => item.count),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgb(255, 99, 132)',
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
                        text: 'Диагноз'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Количество'
                    },
                    beginAtZero: true
                }
            }
        }
    });
    
    // График вакцинаций по видам животных
    const vaccinationsCtx = document.getElementById('vaccinationsChart').getContext('2d');
    const vaccinationsChart = new Chart(vaccinationsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(vaccinationsData),
            datasets: [{
                data: Object.values(vaccinationsData),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF9F40',
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Отключаем встроенную легенду
                }
            }
        }
    });
    
    // Создаем кастомную легенду
    const legendContainer = document.querySelector('.vaccinations-legend');
    const legendItems = Object.keys(vaccinationsData).map((label, index) => {
        const color = vaccinationsChart.data.datasets[0].backgroundColor[index % vaccinationsChart.data.datasets[0].backgroundColor.length];
        const value = vaccinationsData[label];
        const percentage = Object.values(vaccinationsData).reduce((a, b) => a + b, 0) > 0 
            ? Math.round((value / Object.values(vaccinationsData).reduce((a, b) => a + b, 0)) * 100) 
            : 0;
        
        return `
            <div class="d-flex align-items-center mb-1">
                <div class="legend-color me-2" style="width: 10px; height: 10px; background-color: ${color}; border-radius: 2px;"></div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size: 0.8rem;">${label}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">${value} (${percentage}%)</div>
                </div>
            </div>
        `;
    }).join('');
    
    legendContainer.innerHTML = legendItems;
});
</script>
@endpush 