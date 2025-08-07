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
                    <input type="text" id="date_range" class="form-control" placeholder="С по" style="max-width: 260px;" readonly>
                </div>
                <span class="text-muted">Период: с {{ isset($startDate) ? $startDate->format('d.m.Y') : '' }} по {{ isset($endDate) ? $endDate->format('d.m.Y') : '' }}</span>
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
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack text-success fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-success">{{ number_format($totalRevenue, 0, ',', ' ') }} ₽</h3>
                <p class="card-text text-muted">Общая выручка</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cart-check text-info fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-info">{{ number_format($totalOrders) }}</h3>
                <p class="card-text text-muted">Заказов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-graph-up text-primary fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-primary">{{ number_format($averageRevenue, 0, ',', ' ') }} ₽</h3>
                <p class="card-text text-muted">Средний чек</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-building text-warning fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-warning">{{ $branchRevenue->count() }}</h3>
                <p class="card-text text-muted">Активных филиалов</p>
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Выручка по категориям
                </h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" width="400" height="200"></canvas>
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
                                        <td>{{ $service['service']->name }}</td>
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
                            <p class="text-muted">Услуги</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">{{ number_format($categoryRevenue['drugs'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted">Лекарства</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($categoryRevenue['lab_tests'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted">Анализы</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($categoryRevenue['vaccinations'], 0, ',', ' ') }} ₽</h4>
                            <p class="text-muted">Вакцинации</p>
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