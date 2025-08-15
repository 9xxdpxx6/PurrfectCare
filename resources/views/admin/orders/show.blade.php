@extends('layouts.admin')

@section('title', 'Заказ #' . $item->id)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 d-flex align-items-center">
        <span class="me-2">Заказ #{{ $item->id }}</span>
        <span class="fs-6 badge" style="background-color: {{ $item->status->color ?? '#6c757d' }}; color: white;">{{ $item->status->name }}</span>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.edit', $item) }}" class="btn btn-outline-warning me-2">
            <i class="bi bi-pencil"></i> Редактировать
        </a>
        <form action="{{ route('admin.orders.destroy', $item) }}" method="POST" class="d-inline me-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"
                onclick="return confirm('Удалить заказ #{{ $item->id }}? Это действие нельзя отменить.');">
                <i class="bi bi-trash"></i> Удалить
            </button>
        </form>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Основная информация -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Основная информация</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Клиент:</span>
                            <a href="{{ route('admin.users.show', $item->client) }}" class="text-decoration-none">
                                <i class="bi bi-person me-1"></i>{{ $item->client->name }}
                            </a>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Питомец:</span>
                            <a href="{{ route('admin.pets.show', $item->pet) }}" class="text-decoration-none">
                                <i class="bi bi-heart me-1"></i>{{ $item->pet->name }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Менеджер:</span>
                            <a href="{{ route('admin.employees.show', $item->manager) }}" class="text-decoration-none">
                                <i class="bi bi-person-badge me-1"></i>{{ $item->manager->name }}
                            </a>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Филиал:</span>
                            <span>{{ $item->branch->name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Статус:</span>
                            <span class="badge" style="background-color: {{ $item->status->color ?? '#6c757d' }}; color: white;">{{ $item->status->name }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Создан:</span>
                            <span>{{ $item->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="d-flex gap-2">
                                @if($item->is_paid)
                                    <span class="badge bg-success">Оплачен</span>
                                @else
                                    <span class="badge bg-warning">Не оплачен</span>
                                @endif
                                
                                @if($item->closed_at)
                                    <span class="badge bg-info">Выполнен</span>
                                    <small class="text-muted">({{ $item->closed_at->format('d.m.Y H:i') }})</small>
                                @else
                                    <span class="badge bg-secondary">В работе</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted fw-bold me-2">Сумма:</span>
                            <span class="h5 mb-0">{{ number_format($item->total, 2, ',', ' ') }} ₽</span>
                        </div>
                    </div>
                    @if($item->notes)
                        <div class="col-12">
                            <div class="d-flex align-items-start mb-2">
                                <span class="text-muted fw-bold me-2">Заметки:</span>
                                <span>{{ $item->notes }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @php
        $services = $item->services()->get();
        $drugs = $item->drugs()->get();
        $labTests = $item->labTests()->get();
        $vaccinations = $item->vaccinations()->get();
    @endphp

    @if($services->count() > 0)
        <!-- Услуги -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear text-primary"></i> Услуги ({{ $services->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach($services as $index => $orderItem)
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="row align-items-center g-2">
                                    <!-- Название услуги -->
                                    <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                        <h6 class="mb-1">
                                            @if($orderItem->item)
                                                {{ $orderItem->item->name }}
                                            @else
                                                <span class="text-muted">Услуга не найдена</span>
                                            @endif
                                        </h6>
                                    </div>
                                    
                                    <!-- Количество и цена -->
                                    <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                        <div class="d-flex justify-content-between w-100 gap-md-1">
                                            <div>
                                                <small class="text-muted d-block">Кол-во</small>
                                                <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">Цена</small>
                                                <span class="fw-bold">{{ number_format($orderItem->unit_price, 2, ',', ' ') }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Сумма -->
                                    <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Сумма</small>
                                                <div class="fw-bold">
                                                    {{ number_format($orderItem->quantity * $orderItem->unit_price, 2, ',', ' ') }} ₽
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Подсумма услуг:</span>
                                <span class="fw-bold">{{ number_format($item->servicesTotal(), 2, ',', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($drugs->count() > 0)
        <!-- Препараты -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-capsule text-success"></i> Препараты ({{ $drugs->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach($drugs as $index => $orderItem)
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="row align-items-center g-2">
                                    <!-- Название препарата -->
                                    <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                        <h6 class="mb-1">
                                            @if($orderItem->item)
                                                {{ $orderItem->item->name }}
                                            @else
                                                <span class="text-muted">Препарат не найден</span>
                                            @endif
                                        </h6>
                                    </div>
                                    
                                    <!-- Количество и цена -->
                                    <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                        <div class="d-flex justify-content-between w-100 gap-md-1">
                                            <div>
                                                <small class="text-muted d-block">Кол-во</small>
                                                <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">Цена</small>
                                                <span class="fw-bold">{{ number_format($orderItem->unit_price, 2, ',', ' ') }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Сумма -->
                                    <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Сумма</small>
                                                <div class="fw-bold">
                                                    {{ number_format($orderItem->quantity * $orderItem->unit_price, 2, ',', ' ') }} ₽
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Подсумма препаратов:</span>
                                <span class="fw-bold">{{ number_format($item->drugsTotal(), 2, ',', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($labTests->count() > 0)
        <!-- Анализы -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-data text-info"></i> Анализы ({{ $labTests->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach($labTests as $index => $orderItem)
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="row align-items-center g-2">
                                    <!-- Название анализа -->
                                    <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                        <h6 class="mb-1">
                                            @if($orderItem->item)
                                                Анализ #{{ $orderItem->item->id }}
                                            @else
                                                <span class="text-muted">Анализ не найден</span>
                                            @endif
                                        </h6>
                                    </div>
                                    
                                    <!-- Количество и цена -->
                                    <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                        <div class="d-flex justify-content-between w-100 gap-md-1">
                                            <div>
                                                <small class="text-muted d-block">Кол-во</small>
                                                <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">Цена</small>
                                                <span class="fw-bold">{{ number_format($orderItem->unit_price, 2, ',', ' ') }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Сумма -->
                                    <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Сумма</small>
                                                <div class="fw-bold">
                                                    {{ number_format($orderItem->quantity * $orderItem->unit_price, 2, ',', ' ') }} ₽
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Подсумма анализов:</span>
                                <span class="fw-bold">{{ number_format($item->labTestsTotal(), 2, ',', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($vaccinations->count() > 0)
        <!-- Вакцинации -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check text-warning"></i> Вакцинации ({{ $vaccinations->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach($vaccinations as $index => $orderItem)
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="row align-items-center g-2">
                                    <!-- Название вакцинации -->
                                    <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                        <h6 class="mb-1">
                                            @if($orderItem->item)
                                                Вакцинация #{{ $orderItem->item->id }}
                                            @else
                                                <span class="text-muted">Вакцинация не найдена</span>
                                            @endif
                                        </h6>
                                    </div>
                                    
                                    <!-- Количество и цена -->
                                    <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                        <div class="d-flex justify-content-between w-100 gap-md-1">
                                            <div>
                                                <small class="text-muted d-block">Кол-во</small>
                                                <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">Цена</small>
                                                <span class="fw-bold">{{ number_format($orderItem->unit_price, 2, ',', ' ') }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Сумма -->
                                    <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="text-end">
                                                <small class="text-muted d-block">Сумма</small>
                                                <div class="fw-bold">
                                                    {{ number_format($orderItem->quantity * $orderItem->unit_price, 2, ',', ' ') }} ₽
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Подсумма вакцинаций:</span>
                                <span class="fw-bold">{{ number_format($item->vaccinationsTotal(), 2, ',', ' ') }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($item->items->count() == 0)
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cart-x display-4 text-muted"></i>
                    <h5 class="mt-3">Состав заказа отсутствует</h5>
                    <p class="text-muted">В данном заказе нет позиций.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Общая сумма -->
    @if($item->items->count() > 0)
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5 mb-0">Общая сумма заказа:</span>
                        <span class="h4 mb-0 fw-bold">{{ number_format($item->total, 2, ',', ' ') }} ₽</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Связанные приемы -->
    @if($item->visits && $item->visits->count() > 0)
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-check"></i> Связанные приемы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($item->visits as $visit)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <a href="{{ route('admin.visits.show', $visit) }}" class="text-decoration-none fw-bold">
                                        Прием от {{ $visit->starts_at->format('d.m.Y H:i') }}
                                    </a>
                                    <div class="small text-muted">
                                        @if($visit->client)
                                            Клиент: {{ $visit->client->name }}
                                        @endif
                                        @if($visit->pet)
                                            | Питомец: {{ $visit->pet->name }}
                                        @endif
                                    </div>
                                    @if($visit->complaints)
                                        <div class="small">
                                            Жалобы: {{ Str::limit($visit->complaints, 100) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @if($visit->status)
                                        <span class="badge" style="background-color: {{ $visit->status->color ?? '#6c757d' }}">
                                            {{ $visit->status->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection 