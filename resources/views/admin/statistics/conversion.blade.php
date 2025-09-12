@extends('layouts.admin')

@section('title', 'Статистика конверсии')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-graph-up-arrow"></i> Статистика конверсии
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.statistics.conversion.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-lg-inline">Экспорт в Excel</span>
        </a>
    </div>
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
            </div>
        </form>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Статистика конверсии приёмов в заказы</h3>
                </div>
                <div class="card-body">
                    <!-- Общая конверсия -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card kpi-card grad-info h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="bi bi-graph-up-arrow text-info fs-1 me-2"></i>
                                    </div>
                                    <h3 class="card-title text-info mb-2">{{ $conversionMetrics['overall']['conversion_rate'] }}%</h3>
                                    <h6 class="mb-0">Общая конверсия</h6>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-info" style="width: {{ $conversionMetrics['overall']['conversion_rate'] }}%"></div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        {{ $conversionMetrics['overall']['visits_with_orders'] }} из {{ $conversionMetrics['overall']['total_visits'] }} приёмов
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card kpi-card grad-primary h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="bi bi-calendar-check text-primary fs-1 me-2"></i>
                                    </div>
                                    <h3 class="card-title text-primary mb-2">{{ number_format($conversionMetrics['overall']['total_visits']) }}</h3>
                                    <h6 class="mb-0">Всего приёмов</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card kpi-card grad-success h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="bi bi-bag-check text-success fs-1 me-2"></i>
                                    </div>
                                    <h3 class="card-title text-success mb-2">{{ number_format($conversionMetrics['overall']['total_orders']) }}</h3>
                                    <h6 class="mb-0">Всего заказов</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card kpi-card grad-warning h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-handshake text-warning fs-1 me-2"></i>
                                    </div>
                                    <h3 class="card-title text-warning mb-2">{{ number_format($conversionMetrics['overall']['visits_with_orders']) }}</h3>
                                    <h6 class="mb-0">Приёмов с заказами</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Конверсия по филиалам -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-building"></i> Конверсия по филиалам
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_branches']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Филиал</th>
                                                        <th>Приёмов</th>
                                                        <th>Заказов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_branches'] as $branchData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $branchData['branch']->name }}</strong>
                                                        </td>
                                                        <td>{{ $branchData['visits_count'] }}</td>
                                                        <td>{{ $branchData['orders_count'] }}</td>
                                                        <td>{{ $branchData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $branchData['conversion_rate'] >= 70 ? 'success' : ($branchData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($branchData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $branchData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $branchData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $branchData['conversion_rate'] }}%
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

                    <!-- Конверсия по типам клиентов -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-people"></i> Конверсия по типам клиентов
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_client_types']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Тип клиента</th>
                                                        <th>Количество клиентов</th>
                                                        <th>Приёмов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_client_types'] as $clientTypeData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $clientTypeData['client_type'] }}</strong>
                                                        </td>
                                                        <td>{{ $clientTypeData['clients_count'] }}</td>
                                                        <td>{{ $clientTypeData['visits_count'] }}</td>
                                                        <td>{{ $clientTypeData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $clientTypeData['conversion_rate'] >= 70 ? 'success' : ($clientTypeData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($clientTypeData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $clientTypeData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $clientTypeData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $clientTypeData['conversion_rate'] }}%
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



                    <!-- Конверсия по ветеринарам -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-person-badge"></i> Конверсия по ветеринарам
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_veterinarians']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Ветеринар</th>
                                                        <th>Приёмов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_veterinarians'] as $vetData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $vetData['veterinarian']->name }}</strong>
                                                        </td>
                                                        <td>{{ $vetData['visits_count'] }}</td>
                                                        <td>{{ $vetData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $vetData['conversion_rate'] >= 70 ? 'success' : ($vetData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($vetData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $vetData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $vetData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $vetData['conversion_rate'] }}%
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

                    <!-- Конверсия по статусам приёмов -->
                    <!-- <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-list-check"></i> Конверсия по статусам приёмов
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_visit_statuses']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Статус</th>
                                                        <th>Приёмов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_visit_statuses'] as $statusData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $statusData['status']->name }}</strong>
                                                        </td>
                                                        <td>{{ $statusData['visits_count'] }}</td>
                                                        <td>{{ $statusData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $statusData['conversion_rate'] >= 70 ? 'success' : ($statusData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($statusData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $statusData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $statusData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $statusData['conversion_rate'] }}%
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
                    </div> -->

                    <!-- Конверсия по времени суток -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-clock"></i> Конверсия по времени суток
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_time_of_day']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Время суток</th>
                                                        <th>Приёмов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_time_of_day'] as $timeData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $timeData['time_slot'] }}</strong>
                                                        </td>
                                                        <td>{{ $timeData['visits_count'] }}</td>
                                                        <td>{{ $timeData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $timeData['conversion_rate'] >= 70 ? 'success' : ($timeData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($timeData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $timeData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $timeData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $timeData['conversion_rate'] }}%
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

                    <!-- Конверсия по дням недели -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-calendar-week"></i> Конверсия по дням недели
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if(count($conversionMetrics['by_weekdays']) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>День недели</th>
                                                        <th>Приёмов</th>
                                                        <th>Приёмов с заказами</th>
                                                        <th>Конверсия</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($conversionMetrics['by_weekdays'] as $weekdayData)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $weekdayData['weekday'] }}</strong>
                                                        </td>
                                                        <td>{{ $weekdayData['visits_count'] }}</td>
                                                        <td>{{ $weekdayData['visits_with_orders'] }}</td>
                                                        <td>
                                                            <div class="progress position-relative" style="height: 20px;">
                                                                <div class="progress-bar bg-{{ $weekdayData['conversion_rate'] >= 70 ? 'success' : ($weekdayData['conversion_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                                     role="progressbar" 
                                                                     style="width: {{ min($weekdayData['conversion_rate'], 100) }}%"
                                                                     aria-valuenow="{{ $weekdayData['conversion_rate'] }}" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                                <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.75rem; font-weight: 500; color: {{ $weekdayData['conversion_rate'] > 50 ? 'white' : 'var(--bs-body-color)' }};">
                                                                    {{ $weekdayData['conversion_rate'] }}%
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Стили для кнопок периода */
.btn-group .btn {
    border-radius: 0;
}
.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}
.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
.btn-group .btn.active {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hiddenPeriod = document.getElementById('hidden-period');
    const hiddenStart = document.getElementById('hidden-start');
    const hiddenEnd = document.getElementById('hidden-end');

    window.setPeriod = function(period) {
        hiddenPeriod.value = period;
        
        // Убираем активный класс у всех кнопок
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Добавляем активный класс к нажатой кнопке
        event.target.classList.add('active');
        
        // Если выбран период "all", очищаем поля дат
        if (period === 'all') {
            hiddenStart.value = '';
            hiddenEnd.value = '';
            document.getElementById('date_range').value = '';
        }
        
        // Отправляем форму
        document.getElementById('period-form').submit();
    };

    const rangePicker = window.createDatepicker('#date_range', {
        range: true,
        multipleDatesSeparator: ' по ',
        onSelect({formattedDate}) {
            if (!formattedDate || formattedDate.length < 2) return;
            hiddenPeriod.value = 'custom';
            hiddenStart.value = formattedDate[0];
            hiddenEnd.value = formattedDate[1];
            document.getElementById('period-form').submit();
        }
    });
});
</script>
@endpush
