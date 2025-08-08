@extends('layouts.admin')

@section('title', 'Финансовая статистика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-cash-coin"></i> Финансовая статистика
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

<!-- Основные финансовые метрики -->
<div class="row mb-4">
    @php
        $totalRevenue = array_sum($revenueData);
        $totalOrders = count($revenueData);
        $averageRevenue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
    @endphp
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack fs-1"></i>
                </div>
                <h3>{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</h3>
                <p class="card-text text-muted mb-1">Общая выручка</p>
                <small class="text-muted d-block">Сумма всех оплаченных заказов</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cart-check fs-1"></i>
                </div>
                <h3>{{ number_format($totalOrders) }}</h3>
                <p class="card-text text-muted mb-1">
                    @php
                        $lastDigit = $totalOrders % 10;
                        $lastTwoDigits = $totalOrders % 100;
                        
                        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
                            $orderText = 'Заказов';
                        } elseif ($lastDigit == 1) {
                            $orderText = 'Заказ';
                        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
                            $orderText = 'Заказа';
                        } else {
                            $orderText = 'Заказов';
                        }
                    @endphp
                    {{ $orderText }}
                </p>
                <small class="text-muted d-block">Количество созданных заказов</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-graph-up fs-1"></i>
                </div>
                <h3>{{ number_format($averageRevenue, 0, ',', ' ') }} ₽</h3>
                <p class="card-text text-muted mb-1">Средний чек</p>
                <small class="text-muted d-block">Средняя сумма заказа</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline warning h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-building fs-1"></i>
                </div>
                <h3>{{ $branchRevenue->count() }}</h3>
                <p class="card-text text-muted mb-1">
                    @php
                        $branchCount = $branchRevenue->count();
                        $lastDigit = $branchCount % 10;
                        $lastTwoDigits = $branchCount % 100;
                        
                        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
                            $branchText = 'Активных филиалов';
                        } elseif ($lastDigit == 1) {
                            $branchText = 'Активный филиал';
                        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
                            $branchText = 'Активных филиала';
                        } else {
                            $branchText = 'Активных филиалов';
                        }
                    @endphp
                    {{ $branchText }}
                </p>
                <small class="text-muted d-block">Филиалы с заказами</small>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- График выручки по дням -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Динамика выручки
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Выручка по категориям -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Выручка по категориям
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="chart-container mx-auto" style="position: relative; width: 100%; max-width: 280px; height: 280px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Топ доходных услуг и филиалов -->
<div class="row">
    <!-- Топ услуг -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-star"></i> Топ доходных услуг
                </h5>
            </div>
            <div class="card-body">
                @if($topServices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Услуга</th>
                                    <th class="d-none-mobile">Заказов</th>
                                    <th>Выручка</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topServices as $service)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.services.show', $service['service']->id) }}" class="text-decoration-underline text-body">
                                                {{ $service['service']->name }}
                                            </a>
                                        </td>
                                        <td class="d-none-mobile">{{ $service['count'] }}</td>
                                        <td class="text-success">{{ number_format($service['revenue'], 0, ',', ' ') }} ₽</td>
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
    
    <!-- Прибыльность по филиалам -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Прибыльность по филиалам
                </h5>
            </div>
            <div class="card-body">
                @if($branchRevenue->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Филиал</th>
                                    <th class="d-none-mobile">Заказов</th>
                                    <th>Выручка</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchRevenue as $branch)
                                    <tr>
                                        <td>{{ $branch['branch']->name }}</td>
                                        <td class="d-none-mobile">{{ $branch['orders_count'] }}</td>
                                        <td class="text-success">{{ number_format($branch['revenue'], 0, ',', ' ') }} ₽</td>
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
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Детальная финансовая статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">{{ number_format($categoryRevenue['services'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted mb-1">Услуги</p>
                            <small class="text-muted">Выручка от медицинских услуг (консультации, операции, процедуры)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">{{ number_format($categoryRevenue['drugs'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted mb-1">Лекарства</p>
                            <small class="text-muted">Выручка от продажи препаратов и медикаментов</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($categoryRevenue['lab_tests'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted mb-1">Анализы</p>
                            <small class="text-muted">Выручка от лабораторных исследований</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($categoryRevenue['vaccinations'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted mb-1">Вакцинации</p>
                            <small class="text-muted">Выручка от прививок и вакцинаций</small>
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
    const revenueData = @json($revenueData);
    const categoryRevenue = @json($categoryRevenue);
    
    // График выручки по дням
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: Object.keys(revenueData),
            datasets: [{
                label: 'Выручка (₽)',
                data: Object.values(revenueData),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
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
                        text: 'Выручка (₽)'
                    }
                }
            }
        }
    });
    
    // График выручки по категориям
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Услуги', 'Лекарства', 'Анализы', 'Вакцинации'],
            datasets: [{
                data: [
                    categoryRevenue.services,
                    categoryRevenue.drugs,
                    categoryRevenue.lab_tests,
                    categoryRevenue.vaccinations
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
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