@extends('layouts.admin')

@section('title', 'Личный кабинет')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Личный кабинет</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.employees.profile.edit') }}" class="btn btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> <span class="d-none d-sm-inline">Редактировать профиль</span>
        </a>
    </div>
</div>

<!-- Основная информация -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i> {{ $employee->name }}
                    @if($employee->specialties->where('is_veterinarian', true)->count() > 0)
                        <span class="text-success ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Медицинский персонал">
                            <i class="bi bi-heart-pulse"></i>
                        </span>
                    @else
                        <span class="text-secondary ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Сервисный персонал">
                            <i class="bi bi-person-vcard"></i>
                        </span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-envelope text-primary me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <strong>{{ $employee->email }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-telephone text-success me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Телефон</small>
                                <strong>{{ $employee->phone }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-briefcase text-warning me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Специальности</small>
                                <strong>
                                    @if($employee->specialties->count())
                                        {{ $employee->specialties->pluck('name')->join(', ') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-building text-info me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Филиалы</small>
                                <strong>
                                    @if($employee->branches->count())
                                        {{ $employee->branches->pluck('name')->join(', ') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-plus text-secondary me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">В системе с</small>
                                <strong>{{ $employee->created_at->format('d.m.Y') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock text-body me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Последний вход</small>
                                <strong>{{ $employee->last_login_at ? $employee->last_login_at->format('d.m.Y H:i') : 'Не зафиксирован' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Статистика и активности -->
<div class="row">
    <!-- Основная статистика -->
    <div class="col-12 col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Общая статистика
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Заказы:</span>
                    <strong class="text-primary">{{ $stats['total_orders'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Приёмы:</span>
                    <strong class="text-success">{{ $stats['total_visits'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Вакцинации:</span>
                    <strong class="text-warning">{{ $stats['total_vaccinations'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Анализы:</span>
                    <strong class="text-info">{{ $stats['total_lab_tests'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small">Расписания:</span>
                    <strong class="text-secondary">{{ $stats['total_schedules'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Текущий период -->
    <div class="col-12 col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar-event"></i> Текущий период
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Приёмов за сегодня:</span>
                    <strong class="text-primary">{{ $stats['today_visits'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Приёмов за месяц:</span>
                    <strong class="text-info">{{ $stats['this_month_visits'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small">Заказов за месяц:</span>
                    <strong class="text-success">{{ $stats['this_month_orders'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Финансы -->
    <div class="col-12 col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h6 class="card-title mb-0">
                    <i class="bi bi-currency-dollar"></i> Финансы
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Общая сумма:</span>
                    <strong class="text-success">{{ number_format($stats['total_orders_amount'] ?? 0, 0, ',', ' ') }} ₽</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Средний чек:</span>
                    <strong class="text-success">{{ number_format($stats['average_order_amount'] ?? 0, 0, ',', ' ') }} ₽</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small">Заказов:</span>
                    <strong class="text-success">{{ $stats['total_orders'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Продуктивность -->
    <div class="col-12 col-xl-3 col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-dark">
                <h6 class="card-title mb-0">
                    <i class="bi bi-speedometer2"></i> Продуктивность
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Приёмов/час:</span>
                    <strong class="text-primary">{{ $stats['visits_per_hour'] ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small">Эффективность:</span>
                    <strong class="text-info">{{ $stats['schedule_efficiency'] ?? 0 }}%</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small">Заполненность:</span>
                    <strong class="text-warning">{{ $stats['schedule_utilization'] ?? 0 }}%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Детальная статистика -->
<div class="row">
    <!-- Дни недели -->
    <div class="col-12 col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-calendar-week"></i> Приёмы по дням недели
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Понедельник:</small>
                            <strong class="text-primary">{{ $stats['monday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Вторник:</small>
                            <strong class="text-primary">{{ $stats['tuesday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Среда:</small>
                            <strong class="text-primary">{{ $stats['wednesday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Четверг:</small>
                            <strong class="text-primary">{{ $stats['thursday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Пятница:</small>
                            <strong class="text-primary">{{ $stats['friday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Суббота:</small>
                            <strong class="text-warning">{{ $stats['saturday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-between">
                            <small>Воскресенье:</small>
                            <strong class="text-warning">{{ $stats['sunday_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Время суток -->
    <div class="col-12 col-xl-6 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Приёмы по времени
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Утренние (8:00-12:00)</small>
                                <span class="text-muted small">Ранние часы</span>
                            </div>
                            <strong class="text-info fs-5">{{ $stats['morning_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Дневные (12:00-17:00)</small>
                                <span class="text-muted small">Основное время</span>
                            </div>
                            <strong class="text-success fs-5">{{ $stats['afternoon_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Вечерние (17:00-21:00)</small>
                                <span class="text-muted small">Поздние часы</span>
                            </div>
                            <strong class="text-warning fs-5">{{ $stats['evening_visits'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Типы услуг и последние активности -->
<div class="row">
    <!-- Типы услуг -->
    <div class="col-12 col-xl-4 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-list-check"></i> По типам услуг
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <small class="text-muted d-block">Первичные приёмы</small>
                        <span class="text-muted small">Новые клиенты</span>
                    </div>
                    <strong class="text-success fs-5">{{ $stats['primary_visits'] ?? 0 }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <small class="text-muted d-block">Повторные приёмы</small>
                        <span class="text-muted small">Постоянные клиенты</span>
                    </div>
                    <strong class="text-primary fs-5">{{ $stats['repeat_visits'] ?? 0 }}</strong>
                </div>
                <!-- <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-muted d-block">Экстренные</small>
                        <span class="text-muted small">Срочные случаи</span>
                    </div>
                    <strong class="text-danger fs-5">{{ $stats['emergency_visits'] ?? 0 }}</strong>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Последние активности -->
    <div class="col-12 col-xl-8 col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-activity"></i> Последние активности
                </h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="activityAccordion">
                                         <!-- Приёмы -->
                     @if($recentActivities['recent_visits']->count() > 0)
                     <div class="accordion-item">
                         <h2 class="accordion-header">
                             <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#visitsCollapse" aria-expanded="false" aria-controls="visitsCollapse">
                                 <i class="bi bi-calendar-check me-2"></i> Последние приёмы
                             </button>
                         </h2>
                         <div id="visitsCollapse" class="accordion-collapse collapse">
                             <div class="accordion-body p-2">
                                 <div class="d-flex flex-column gap-2">
                                     @foreach($recentActivities['recent_visits']->take(3) as $visit)
                                         <a href="{{ route('admin.visits.show', $visit->id) }}" class="text-decoration-none">
                                             <div class="border rounded p-2 bg-body-tertiary hover-shadow">
                                                 <div class="d-flex justify-content-between align-items-start">
                                                     <div class="flex-grow-1">
                                                         <h6 class="mb-1 small text-body">{{ $visit->pet->name ?? 'Питомец не указан' }}</h6>
                                                         <p class="text-muted small mb-0">{{ $visit->pet->client->name ?? 'Клиент не указан' }}</p>
                                                     </div>
                                                     <div class="text-end">
                                                         <small class="text-muted">{{ $visit->starts_at ? $visit->starts_at->format('d.m.Y H:i') : 'Дата не указана' }}</small>
                                                     </div>
                                                 </div>
                                             </div>
                                         </a>
                                     @endforeach
                                 </div>
                             </div>
                         </div>
                     </div>
                     @endif

                     <!-- Заказы -->
                     @if($recentActivities['recent_orders']->count() > 0)
                     <div class="accordion-item">
                         <h2 class="accordion-header">
                             <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#ordersCollapse" aria-expanded="false" aria-controls="ordersCollapse">
                                 <i class="bi bi-bag me-2"></i> Последние заказы
                             </button>
                         </h2>
                         <div id="ordersCollapse" class="accordion-collapse collapse">
                             <div class="accordion-body p-2">
                                 <div class="d-flex flex-column gap-2">
                                     @foreach($recentActivities['recent_orders']->take(3) as $order)
                                         <a href="{{ route('admin.orders.show', $order->id) }}" class="text-decoration-none">
                                             <div class="border rounded p-2 bg-body-tertiary hover-shadow">
                                                 <div class="d-flex justify-content-between align-items-start">
                                                     <div class="flex-grow-1">
                                                         <h6 class="mb-1 small text-body">{{ $order->client->name ?? 'Клиент не указан' }}</h6>
                                                         <p class="text-muted small mb-0">{{ $order->pet->name ?? 'Питомец не указан' }}</p>
                                                     </div>
                                                     <div class="text-end">
                                                         <small class="text-muted">{{ $order->created_at ? $order->created_at->format('d.m.Y') : 'Дата не указана' }}</small>
                                                         <div class="fw-bold small text-success">{{ number_format($order->total, 0, ',', ' ') }} ₽</div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </a>
                                     @endforeach
                                 </div>
                             </div>
                         </div>
                     </div>
                     @endif

                     <!-- Вакцинации -->
                     @if($recentActivities['recent_vaccinations']->count() > 0)
                     <div class="accordion-item">
                         <h2 class="accordion-header">
                             <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#vaccinationsCollapse" aria-expanded="false" aria-controls="vaccinationsCollapse">
                                 <i class="bi bi-shield-check me-2"></i> Последние вакцинации
                             </button>
                         </h2>
                         <div id="vaccinationsCollapse" class="accordion-collapse collapse">
                             <div class="accordion-body p-2">
                                 <div class="d-flex flex-column gap-2">
                                     @foreach($recentActivities['recent_vaccinations']->take(3) as $vaccination)
                                         <a href="{{ route('admin.vaccinations.show', $vaccination->id) }}" class="text-decoration-none">
                                             <div class="border rounded p-2 bg-body-tertiary hover-shadow">
                                                 <div class="d-flex justify-content-between align-items-start">
                                                     <div class="flex-grow-1">
                                                         <h6 class="mb-1 small text-body">{{ $vaccination->pet->name ?? 'Питомец не указан' }}</h6>
                                                         <p class="text-muted small mb-0">{{ $vaccination->pet->client->name ?? 'Клиент не указан' }}</p>
                                                     </div>
                                                     <div class="text-end">
                                                         <small class="text-muted">{{ $vaccination->administered_at ? $vaccination->administered_at->format('d.m.Y') : 'Дата не указана' }}</small>
                                                     </div>
                                                 </div>
                                             </div>
                                         </a>
                                     @endforeach
                                 </div>
                             </div>
                         </div>
                     </div>
                     @endif

                     <!-- Анализы -->
                     @if($recentActivities['recent_lab_tests']->count() > 0)
                     <div class="accordion-item">
                         <h2 class="accordion-header">
                             <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#labTestsCollapse" aria-expanded="false" aria-controls="labTestsCollapse">
                                 <i class="bi bi-clipboard-data me-2"></i> Последние анализы
                             </button>
                         </h2>
                         <div id="labTestsCollapse" class="accordion-collapse collapse">
                             <div class="accordion-body p-2">
                                 <div class="d-flex flex-column gap-2">
                                     @foreach($recentActivities['recent_lab_tests']->take(3) as $labTest)
                                         <a href="{{ route('admin.lab-tests.show', $labTest->id) }}" class="text-decoration-none">
                                             <div class="border rounded p-2 bg-body-tertiary hover-shadow">
                                                 <div class="d-flex justify-content-between align-items-start">
                                                     <div class="flex-grow-1">
                                                         <h6 class="mb-1 small text-body">{{ $labTest->pet->name ?? 'Питомец не указан' }}</h6>
                                                         <p class="text-muted small mb-0">{{ $labTest->pet->client->name ?? 'Клиент не указан' }}</p>
                                                     </div>
                                                     <div class="text-end">
                                                         <small class="text-muted">{{ $labTest->created_at ? $labTest->created_at->format('d.m.Y') : 'Дата не указана' }}</small>
                                                     </div>
                                                 </div>
                                             </div>
                                         </a>
                                     @endforeach
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
@endsection

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.2s ease-in-out;
    }
    
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        border-color: var(--bs-primary) !important;
    }
    
    .accordion-body a:hover {
        text-decoration: none;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Инициализация тултипов
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
