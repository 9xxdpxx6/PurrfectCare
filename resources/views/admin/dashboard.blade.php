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
        <a href="{{ route('admin.visits.index') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-primary">
            <i class="bi bi-calendar-check fs-1 mb-2 text-primary"></i>
            <span class="fw-bold text-primary">Приёмы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-success">
            <i class="bi bi-cart-check fs-1 mb-2 text-success"></i>
            <span class="fw-bold text-success">Заказы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.users.index') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-info">
            <i class="bi bi-people fs-1 mb-2 text-info"></i>
            <span class="fw-bold text-info">Клиенты</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.pets.index') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-warning">
            <i class="bi bi-heart fs-1 mb-2 text-warning"></i>
            <span class="fw-bold text-warning">Питомцы</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.employees.index') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-secondary">
            <i class="bi bi-person-badge fs-1 mb-2 text-secondary"></i>
            <span class="fw-bold text-secondary">Сотрудники</span>
        </a>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('admin.statistics.dashboard') }}" class="btn h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none nav-btn nav-btn-dark">
            <i class="bi bi-graph-up fs-1 mb-2 text-dark-light"></i>
            <span class="fw-bold text-dark-light">Статистика</span>
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
                <h3 class="card-title text-white mb-2">{{ number_format($metrics['total_visits']) }}</h3>
                <h6 class="text-white mb-0">Приёмов</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-success">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cart-check text-white fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-white mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                <h6 class="text-white mb-0">Заказов</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-info">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-cash-stack text-white fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-white mb-2">{{ number_format($metrics['total_revenue'], 0, ',', ' ') }} ₽</h3>
                <h6 class="text-white mb-0">Выручка</h6>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card kpi-card grad-warning">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-people text-white fs-1 me-2"></i>
                </div>
                <h3 class="card-title text-white mb-2">{{ number_format($additionalMetrics['total_clients']) }}</h3>
                <h6 class="text-white mb-0">Новых клиентов</h6>
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
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card border-0 bg-light">
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
/* Адаптивные цвета для темной и светлой темы */
[data-bs-theme="dark"] .text-muted {
    color: #adb5bd !important;
}

[data-bs-theme="dark"] .card {
    background-color: #2b3035;
    border-color: #495057;
}

[data-bs-theme="dark"] .card-header {
    background-color: #343a40;
    border-color: #495057;
}

[data-bs-theme="dark"] .list-group-item {
    background-color: #2b3035;
    border-color: #495057;
    color: #f8f9fa;
}

[data-bs-theme="dark"] .list-group-item:hover {
    background-color: #343a40;
}

[data-bs-theme="dark"] .bg-light {
    background-color: #343a40 !important;
}

