@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-house-door"></i> Главная
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.visits.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Новый приём
            </a>
            <a href="{{ route('admin.orders.create') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-cart-plus"></i> Новый заказ
            </a>
        </div>
    </div>
</div>

<!-- Быстрые переходы -->
<h5 class="text-muted mb-3">Быстрые переходы</h5>
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-primary h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-calendar-check fs-1 mb-2"></i>
            <span class="fw-bold">Приёмы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-cart-check fs-1 mb-2"></i>
            <span class="fw-bold">Заказы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-people fs-1 mb-2"></i>
            <span class="fw-bold">Клиенты</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-warning h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-heart fs-1 mb-2"></i>
            <span class="fw-bold">Питомцы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-person-badge fs-1 mb-2"></i>
            <span class="fw-bold">Сотрудники</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.statistics.dashboard') }}" class="btn btn-outline-dark h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn">
            <i class="bi bi-graph-up fs-1 mb-2"></i>
            <span class="fw-bold">Статистика</span>
        </a>
    </div>
</div>

<!-- Основные метрики -->
<h5 class="text-muted mb-3">Ключевые показатели за текущий месяц</h5>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-calendar-check fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_visits']) }}</h3>
                <h6 class= mb-0">Приёмов</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cart-check fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                <h6 class= mb-0">Заказов</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($metrics['total_revenue'], 0, ',', ' ') }} ₽</h3>
                <h6 class= mb-0">Выручка</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people fs-1 me-2"></i>
                </div>
                <h3 class="card-title mb-2">{{ number_format($additionalMetrics['total_clients']) }}</h3>
                <h6 class= mb-0">Новых клиентов</h6>
            </div>
        </div>
    </div>
</div>

<!-- Дополнительные метрики -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline success h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Средний чек</h5>
                <h3 class="text-success mb-0">{{ number_format($additionalMetrics['average_order'], 0, ',', ' ') }} ₽</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline info h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Конверсия приёмов в заказы</h5>
                <h3 class="text-info mb-0">{{ $additionalMetrics['conversion_rate'] }}%</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card kpi-outline primary h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title mb-2">Новых питомцев</h5>
                <h3 class="text-primary mb-0">{{ number_format($additionalMetrics['total_pets']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Основной контент -->
<div class="row">
    <!-- Сегодняшние приёмы -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-day"></i> Сегодняшние приёмы
                </h5>
                <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-outline-primary">Все приёмы</a>
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
                                                <a href="{{ route('admin.visits.show', $visit->id) }}" class="text-decoration-none">
                                                    {{ $visit->pet->name ?? 'Без имени' }}
                                                </a>
                                            </h6>
                                                                                         <small class="text-muted">
                                                 {{ $visit->pet->client->name ?? 'Неизвестный владелец' }} • 
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
    
    <!-- Последние заказы -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-cart"></i> Последние заказы
                </h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-success">Все заказы</a>
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
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">
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
                        <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">Заказов пока нет</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Дополнительная информация -->
<div class="row">
    <!-- Ближайшие приёмы -->
    <div class="col-lg-6 mb-4">
        <div class="card">
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
                                        <a href="{{ route('admin.visits.show', $visit->id) }}" class="text-decoration-none">
                                            {{ $visit->pet->name ?? 'Без имени' }}
                                        </a>
                                    </h6>
                                                                         <small class="text-muted">
                                         {{ $visit->pet->client->name ?? 'Неизвестный владелец' }} • 
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
    
    <!-- Топ услуг -->
    <div class="col-lg-6 mb-4">
        <div class="card">
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

<!-- Статистика по дням недели -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика по дням недели
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($weekStats as $day => $stats)
                        <div class="col-md-6 col-sm-6 mb-3">
                            <div class="card border-0">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted mb-2">{{ $day }}</h6>
                                    <div class="row">
                                        <div class="col-4">
                                            <small class="text-muted d-block">Приёмы</small>
                                            <strong class="text-primary">{{ $stats['visits'] }}</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Заказы</small>
                                            <strong class="text-success">{{ $stats['orders'] }}</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">Выручка</small>
                                            <strong class="text-info">{{ number_format($stats['revenue'], 0, ',', ' ') }} ₽</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Общая информация -->
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
                        <h6 class="text-muted mb-1">Филиалов</h6>
                        <h4 class="text-primary mb-0">{{ $additionalMetrics['total_branches'] }}</h4>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Всего сотрудников</h6>
                        <h4 class="text-success mb-0">{{ $additionalMetrics['total_employees'] }}</h4>
                    </div>
                    <div class="col-6 mt-3">
                        <h6 class="text-muted mb-1">Услуг</h6>
                        <h4 class="text-info mb-0">{{ $metrics['total_services'] }}</h4>
                    </div>
                    <div class="col-6 mt-3">
                        <h6 class="text-muted mb-1">Ветеринаров</h6>
                        <h4 class="text-warning mb-0">{{ $metrics['total_veterinarians'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Быстрые действия
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.visits.create') }}" class="btn btn-sm w-100 action-btn action-btn-primary">
                            <i class="bi bi-plus-circle"></i> Новый приём
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.orders.create') }}" class="btn btn-sm w-100 action-btn action-btn-success">
                            <i class="bi bi-cart-plus"></i> Новый заказ
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-sm w-100 action-btn action-btn-info">
                            <i class="bi bi-person-plus"></i> Новый клиент
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.pets.create') }}" class="btn btn-sm w-100 action-btn action-btn-warning">
                            <i class="bi bi-heart"></i> Новый питомец
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm w-100 action-btn action-btn-secondary">
                            <i class="bi bi-calendar-week"></i> Расписание
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('admin.drugs.index') }}" class="btn btn-sm w-100 action-btn action-btn-dark">
                            <i class="bi bi-capsule"></i> Препараты
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

