@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-house-door"></i> Главная
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.visits.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar-plus"></i> Новый приём
            </a>
            <a href="{{ route('admin.orders.create') }}" class="btn btn-outline-success">
                <i class="bi bi-bag-plus"></i> Новый заказ
            </a>
        </div>
    </div>
</div>

<!-- Быстрые переходы -->
@if(auth('admin')->user()->can('visits.read') || auth('admin')->user()->can('orders.read') || auth('admin')->user()->can('clients.read') || auth('admin')->user()->can('pets.read') || auth('admin')->user()->can('employees.read') || auth('admin')->user()->can('statistics_general.read'))
<h5 class="text-muted mb-3">Быстрые переходы</h5>
<div class="row mb-4">
    @can('visits.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-primary h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-calendar-check fs-1 mb-2"></i>
            <span class="fw-bold">Приёмы</span>
        </a>
    </div>
    @endcan
    
    @can('orders.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-bag-check fs-1 mb-2"></i>
            <span class="fw-bold">Заказы</span>
        </a>
    </div>
    @endcan
    
    @can('clients.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-people fs-1 mb-2"></i>
            <span class="fw-bold">Клиенты</span>
        </a>
    </div>
    @endcan
    
    @can('pets.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-warning h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-heart fs-1 mb-2"></i>
            <span class="fw-bold">Питомцы</span>
        </a>
    </div>
    @endcan
    
    @can('employees.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-person-badge fs-1 mb-2"></i>
            <span class="fw-bold">Сотрудники</span>
        </a>
    </div>
    @endcan
    
    @can('statistics_general.read')
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.statistics.dashboard') }}" class="btn btn-outline-dark h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-graph-up fs-1 mb-2"></i>
            <span class="fw-bold">Статистика</span>
        </a>
    </div>
    @endcan
</div>
@endif

<!-- Основные метрики -->
@if(auth('admin')->user()->can('visits.read') || auth('admin')->user()->can('orders.read') || auth('admin')->user()->can('clients.read') || auth('admin')->user()->can('statistics_finance.read'))
<h5 class="text-muted mb-3">Ключевые показатели за текущий месяц</h5>
<div class="row mb-4">
    @can('visits.read')
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-check fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_visits']) }}</h3>
                <h6 class="mb-0">Приёмов</h6>
            </div>
        </div>
    </div>
    @endcan
    
    @can('orders.read')
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-bag-check fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                <h6 class="mb-0">Заказов</h6>
            </div>
        </div>
    </div>
    @endcan
    
    @can('statistics_finance.read')
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_revenue'], 0, ',', ' ') }} ₽</h3>
                <h6 class="mb-0">Выручка</h6>
            </div>
        </div>
    </div>
    @endcan
    
    @can('clients.read')
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-warning h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($additionalMetrics['total_clients']) }}</h3>
                <h6 class="mb-0">Новых клиентов</h6>
            </div>
        </div>
    </div>
    @endcan
</div>
@endif

<!-- Дополнительные метрики -->
@if(auth('admin')->user()->can('orders.read') || auth('admin')->user()->can('pets.read') || auth('admin')->user()->can('statistics_conversion.read'))
<div class="row mb-4">
    @can('orders.read')
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Средний чек</h5>
                <h3 class="text-success mb-0">{{ number_format($additionalMetrics['average_order'], 0, ',', ' ') }} ₽</h3>
            </div>
        </div>
    </div>
    @endcan
    
    @can('statistics_conversion.read')
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Конверсия приёмов в заказы</h5>
                <h3 class="text-info mb-0">{{ $additionalMetrics['conversion_rate'] }}%</h3>
            </div>
        </div>
    </div>
    @endcan
    
    @can('pets.read')
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Новых питомцев</h5>
                <h3 class="text-primary mb-0">{{ number_format($additionalMetrics['total_pets']) }}</h3>
            </div>
        </div>
    </div>
    @endcan
</div>
@endif

<!-- Основной контент -->
@if(auth('admin')->user()->can('visits.read') || auth('admin')->user()->can('orders.read'))
<div class="row">
    <!-- Сегодняшние приёмы -->
    @can('visits.read')
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-day"></i> Сегодняшние приёмы
                </h5>
                <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-primary">Все приёмы</a>
            </div>
            <div class="card-body">
                @if($todayVisits->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($todayVisits as $visit)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('admin.visits.show', $visit->id) }}" class="text-decoration-none text-body">
                                                    @if($visit->pet && $visit->pet->name)
                                                        {{ $visit->pet->client->name ?? 'Неизвестный владелец' }} ({{ $visit->pet->name }})
                                                    @else
                                                        {{ $visit->pet->client->name ?? 'Неизвестный владелец' }}
                                                    @endif
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                {{ $visit->schedule->veterinarian->name ?? 'Не назначен' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $visit->status->color ?? 'secondary' }} rounded-pill">
                                                {{ $visit->status->name ?? 'Неизвестно' }}
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> {{ $visit->starts_at->format('H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">Сегодня приёмов нет</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan
    
    <!-- Последние заказы -->
    @can('orders.read')
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bag"></i> Последние заказы
                </h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success">Все заказы</a>
            </div>
            <div class="card-body">
                @if($recentOrders->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentOrders as $order)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none text-body">
                                                    Заказ #{{ $order->id }}
                                                </a>
                                            </h6>
                                                                                         <small class="text-muted">
                                                 {{ $order->client->name ?? 'Неизвестный клиент' }} • 
                                                 {{ $order->pet->name ?? 'Без питомца' }}
                                             </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $order->status->color ?? 'secondary' }} rounded-pill">
                                                {{ $order->status->name ?? 'Неизвестно' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $order->created_at->format('d.m.Y H:i') }}
                                        </small>
                                        <strong class="text-success">{{ number_format($order->total, 0, ',', ' ') }} ₽</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-bag-x fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">Заказов пока нет</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan
</div>
@endif

<!-- Дополнительная информация -->
@if(auth('admin')->user()->can('visits.read') || auth('admin')->user()->can('services.read'))
<div class="row">
    <!-- Ближайшие приёмы -->
    @can('visits.read')
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-week"></i> Завтрашние приёмы
                </h5>
            </div>
            <div class="card-body">
                @if($tomorrowVisits->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($tomorrowVisits as $visit)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="{{ route('admin.visits.show', $visit->id) }}" class="text-decoration-none text-body">
                                            @if($visit->pet && $visit->pet->name)
                                                {{ $visit->pet->client->name ?? 'Неизвестный владелец' }} ({{ $visit->pet->name }})
                                            @else
                                                {{ $visit->pet->client->name ?? 'Неизвестный владелец' }}
                                            @endif
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        {{ $visit->schedule->veterinarian->name ?? 'Не назначен' }} • 
                                        {{ $visit->starts_at->format('H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">Завтра приёмов нет</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan
    
    <!-- Топ услуг -->
    @can('services.read')
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-star"></i> Популярные услуги
                </h5>
            </div>
            <div class="card-body">
                @if($topServices->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topServices as $service)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">
                                        <a href="{{ route('admin.services.show', $service['service']->id) }}" class="text-decoration-none text-body">
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
    @endcan
</div>
@endif

<!-- Статистика по дням недели -->
@can('statistics_general.read')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Средние показатели по дням недели за текущий месяц
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($weekStats['weekdays'] as $day => $stats)
                        <div class="col-md-6 col-sm-6 mb-3">
                            <div class="card border h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        @if($stats['rank'])
                                            @php
                                                $trophyColors = ['warning', 'secondary', 'danger'];
                                                $trophyColor = $trophyColors[$stats['rank'] - 1] ?? 'secondary';
                                            @endphp
                                            <i class="bi bi-trophy-fill text-{{ $trophyColor }} me-2"></i>
                                        @endif
                                        <h6 class="card-title text-muted mb-0">{{ $day }}</h6>
                                    </div>
                                    <div class="row">
                                        @can('visits.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Приёмы</small>
                                            <strong class="text-primary">{{ $stats['visits'] }}</strong>
                                        </div>
                                        @endcan
                                        @can('orders.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Заказы</small>
                                            <strong class="text-success">{{ $stats['orders'] }}</strong>
                                        </div>
                                        @endcan
                                        @can('statistics_finance.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Выручка</small>
                                            <strong class="text-info">{{ number_format($stats['revenue'], 0, ',', ' ') }} ₽</strong>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($weekStats['bestDay'])
                        <div class="col-md-6 col-sm-6 mb-3">
                            <div class="card border h-100">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <h6 class="card-title text-muted mb-0">
                                            Лучший день ({{ $weekStats['bestDay']['date']->format('d.m.Y') }})
                                        </h6>
                                    </div>
                                    <div class="row">
                                        @can('visits.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Приёмы</small>
                                            <strong class="text-primary">{{ $weekStats['bestDay']['visits'] }}</strong>
                                        </div>
                                        @endcan
                                        @can('orders.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Заказы</small>
                                            <strong class="text-success">{{ $weekStats['bestDay']['orders'] }}</strong>
                                        </div>
                                        @endcan
                                        @can('statistics_finance.read')
                                        <div class="col-4">
                                            <small class="text-muted d-block">Выручка</small>
                                            <strong class="text-info">{{ number_format($weekStats['bestDay']['revenue'], 0, ',', ' ') }} ₽</strong>
                                        </div>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endcan



<!-- Общая информация -->
@if(auth('admin')->user()->can('settings_branches.read') || auth('admin')->user()->can('employees.read') || auth('admin')->user()->can('services.read') || auth('admin')->user()->can('visits.create') || auth('admin')->user()->can('orders.create') || auth('admin')->user()->can('clients.create') || auth('admin')->user()->can('pets.create') || auth('admin')->user()->can('schedules.read') || auth('admin')->user()->can('drugs.read') || auth('admin')->user()->can('settings_analysis_types.read'))
<div class="row">
    @if(auth('admin')->user()->can('settings_branches.read') || auth('admin')->user()->can('employees.read') || auth('admin')->user()->can('services.read'))
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Общая информация
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="row">
                    @can('settings_branches.read')
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Филиалов</h6>
                        <h4 class="text-primary mb-0">{{ $additionalMetrics['total_branches'] }}</h4>
                    </div>
                    @endcan
                    @can('employees.read')
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Всего сотрудников</h6>
                        <h4 class="text-success mb-0">{{ $additionalMetrics['total_employees'] }}</h4>
                    </div>
                    @endcan
                    @can('services.read')
                    <div class="col-6 mt-3">
                        <h6 class="text-muted mb-1">Услуг</h6>
                        <h4 class="text-info mb-0">{{ $metrics['total_services'] }}</h4>
                    </div>
                    @endcan
                    @can('employees.read')
                    <div class="col-6 mt-3">
                        <h6 class="text-muted mb-1">Ветеринаров</h6>
                        <h4 class="text-warning mb-0">{{ $metrics['total_veterinarians'] }}</h4>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if(auth('admin')->user()->can('visits.create') || auth('admin')->user()->can('orders.create') || auth('admin')->user()->can('clients.create') || auth('admin')->user()->can('pets.create') || auth('admin')->user()->can('schedules.read') || auth('admin')->user()->can('drugs.read') || auth('admin')->user()->can('settings_analysis_types.read'))
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Быстрые действия
                </h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="row">
                    @can('visits.create')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.visits.create') }}" class="btn w-100 action-btn action-btn-primary text-start ms-3">
                            <i class="bi bi-calendar-plus"></i> Новый приём
                        </a>
                    </div>
                    @endcan
                    @can('orders.create')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.orders.create') }}" class="btn w-100 action-btn action-btn-success text-start ms-3">
                            <i class="bi bi-bag-plus"></i> Новый заказ
                        </a>
                    </div>
                    @endcan
                    @can('clients.create')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.users.create') }}" class="btn w-100 action-btn action-btn-info text-start ms-3">
                            <i class="bi bi-person-plus"></i> Новый клиент
                        </a>
                    </div>
                    @endcan
                    @can('pets.create')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.pets.create') }}" class="btn w-100 action-btn action-btn-warning text-start ms-3">
                            <i class="bi bi-heart"></i> Новый питомец
                        </a>
                    </div>
                    @endcan
                    @can('schedules.read')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.schedules.index') }}" class="btn w-100 action-btn action-btn-secondary text-start ms-3">
                            <i class="bi bi-calendar-week"></i> Расписание
                        </a>
                    </div>
                    @endcan
                    @can('drugs.read')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.drugs.index') }}" class="btn w-100 action-btn action-btn-dark text-start ms-3">
                            <i class="bi bi-capsule"></i> Препараты
                        </a>
                    </div>
                    @endcan
                    @can('settings_analysis_types.read')
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.settings.index') }}" class="btn w-100 action-btn action-btn-light text-start ms-3">
                            <i class="bi bi-gear"></i> Настройки
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Fallback контент для пользователей с ограниченными правами -->
@if(!auth('admin')->user()->can('visits.read') && !auth('admin')->user()->can('orders.read') && !auth('admin')->user()->can('clients.read') && !auth('admin')->user()->can('pets.read') && !auth('admin')->user()->can('employees.read') && !auth('admin')->user()->can('statistics_general.read') && !auth('admin')->user()->can('services.read') && !auth('admin')->user()->can('settings_branches.read') && !auth('admin')->user()->can('visits.create') && !auth('admin')->user()->can('orders.create') && !auth('admin')->user()->can('clients.create') && !auth('admin')->user()->can('pets.create') && !auth('admin')->user()->can('schedules.read') && !auth('admin')->user()->can('drugs.read') && !auth('admin')->user()->can('settings_analysis_types.read'))
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-check fs-1 text-muted mb-3"></i>
                <h4 class="text-muted mb-3">Добро пожаловать в систему PurrfectCare</h4>
                <p class="text-muted mb-4">
                    Ваш аккаунт имеет ограниченные права доступа. 
                    Обратитесь к администратору для получения дополнительных разрешений.
                </p>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>Доступные функции
                            </h6>
                            <p class="mb-0">
                                В зависимости от вашей роли, вам могут быть доступны следующие функции:
                            </p>
                            <ul class="list-unstyled mt-2 mb-0">
                                <li><i class="bi bi-check-circle text-success me-2"></i>Просмотр приёмов (для ветеринаров)</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Просмотр заказов (для менеджеров и бухгалтеров)</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Просмотр клиентов и питомцев</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Создание новых записей (в зависимости от роли)</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Просмотр статистики (для администраторов)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
/* Специальные стили для кнопки Статистика */
[data-bs-theme="dark"] .btn-outline-dark {
    color: #ffffff !important;
    border-color: #ffffff !important;
}

[data-bs-theme="dark"] .btn-outline-dark:hover {
    background-color: #ffffff !important;
    color: #000000 !important;
}

[data-bs-theme="light"] .btn-outline-dark {
    color: #000000 !important;
    border-color: #000000 !important;
}

[data-bs-theme="light"] .btn-outline-dark:hover {
    background-color: #000000 !important;
    color: #ffffff !important;
}
</style>
@endpush