[data-bs-theme="dark"] .text-body {
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .text-decoration-underline {
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .text-decoration-underline:hover {
    color: #0d6efd !important;
}

/* Светлая тема */
[data-bs-theme="light"] .text-muted {
    color: #6c757d !important;
}

[data-bs-theme="light"] .text-body {
    color: #212529 !important;
}

[data-bs-theme="light"] .text-decoration-underline {
    color: #212529 !important;
}

[data-bs-theme="light"] .text-decoration-underline:hover {
    color: #0d6efd !important;
}

/* KPI карточки */
.kpi-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.2s;
}

.kpi-card:hover {
    transform: translateY(-5px);
}

.grad-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.grad-success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.grad-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.grad-warning {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* KPI карточки с обводкой */
.kpi-outline {
    border: 2px solid;
    border-radius: 15px;
    transition: all 0.2s;
}

.kpi-outline:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .kpi-outline:hover {
    box-shadow: 0 5px 15px rgba(255,255,255,0.1);
}

.kpi-outline.primary {
    border-color: #667eea;
}

.kpi-outline.success {
    border-color: #f093fb;
}

.kpi-outline.info {
    border-color: #4facfe;
}

/* Навигационные кнопки */
.nav-btn {
    transition: all 0.2s;
    border-radius: 15px;
}

.nav-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .nav-btn:hover {
    box-shadow: 0 5px 15px rgba(255,255,255,0.1);
}

/* Адаптивные цвета для заголовков */
[data-bs-theme="dark"] h1, 
[data-bs-theme="dark"] h2, 
[data-bs-theme="dark"] h3, 
[data-bs-theme="dark"] h4, 
[data-bs-theme="dark"] h5, 
[data-bs-theme="dark"] h6 {
    color: #f8f9fa;
}

[data-bs-theme="light"] h1, 
[data-bs-theme="light"] h2, 
[data-bs-theme="light"] h3, 
[data-bs-theme="light"] h4, 
[data-bs-theme="light"] h5, 
[data-bs-theme="light"] h6 {
    color: #212529;
}

/* Адаптивные цвета для ссылок */
[data-bs-theme="dark"] a {
    color: #0d6efd;
}

[data-bs-theme="dark"] a:hover {
    color: #0a58ca;
}

[data-bs-theme="light"] a {
    color: #0d6efd;
}

[data-bs-theme="light"] a:hover {
    color: #0a58ca;
}

/* Специальные стили для навигационных кнопок в темной теме */
[data-bs-theme="dark"] .nav-btn {
    background-color: #2b3035;
    border-color: currentColor;
}

[data-bs-theme="dark"] .nav-btn:hover {
    background-color: #343a40;
    border-color: currentColor;
}

[data-bs-theme="dark"] .nav-btn.text-primary {
    color: #0d6efd !important;
}

[data-bs-theme="dark"] .nav-btn.text-success {
    color: #198754 !important;
}

[data-bs-theme="dark"] .nav-btn.text-info {
    color: #0dcaf0 !important;
}

[data-bs-theme="dark"] .nav-btn.text-warning {
    color: #ffc107 !important;
}

[data-bs-theme="dark"] .nav-btn.text-secondary {
    color: #6c757d !important;
}

[data-bs-theme="dark"] .nav-btn.text-dark {
    color: #f8f9fa !important;
}

/* Специальный класс для темной/светлой темы */
[data-bs-theme="dark"] .text-dark-light {
    color: #f8f9fa !important;
}

[data-bs-theme="light"] .text-dark-light {
    color: #212529 !important;
}

/* Стили для светлой темы */
[data-bs-theme="light"] .nav-btn {
    background-color: #fff;
    border-color: currentColor;
}

[data-bs-theme="light"] .nav-btn:hover {
    background-color: #f8f9fa;
    border-color: currentColor;
}

/* Специальные стили для навигационных кнопок с правильными цветами */
.nav-btn-primary {
    border: 2px solid #0d6efd !important;
    color: #0d6efd !important;
}

.nav-btn-success {
    border: 2px solid #198754 !important;
    color: #198754 !important;
}

.nav-btn-info {
    border: 2px solid #0dcaf0 !important;
    color: #0dcaf0 !important;
}

.nav-btn-warning {
    border: 2px solid #ffc107 !important;
    color: #ffc107 !important;
}

.nav-btn-secondary {
    border: 2px solid #6c757d !important;
    color: #6c757d !important;
}

.nav-btn-dark {
    border: 2px solid #212529 !important;
    color: #212529 !important;
}

/* Стили для кнопок быстрых действий */
.action-btn {
    border: 1px solid;
    transition: all 0.2s;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.action-btn-primary {
    border-color: #0d6efd !important;
    color: #0d6efd !important;
    background-color: transparent;
}

.action-btn-primary:hover {
    background-color: #0d6efd !important;
    color: white !important;
}

.action-btn-success {
    border-color: #198754 !important;
    color: #198754 !important;
    background-color: transparent;
}

.action-btn-success:hover {
    background-color: #198754 !important;
    color: white !important;
}

.action-btn-info {
    border-color: #0dcaf0 !important;
    color: #0dcaf0 !important;
    background-color: transparent;
}

.action-btn-info:hover {
    background-color: #0dcaf0 !important;
    color: white !important;
}

.action-btn-warning {
    border-color: #ffc107 !important;
    color: #ffc107 !important;
    background-color: transparent;
}

.action-btn-warning:hover {
    background-color: #ffc107 !important;
    color: white !important;
}

.action-btn-secondary {
    border-color: #6c757d !important;
    color: #6c757d !important;
    background-color: transparent;
}

.action-btn-secondary:hover {
    background-color: #6c757d !important;
    color: white !important;
}

.action-btn-dark {
    border-color: #212529 !important;
    color: #212529 !important;
    background-color: transparent;
}

.action-btn-dark:hover {
    background-color: #212529 !important;
    color: white !important;
}

/* Адаптация для темной темы */
[data-bs-theme="dark"] .nav-btn-dark {
    border-color: #f8f9fa !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .action-btn-dark {
    border-color: #f8f9fa !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] .action-btn-dark:hover {
    background-color: #f8f9fa !important;
    color: #212529 !important;
}

/* Адаптивные цвета для текста в карточках */
[data-bs-theme="dark"] .card-body {
    color: #f8f9fa;
}

[data-bs-theme="light"] .card-body {
    color: #212529;
}
</style>
@endpush
