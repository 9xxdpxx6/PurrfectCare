@extends('layouts.admin')

@section('title', 'Питомец: ' . $pet->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 col-12 col-md-7 col-xl-8">Питомец: {{ $pet->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.pets.edit', $pet) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-paw"></i> {{ $pet->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-person"></i> Владелец:</strong>
                        @if($pet->client)
                                <a href="{{ route('admin.users.show', $pet->client) }}" class="text-decoration-none">{{ $pet->client->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-tags"></i> Порода:</strong>
                            {{ $pet->breed->name ?? '—' }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-collection"></i> Вид:</strong>
                            {{ $pet->breed->species->name ?? '—' }}
                        </p>
                        <p class="mb-2">
                            <strong>
                                @if($pet->gender === 'male')
                                    <i class="bi bi-gender-male"></i>
                                @elseif($pet->gender === 'female')
                                    <i class="bi bi-gender-female"></i>
                                @else
                                    <i class="bi bi-gender-ambiguous"></i>
                                @endif
                                Пол:
                            </strong>
                            @if($pet->gender === 'male')
                                Самец
                            @elseif($pet->gender === 'female')
                                Самка
                            @else
                                Неизвестно
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar"></i> Дата рождения:</strong>
                            {{ $pet->birthdate ? $pet->birthdate->format('d.m.Y') : '—' }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-speedometer2"></i> Вес:</strong>
                            {{ $pet->weight ? $pet->weight . ' кг' : '—' }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-thermometer"></i> Температура:</strong>
                            {{ $pet->temperature ? $pet->temperature . ' °C' : '—' }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Добавлен:</strong>
                            {{ $pet->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Аккордеон активности -->
        <div class="accordion" id="activityAccordion">
            <!-- Приёмы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#visitsCollapse" aria-expanded="true" aria-controls="visitsCollapse">
                        <i class="bi bi-calendar-check me-2"></i> Последние приёмы ({{ $visitsTotal }})
                    </button>
                </h2>
                <div id="visitsCollapse" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        @if($visits->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($visits as $visit)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Приём #{{ $visit->id }}</h6>
                                                <p class="text-muted small mb-2">{{ $visit->created_at->format('d.m.Y') }}</p>
                                                
                                                @if($visit->schedule && $visit->schedule->employee)
                                                    <p class="text-muted small mb-0">
                                                        Ветеринар: {{ $visit->schedule->employee->name }}
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="align-self-center">
                                                <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Приёмы не найдены</p>
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
                                                <h6 class="mb-1">Вакцинация #{{ $vaccination->id }}</h6>
                                                <p class="text-muted small mb-2">{{ $vaccination->administered_at->format('d.m.Y') }}</p>
                                                
                                                @if($vaccination->veterinarian)
                                                    <p class="text-muted small mb-2">
                                                        Ветеринар: {{ $vaccination->veterinarian->name }}
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="align-self-center">
                                                <a href="{{ route('admin.vaccinations.show', $vaccination) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
                                                </a>
                                            </div>
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
                        <i class="bi bi-clipboard-pulse me-2"></i> Последние анализы ({{ $labTestsTotal }})
                    </button>
                </h2>
                <div id="labTestsCollapse" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @if($labTests->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($labTests as $labTest)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Анализ #{{ $labTest->id }}</h6>
                                                <p class="text-muted small mb-2">{{ $labTest->created_at->format('d.m.Y') }}</p>
                                                
                                                @if($labTest->veterinarian)
                                                    <p class="text-muted small mb-2">
                                                        Ветеринар: {{ $labTest->veterinarian->name }}
                                                    </p>
                                                @endif
                                                
                                                <div>
                                                    <small class="text-muted">Тип:</small>
                                                    <span class="fw-bold">{{ $labTest->labTestType->name ?? 'Не указан' }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="align-self-center">
                                                <a href="{{ route('admin.lab-tests.show', $labTest) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
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

            <!-- Заказы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersCollapse" aria-expanded="false" aria-controls="ordersCollapse">
                        <i class="bi bi-bag me-2"></i> Последние заказы ({{ $ordersTotal }})
                    </button>
                </h2>
                <div id="ordersCollapse" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @if($orders->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($orders as $order)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">Заказ #{{ $order->id }}</h6>
                                                <p class="text-muted small mb-2">{{ $order->created_at->format('d.m.Y') }}</p>
                                                
                                                @if($order->total_amount)
                                                    <div>
                                                        <span class="badge bg-success">
                                                            {{ number_format($order->total_amount, 2, ',', ' ') }} ₽
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="align-self-center">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
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
                    <span>Всего приёмов:</span>
                    <strong>{{ $visitsTotal }}</strong>
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
                    <span>Всего заказов:</span>
                    <strong>{{ $ordersTotal }}</strong>
                </div>
                @if($orders->count() > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Общая сумма заказов:</span>
                        <strong>{{ number_format($orders->sum('total_amount'), 2, ',', ' ') }} ₽</strong>
            </div>
                @endif
                                @if($pet->birthdate)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Возраст:</span>
                        <strong>{{ $pet->birthdate->age }} 
                            @php
                                $age = $pet->birthdate->age;
                                $lastDigit = $age % 10;
                                $lastTwoDigits = $age % 100;
                                
                                if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
                                    echo 'лет';
                                } elseif ($lastDigit == 1) {
                                    echo 'год';
                                } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
                                    echo 'года';
                                } else {
                                    echo 'лет';
                                }
                            @endphp
                        </strong>
        </div>
                @endif
                                @if($visits->count() > 0)
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Последний приём:</span>
                        <strong>{{ $visits->first()->created_at->format('d.m.Y') }}</strong>
    </div>
                @endif
                @if($orders->count() > 0)
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
                    <a href="{{ route('admin.visits.create', ['pet' => $pet->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus"></i> Записать на приём
                    </a>
                    <a href="{{ route('admin.orders.create', ['pet' => $pet->id]) }}" class="btn btn-outline-success">
                        <i class="bi bi-plus"></i> Добавить заказ
                    </a>
                    <a href="{{ route('admin.lab-tests.create', ['pet' => $pet->id]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-plus"></i> Добавить анализ
                    </a>
                    <a href="{{ route('admin.vaccinations.create', ['pet' => $pet->id]) }}" class="btn btn-outline-info">
                        <i class="bi bi-plus"></i> Добавить вакцинацию
                    </a>
                    <hr>
                    <a href="{{ route('admin.pets.edit', $pet) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <hr>
                    <form action="{{ route('admin.pets.destroy', $pet) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить питомца?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 