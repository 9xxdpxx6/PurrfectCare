@extends('layouts.admin')

@section('title', 'Клиент: ' . $user->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Клиент: {{ $user->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('clients.update')
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        @endcan
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-person"></i> {{ $user->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-envelope"></i> Email:</strong>
                            {{ $user->email }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-telephone"></i> Телефон:</strong>
                            <span class="d-inline-flex align-items-center gap-2 ms-2">
                                {{ preg_replace('/^(\+?7|8)(\d{3})(\d{3})(\d{2})(\d{2})$/', '+7-$2-$3-$4-$5', $user->phone) }}
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="copyToClipboard('{{ $user->phone }}')" data-bs-toggle="tooltip" data-bs-placement="top" title="Копировать номер">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        </p>
                        @if($user->telegram)
                        <p class="mb-2">
                            <strong><i class="bi bi-telegram"></i> Telegram ID:</strong>
                            @if(is_numeric($user->telegram))
                                <span class="d-inline-flex align-items-center gap-2 ms-2">
                                    <a href="https://web.telegram.org/k/#{{ $user->telegram }}" target="_blank" class="text-decoration-none">
                                        {{ $user->telegram }}
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="copyToClipboard('{{ $user->telegram }}')" data-bs-toggle="tooltip" data-bs-placement="top" title="Копировать ID">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </span>
                            @else
                                <a href="https://t.me/{{ ltrim($user->telegram, '@') }}" target="_blank" class="text-decoration-none ms-2">
                                    {{ $user->telegram }}
                                    <i class="bi bi-box-arrow-up-right text-muted small"></i>
                                </a>
                            @endif
                        </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-geo-alt"></i> Адрес:</strong>
                            {{ $user->address }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Дата регистрации:</strong>
                            {{ $user->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Аккордеон активности -->
        <div class="accordion mb-4" id="activityAccordion">
            <!-- Приёмы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#visitsCollapse" aria-expanded="true" aria-controls="visitsCollapse">
                        <i class="bi bi-calendar-check me-2"></i> Приёмы ({{ $visitsTotal }})
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
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="mb-1">Приём #{{ $visit->id }}</h6>
                                                        <p class="text-muted small mb-2">
                                                            @if($visit->schedule && $visit->schedule->shift_starts_at)
                                                                {{ $visit->schedule->shift_starts_at->format('d.m.Y H:i') }}
                                                            @else
                                                                {{ $visit->created_at->format('d.m.Y H:i') }}
                                                            @endif
                                                        </p>
                                                        
                                                        @if($visit->pet)
                                                            <p class="text-muted small mb-0">
                                                                <i class="bi bi-heart"></i> {{ $visit->pet->name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="col-md-3">
                                                        @if($visit->schedule && $visit->schedule->employee)
                                                            <p class="text-muted small mb-0">
                                                                <i class="bi bi-person"></i> {{ $visit->schedule->employee->name }}
                                                                @if($visit->schedule->employee->specialties->count() > 0)
                                                                    <span class="text-muted">({{ $visit->schedule->employee->specialties->first()->name }})</span>
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="col-md-3 text-md-center">
                                                        @if($visit->status)
                                                            <div class="d-flex justify-content-center">
                                                                <span class="badge" style="background-color: {!! $visit->status->color !!}; color: white;">
                                                                    {{ $visit->status->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="align-self-center d-none d-md-block ms-3">
                                                <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Кнопка на всю ширину для маленьких экранов -->
                                        <div class="d-md-none mt-2">
                                            <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-eye"></i> Подробнее
                                            </a>
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

            <!-- Питомцы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#petsCollapse" aria-expanded="false" aria-controls="petsCollapse">
                        <i class="bi bi-heart me-2"></i> Питомцы ({{ $petsTotal }})
                    </button>
                </h2>
                <div id="petsCollapse" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        @if($pets->count() > 0)
                            <div class="d-flex flex-column gap-3">
                                @foreach($pets as $pet)
                                    <div class="border rounded p-3 bg-body-tertiary">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <h6 class="mb-0">{{ $pet->name }}</h6>
                                                            @if($pet->gender === 'male')
                                                                <i class="bi bi-gender-male text-muted"></i>
                                                            @elseif($pet->gender === 'female')
                                                                <i class="bi bi-gender-female text-muted"></i>
                                                            @else
                                                                <i class="bi bi-gender-ambiguous text-muted"></i>
                                                            @endif
                                                        </div>
                                                        <p class="text-muted small mb-2">
                                                            {{ $pet->birthdate ? $pet->birthdate->format('d.m.Y') : '—' }}
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="col-md-6 text-md-end">
                                                        <p class="text-muted small mb-0">
                                                            {{ $pet->breed->name ?? '—' }}
                                                            @if($pet->breed && $pet->breed->species)
                                                                <span class="text-muted">({{ $pet->breed->species->name }})</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="align-self-center d-none d-md-block ms-3">
                                                <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Кнопка на всю ширину для маленьких экранов -->
                                        <div class="d-md-none mt-2">
                                            <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-eye"></i> Подробнее
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Питомцы не найдены</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Заказы -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ordersCollapse" aria-expanded="false" aria-controls="ordersCollapse">
                        <i class="bi bi-bag me-2"></i> Заказы ({{ $ordersTotal }})
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
                                                <div class="row align-items-center">
                                                    <div class="col-md-5">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <h6 class="mb-0">Заказ #{{ $order->id }}</h6>
                                                            @if($order->is_paid)
                                                                <i class="bi bi-check-all text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Оплачен"></i>
                                                            @else
                                                                <i class="bi bi-cash text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Не оплачен"></i>
                                                            @endif
                                                        </div>
                                                        <p class="text-muted small mb-0">{{ $order->created_at->format('d.m.Y') }}</p>
                                                        
                                                        @if($order->branch)
                                                            <p class="text-muted small mb-0">
                                                                <i class="bi bi-building"></i> {{ $order->branch->name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="col-md-4 text-md-center">
                                                        @if($order->status)
                                                            <div class="d-flex justify-content-center">
                                                                <span class="badge" style="background-color: {!! $order->status->color !!}; color: white;">
                                                                    {{ $order->status->name }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="col-md-3 text-md-end">
                                                        @if($order->total)
                                                            <div>
                                                                <strong>{{ number_format($order->total, 2, ',', ' ') }} ₽</strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="align-self-center d-none d-md-block ms-3">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Подробнее</span>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Кнопка на всю ширину для маленьких экранов -->
                                        <div class="d-md-none mt-2">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-eye"></i> Подробнее
                                            </a>
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
                    <span>Всего питомцев:</span>
                    <strong>{{ $petsTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего заказов:</span>
                    <strong>{{ $ordersTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего приёмов:</span>
                    <strong>{{ $visitsTotal }}</strong>
                </div>
                @if($orders->count() > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Общая сумма заказов:</span>
                        <strong>{{ number_format($orders->sum('total'), 2, ',', ' ') }} ₽</strong>
                    </div>
                @endif
                @if($visits->count() > 0 || $orders->count() > 0)
                    <hr>
                @endif
                @if($visits->count() > 0)
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
                    <a href="{{ route('admin.visits.create', ['client' => $user->id]) }}" class="btn btn-outline-info">
                        <i class="bi bi-calendar-plus"></i> Записать на приём
                    </a>
                    <a href="{{ route('admin.pets.create', ['owner' => $user->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Добавить питомца
                    </a>
                    <a href="{{ route('admin.orders.create', ['client' => $user->id]) }}" class="btn btn-outline-success">
                        <i class="bi bi-bag-plus"></i> Добавить заказ
                    </a>
                    
                    @if($pets->count() > 0)
                        <hr>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-flask"></i> Добавить анализ
                            </button>
                            <ul class="dropdown-menu">
                                @foreach($pets as $pet)
                                    <li><a class="dropdown-item" href="{{ route('admin.lab-tests.create', ['pet' => $pet->id]) }}">
                                        {{ $pet->name }}
                                    </a></li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-droplet"></i> Добавить вакцинацию
                            </button>
                            <ul class="dropdown-menu">
                                @foreach($pets as $pet)
                                    <li><a class="dropdown-item" href="{{ route('admin.vaccinations.create', ['pet' => $pet->id]) }}">
                                        {{ $pet->name }}
                                    </a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <hr>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <a href="{{ route('admin.users.resetPassword', $user) }}" class="btn btn-outline-primary" onclick="return confirm('Вы уверены, что хотите сбросить пароль для клиента {{ $user->name }}? Новый пароль будет отправлен на email: {{ $user->email }}')">
                        <i class="bi bi-key"></i> Сбросить пароль
                    </a>
                    <hr>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить клиента {{ $user->name }}? Это действие нельзя отменить.')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Определяем, что копируем
            let message = '';
            // Проверяем, является ли текст номером телефона (начинается с +7 или 8 и содержит только цифры)
            if (/^(\+?7|8)\d{10}$/.test(text)) {
                message = 'Номер телефона скопирован в буфер обмена!';
            } else {
                message = 'ID Telegram скопирован в буфер обмена!';
            }
            showAlert(message, 'success');
        }, function(err) {
            console.error('Ошибка при копировании: ', err);
            showAlert('Ошибка при копировании', 'danger');
        });
        }
    
    function showAlert(message, type) {
        // Удаляем существующие алерты
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Создаем новый алерт
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Автоматически скрываем через 3 секунды
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
</script>
@endpush 