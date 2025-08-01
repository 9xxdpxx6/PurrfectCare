@extends('layouts.admin')

@section('title', 'Сотрудник: ' . $employee->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 col-12 col-md-7 col-xl-8">Сотрудник: {{ $employee->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Основная информация -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i> {{ $employee->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-envelope"></i> Email:</strong>
                            {{ $employee->email }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-telephone"></i> Телефон:</strong>
                            {{ $employee->phone }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-briefcase"></i> Специальности:</strong>
                            @if($employee->specialties->count())
                                {{ $employee->specialties->pluck('name')->join(', ') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-building"></i> Филиалы:</strong>
                            @if($employee->branches->count())
                                {{ $employee->branches->pluck('name')->join(', ') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Добавлен:</strong>
                            {{ $employee->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Аккордеон активности -->
        <div class="accordion mb-4" id="activityAccordion">
            <!-- Заказы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ordersCollapse" aria-expanded="true" aria-controls="ordersCollapse">
                        <i class="bi bi-cart me-2"></i> Последние заказы ({{ $ordersTotal }})
                    </button>
                </h2>
                <div id="ordersCollapse" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        @if($orders->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($orders as $order)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="row align-items-center g-2">
                                            <!-- Клиент и дата -->
                                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                                <h6 class="mb-1">{{ $order->client->name ?? 'Клиент не указан' }}</h6>
                                                <small class="text-muted">{{ $order->created_at->format('d.m.Y') }}</small>
                                            </div>
                                            
                                            <!-- Питомец -->
                                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                                <div>
                                                    <small class="text-muted d-block">Питомец</small>
                                                    <span>{{ $order->pet->name ?? 'Питомец не указан' }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Сумма -->
                                            <div class="col-12 col-md-3 text-md-end">
                                                <div>
                                                    <small class="text-muted d-block">Сумма</small>
                                                    <span>{{ number_format($order->total, 2, ',', ' ') }} ₽</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Действие на больших экранах -->
                                            <div class="col-12 col-md-1 text-end align-self-center d-none d-md-block">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm" title="Подробнее">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Действие на маленьких экранах -->
                                        <div class="row d-md-none mt-2">
                                            <div class="col-12">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bi bi-eye"></i> Подробнее
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Заказы не найдены</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Вакцинации -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vaccinationsCollapse" aria-expanded="false" aria-controls="vaccinationsCollapse">
                        <i class="bi bi-shield-check me-2"></i> Последние вакцинации ({{ $vaccinationsTotal }})
                    </button>
                </h2>
                <div id="vaccinationsCollapse" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @if($vaccinations->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($vaccinations as $vaccination)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $vaccination->pet->name ?? 'Питомец не указан' }}</h6>
                                                <p class="text-muted small mb-0">{{ $vaccination->pet->client->name ?? 'Клиент не указан' }}</p>
                                            </div>
                                            
                                            <div class="text-end d-flex align-items-center gap-2 d-none d-md-flex">
                                                <small class="text-muted">{{ $vaccination->administered_at->format('d.m.Y') }}</small>
                                                <a href="{{ route('admin.vaccinations.show', $vaccination) }}" class="btn btn-outline-primary btn-sm" title="Подробнее">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                            
                                            <div class="text-end d-md-none">
                                                <small class="text-muted">{{ $vaccination->administered_at->format('d.m.Y') }}</small>
                                            </div>
                                        </div>
                                        <!-- Действие на маленьких экранах -->
                                        <div class="d-md-none mt-2">
                                            <a href="{{ route('admin.vaccinations.show', $vaccination) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-eye"></i> Подробнее
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Вакцинации не найдены</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Анализы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#labTestsCollapse" aria-expanded="false" aria-controls="labTestsCollapse">
                        <i class="bi bi-clipboard-data me-2"></i> Последние анализы ({{ $labTestsTotal }})
                    </button>
                </h2>
                <div id="labTestsCollapse" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @if($labTests->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($labTests as $labTest)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="row align-items-center g-2">
                                            <!-- Питомец и клиент -->
                                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                                <h6 class="mb-1">{{ $labTest->pet->name ?? 'Питомец не указан' }}</h6>
                                                <small class="text-muted">{{ $labTest->pet->client->name ?? 'Клиент не указан' }}</small>
                                            </div>
                                            
                                            <!-- Тип анализа -->
                                            <div class="col-12 col-md-4 mb-2 mb-md-0">
                                                <div>
                                                    <small class="text-muted d-block">Тип анализа</small>
                                                    <span>{{ $labTest->labTestType->name ?? 'Тип не указан' }}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Дата -->
                                            <div class="col-12 col-md-3 align-self-start">
                                                <div class="text-end">
                                                    <small class="text-muted">{{ $labTest->created_at->format('d.m.Y') }}</small>
                                                </div>
                                            </div>
                                            
                                            <!-- Действие на больших экранах -->
                                            <div class="col-12 col-md-1 text-end d-none d-md-block">
                                                <a href="{{ route('admin.lab-tests.show', $labTest) }}" class="btn btn-outline-primary btn-sm" title="Подробнее">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Действие на маленьких экранах -->
                                        <div class="row d-md-none mt-2">
                                            <div class="col-12">
                                                <a href="{{ route('admin.lab-tests.show', $labTest) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bi bi-eye"></i> Подробнее
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Анализы не найдены</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Статистика -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего заказов:</span>
                    <strong>{{ $ordersTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего вакцинаций:</span>
                    <strong>{{ $vaccinationsTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего анализов:</span>
                    <strong>{{ $labTestsTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Специальностей:</span>
                    <strong>{{ $employee->specialties->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Филиалов:</span>
                    <strong>{{ $employee->branches->count() }}</strong>
                </div>
                @if($orders->count() > 0)
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Общая сумма заказов:</span>
                        <strong>{{ number_format($orders->sum('total'), 2, ',', ' ') }} ₽</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Последний заказ:</span>
                        <strong>{{ $orders->first()->created_at->format('d.m.Y') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Действия -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Действия
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <a href="{{ route('admin.employees.resetPassword', $employee) }}" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Сбросить пароль
                    </a>
                    <hr>
                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить сотрудника?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 