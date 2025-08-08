@extends('layouts.admin')

@section('title', 'Клиентская статистика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-people"></i> Клиентская статистика
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

<!-- Основные клиентские метрики -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-person-plus fs-1"></i>
                </div>
                <h3>{{ number_format($clientsData['new_clients']) }}</h3>
                <p class="card-text text-muted mb-1">Новых клиентов</p>
                <small class="text-muted d-block">Клиенты, зарегистрированные впервые</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people fs-1"></i>
                </div>
                <h3>{{ number_format($clientsData['repeat_clients']) }}</h3>
                <p class="card-text text-muted mb-1">Повторных клиентов</p>
                <small class="text-muted d-block">Клиенты с повторными обращениями</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-heart fs-1"></i>
                </div>
                <h3>{{ number_format($petsData['total_pets']) }}</h3>
                <p class="card-text text-muted mb-1">Питомцев</p>
                <small class="text-muted d-block">Общее количество питомцев</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-outline warning h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-star fs-1"></i>
                </div>
                <h3>{{ $topClients->count() }}</h3>
                <p class="card-text text-muted mb-1">Топ клиентов</p>
                <small class="text-muted d-block">Клиенты с наибольшими заказами</small>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- Статистика клиентов -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Распределение клиентов
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="chart-container mx-auto" style="position: relative; width: 100%; max-width: 280px; height: 280px;">
                    <canvas id="clientsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Статистика питомцев по породам -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Питомцы по породам
                </h5>
            </div>
            <div class="card-body">
                <canvas id="petsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Топ клиентов -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-trophy"></i> Топ клиентов по объёму заказов
                </h5>
            </div>
            <div class="card-body">
                @if($topClients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Клиент</th>
                                    <th class="d-none-mobile">Email</th>
                                    <th class="d-none-mobile">Телефон</th>
                                    <th class="d-none-tablet">Количество заказов</th>
                                    <th>Общая сумма</th>
                                    <th class="d-none-mobile">Средний чек</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClients as $index => $client)
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
                                            <strong>{{ $client['user']->name }}</strong>
                                            @if($client['user']->address)
                                                <br><small class="text-muted d-none-mobile">{{ $client['user']->address }}</small>
                                            @endif
                                        </td>
                                        <td class="d-none-mobile">{{ $client['user']->email }}</td>
                                        <td class="d-none-mobile">{{ $client['user']->phone ?? 'Не указан' }}</td>
                                        <td class="d-none-tablet">
                                            <span class="badge bg-primary">{{ $client['orders_count'] }}</span>
                                        </td>
                                        <td class="text-success">
                                            <strong>{{ number_format($client['total_spent'], 0, ',', ' ') }} ₽</strong>
                                        </td>
                                        <td class="text-info d-none-mobile">
                                            {{ number_format($client['average_order'], 0, ',', ' ') }} ₽
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
    <!-- Статистика клиентов -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Детальная статистика клиентов
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $clientsData['new_clients'] }}</h4>
                            <p class="text-muted">Новых клиентов</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">{{ $clientsData['repeat_clients'] }}</h4>
                            <p class="text-muted">Повторных клиентов</p>
                        </div>
                    </div>
                </div>
                
                @php
                    $totalClients = $clientsData['new_clients'] + $clientsData['repeat_clients'];
                @endphp
                
                <div class="mt-3">
                    <h6>Распределение клиентов</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-primary" 
                             role="progressbar" 
                             style="width: {{ $clientsData['new_clients_percentage'] }}%"
                             aria-valuenow="{{ $clientsData['new_clients_percentage'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            Новые: {{ $clientsData['new_clients_percentage'] }}%
                        </div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $clientsData['repeat_clients_percentage'] }}%"
                             aria-valuenow="{{ $clientsData['repeat_clients_percentage'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            Повторные: {{ $clientsData['repeat_clients_percentage'] }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Статистика питомцев -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-heart"></i> Статистика питомцев
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h4 class="text-info">{{ $petsData['total_pets'] }}</h4>
                            <p class="text-muted">Всего питомцев</p>
                        </div>
                    </div>
                </div>
                
                @if($petsData['by_breed']->count() > 0)
                    <div class="mt-3">
                        <h6>Топ пород</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Порода</th>
                                        <th>Количество</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($petsData['by_breed']->take(5) as $breed => $count)
                                        <tr>
                                            <td>{{ $breed }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $count }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
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
    const clientsData = @json($clientsData);
    const petsData = @json($petsData);
    
    // График распределения клиентов
    const clientsCtx = document.getElementById('clientsChart').getContext('2d');
    new Chart(clientsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Новые клиенты', 'Повторные клиенты'],
            datasets: [{
                data: [clientsData.new_clients, clientsData.repeat_clients],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB'
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
    
    // График питомцев по породам
    const petsCtx = document.getElementById('petsChart').getContext('2d');
    new Chart(petsCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(petsData.by_breed).slice(0, 10),
            datasets: [{
                label: 'Количество питомцев',
                data: Object.values(petsData.by_breed).slice(0, 10),
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
                        text: 'Порода'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Количество питомцев'
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush 