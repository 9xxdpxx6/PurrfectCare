@extends('layouts.admin')

@section('title', 'Редактировать клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать клиента</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12">
        <form action="{{ route('admin.users.update', $item) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="row">
                <div class="col-12 col-lg-6 mb-3">
                    <label for="name" class="form-label">Имя *</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $item->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-6 mb-3">
                    <label for="phone" class="form-label">Телефон *</label>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $item->phone) }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-lg-6 mb-3">
                    <label for="address" class="form-label">Адрес</label>
                    <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $item->address) }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">Статистика клиента</h6>
                    <div class="row text-center g-3">
                        <div class="col-12 col-sm-4">
                            <div class="p-2">
                                <div class="h4 text-primary mb-1">{{ $item->pets->count() }}</div>
                                <small class="text-muted">Питомцы</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="p-2">
                                <div class="h4 text-success mb-1">{{ $item->orders->count() }}</div>
                                <small class="text-muted">Заказы</small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="p-2">
                                <div class="h4 text-info mb-1">{{ $item->visits->count() }}</div>
                                <small class="text-muted">Приемы</small>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="text-center text-sm-start">
                        <small class="text-muted">
                            <i class="bi bi-calendar-plus"></i> Зарегистрирован: {{ $item->created_at->format('d.m.Y в H:i') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Связанные данные -->
            <div class="accordion mb-4" id="clientDataAccordion">
                <!-- Питомцы -->
                @if($item->pets->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePets" aria-expanded="false" aria-controls="collapsePets">
                            <i class="bi bi-heart me-2 text-primary"></i>
                            Питомцы ({{ $item->pets->count() }})
                        </button>
                    </h2>
                    <div id="collapsePets" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Всего питомцев: {{ $item->pets->count() }}</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.pets.index', ['owner' => $item->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list"></i> Все питомцы
                                    </a>
                                    <a href="{{ route('admin.pets.create', ['client_id' => $item->id]) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-plus"></i> Новый питомец
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                @foreach($item->pets as $pet)
                                <div class="col-12 col-lg-6">
                                    <div class="card h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <a href="{{ route('admin.pets.edit', $pet->id) }}" class="text-decoration-none">
                                                        {{ $pet->name }}
                                                    </a>
                                                </h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <a href="{{ route('admin.pets.edit', $pet->id) }}" class="btn btn-outline-warning btn-sm" title="Редактировать">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            @if($pet->breed && $pet->breed->species)
                                            <h6 class="card-subtitle mb-2 text-muted small">
                                                {{ $pet->breed->species->name }} - {{ $pet->breed->name }}
                                            </h6>
                                            @endif
                                            
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                @if($pet->birth_date)
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($pet->birth_date)->format('d.m.Y') }}
                                                </small>
                                                @endif
                                                
                                                <small class="text-muted">
                                                    <i class="bi bi-gender-{{ $pet->gender === 'male' ? 'male' : 'female' }}"></i>
                                                    @if($pet->gender === 'male')
                                                        Самец
                                                    @elseif($pet->gender === 'female')
                                                        Самка
                                                    @else
                                                        Неизвестно
                                                    @endif
                                                </small>
                                            </div>
                                            
                                            <small class="text-muted">
                                                <i class="bi bi-plus-circle"></i> Добавлен: {{ $pet->created_at->format('d.m.Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Заказы -->
                @if($item->orders->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrders" aria-expanded="false" aria-controls="collapseOrders">
                            <i class="bi bi-cart me-2 text-success"></i>
                            Заказы ({{ $item->orders->count() }})
                        </button>
                    </h2>
                    <div id="collapseOrders" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Всего заказов: {{ $item->orders->count() }}</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.orders.index', ['client_id' => $item->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list"></i> Все заказы
                                    </a>
                                    <a href="{{ route('admin.orders.create', ['client_id' => $item->id]) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-plus"></i> Новый заказ
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                @foreach($item->orders->take(10) as $order)
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="text-decoration-none">
                                                        Заказ #{{ $order->id }}
                                                    </a>
                                                </h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    @if(isset($order->status))
                                                    <span class="badge" style="background-color: {{ $order->status->color }};">
                                                        @switch($order->status)
                                                            @case('completed')
                                                                Выполнен
                                                                @break
                                                            @case('pending')
                                                                В обработке
                                                                @break
                                                            @case('cancelled')
                                                                Отменен
                                                                @break
                                                            @default
                                                                Новый
                                                        @endswitch
                                                    </span>
                                                    @endif
                                                    <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-outline-warning btn-sm" title="Редактировать">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> {{ $order->created_at->format('d.m.Y H:i') }}
                                                    </small>
                                                </div>
                                                @if(isset($order->total_price) && $order->total_price)
                                                <div class="col-sm-6 text-sm-end">
                                                    <small class="fw-bold text-success">{{ number_format($order->total_price, 0, '.', ' ') }} ₽</small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if($item->orders->count() > 10)
                                <div class="col-12">
                                    <div class="text-center">
                                        <small class="text-muted">... и ещё {{ $item->orders->count() - 10 }} заказов</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Визиты -->
                @if($item->visits->count() > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVisits" aria-expanded="false" aria-controls="collapseVisits">
                            <i class="bi bi-calendar-check me-2 text-info"></i>
                            Приемы ({{ $item->visits->count() }})
                        </button>
                    </h2>
                    <div id="collapseVisits" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Всего приемов: {{ $item->visits->count() }}</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.visits.index', ['client_id' => $item->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list"></i> Все приемы
                                    </a>
                                    <a href="{{ route('admin.visits.create', ['client_id' => $item->id]) }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-plus"></i> Новый прием
                                    </a>
                                </div>
                            </div>
                            <div class="row g-3">
                                @foreach($item->visits->take(10) as $visit)
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <a href="{{ route('admin.visits.edit', $visit->id) }}" class="text-decoration-none">
                                                        Приём #{{ $visit->id }}
                                                    </a>
                                                </h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <a href="{{ route('admin.visits.edit', $visit->id) }}" class="btn btn-outline-warning btn-sm" title="Редактировать">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    @if(isset($visit->status))
                                                    <span class="badge bg-{{ $visit->status === 'completed' ? 'success' : ($visit->status === 'scheduled' ? 'primary' : 'secondary') }}">
                                                        @switch($visit->status)
                                                            @case('completed')
                                                                Завершен
                                                                @break
                                                            @case('scheduled')
                                                                Запланирован
                                                                @break
                                                            @case('cancelled')
                                                                Отменен
                                                                @break
                                                            @default
                                                                Новый
                                                        @endswitch
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> 
                                                        @if(isset($visit->visit_date) && $visit->visit_date)
                                                            {{ \Carbon\Carbon::parse($visit->visit_date)->format('d.m.Y H:i') }}
                                                        @else
                                                            {{ $visit->created_at->format('d.m.Y H:i') }}
                                                        @endif
                                                    </small>
                                                </div>
                                                @if(isset($visit->pet) && $visit->pet)
                                                <div class="col-sm-6 text-sm-end">
                                                    <small class="text-muted">
                                                        <i class="bi bi-heart"></i> {{ $visit->pet->name }}
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                            @if(isset($visit->notes) && $visit->notes)
                                            <div class="mt-2">
                                                <small class="text-muted">{{ Str::limit($visit->notes, 100) }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if($item->visits->count() > 10)
                                <div class="col-12">
                                    <div class="text-center">
                                        <small class="text-muted">... и ещё {{ $item->visits->count() - 10 }} приемов</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Кнопки действий -->
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                <!-- Левая группа - Отмена -->
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary order-sm-first">
                    <span class="d-inline">Отмена</span>
                </a>
                
                <!-- Правая группа - Сброс пароля и Сохранить -->
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <a href="{{ route('admin.users.resetPassword', $item) }}" class="btn btn-outline-warning"
                        onclick="return confirm('Сбросить пароль для клиента {{ $item->name }}?');">
                        <i class="bi bi-key"></i> <span class="d-inline">Сбросить пароль</span>
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 