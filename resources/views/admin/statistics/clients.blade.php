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
                    <input type="text" id="date_range" class="form-control" placeholder="С по" style="max-width: 260px;" readonly>
                </div>
                <span class="text-muted">Период: с {{ isset($startDate) ? $startDate->format('d.m.Y') : '' }} по {{ isset($endDate) ? $endDate->format('d.m.Y') : '' }}</span>
            </div>
        </form>
    </div>
</div>

<!-- Основные клиентские метрики -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-person-plus text-primary fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-primary">{{ number_format($clientsData['new_clients']) }}</h3>
                <p class="card-text text-muted">Новых клиентов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people text-success fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-success">{{ number_format($clientsData['repeat_clients']) }}</h3>
                <p class="card-text text-muted">Повторных клиентов</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-heart text-info fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-info">{{ number_format($petsData['total_pets']) }}</h3>
                <p class="card-text text-muted">Питомцев</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-star text-warning fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-warning">{{ $topClients->count() }}</h3>
                <p class="card-text text-muted">Топ клиентов</p>
            </div>
        </div>
    </div>
</div>

<!-- Графики -->
<div class="row">
    <!-- Статистика клиентов -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pie-chart"></i> Распределение клиентов
                </h5>
            </div>
            <div class="card-body">
                <canvas id="clientsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Статистика питомцев по породам -->
    <div class="col-md-6 mb-4">
        <div class="card">
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
                                    @php
                                        $averageOrder = $client['orders_count'] > 0 
                                            ? round($client['total_spent'] / $client['orders_count'], 0) 
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-warning">🥇</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary">🥈</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-warning">🥉</span>
                                            @else
                                                <span class="text-muted">{{ $index + 1 }}</span>
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
                                            {{ number_format($averageOrder, 0, ',', ' ') }} ₽
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
                    $newClientsPercentage = $totalClients > 0 
                        ? round(($clientsData['new_clients'] / $totalClients) * 100, 1) 
                        : 0;
                    $repeatClientsPercentage = $totalClients > 0 
                        ? round(($clientsData['repeat_clients'] / $totalClients) * 100, 1) 
                        : 0;
                @endphp
                
                <div class="mt-3">
                    <h6>Распределение клиентов</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar bg-primary" 
                             role="progressbar" 
                             style="width: {{ $newClientsPercentage }}%"
                             aria-valuenow="{{ $newClientsPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            Новые: {{ $newClientsPercentage }}%
                        </div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $repeatClientsPercentage }}%"
                             aria-valuenow="{{ $repeatClientsPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            Повторные: {{ $repeatClientsPercentage }}%
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