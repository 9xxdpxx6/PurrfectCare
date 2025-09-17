@extends('layouts.client')

@section('title', 'Детали визита - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-12 col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('client.profile') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person me-2"></i>Профиль
                        </a>
                        <a href="{{ route('client.profile.visits') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check me-2"></i>История визитов
                        </a>
                        <a href="{{ route('client.appointment.appointments') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-plus me-2"></i>Новая запись
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag me-2"></i>Мои заказы
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart me-2"></i>Мои питомцы
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-12 col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">Детали визита</h2>
                <a href="{{ route('client.profile.visits') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>

            <!-- Уведомления -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                @php
                    $fieldErrors = ['reason', 'new_date', 'new_time'];
                    $generalErrors = [];
                    foreach ($errors->keys() as $key) {
                        if (!in_array($key, $fieldErrors)) {
                            $generalErrors = array_merge($generalErrors, $errors->get($key));
                        }
                    }
                @endphp
                @if (!empty($generalErrors))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Ошибка:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($generalErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif

            <!-- Информация о визите -->
            <div class="row">
                <!-- Основная информация -->
                <div class="col-12 col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-event me-2"></i>Информация о визите
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Дата и время</label>
                                    <p class="fw-bold">{{ \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Статус</label>
                                    <p>
                                        <span class="badge 
                                            @if($visit->status->name === 'Запланирован') bg-primary
                                            @elseif($visit->status->name === 'Завершен') bg-success
                                            @elseif($visit->status->name === 'Отменен') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            {{ $visit->status->name }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Ветеринар</label>
                                    <p class="fw-bold">{{ $visit->schedule->veterinarian->name }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Филиал</label>
                                    <p class="fw-bold">{{ $visit->schedule->branch->name }}</p>
                                    <small class="text-muted">{{ $visit->schedule->branch->address }}</small>
                                </div>
                            </div>

                            @if($visit->pet)
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Питомец</label>
                                    <p class="fw-bold">{{ $visit->pet->name }}</p>
                                    @if($visit->pet->breed)
                                        <small class="text-muted">{{ $visit->pet->breed->name }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Пол</label>
                                    <p class="fw-bold">
                                        @if($visit->pet->gender === 'male') Самец
                                        @elseif($visit->pet->gender === 'female') Самка
                                        @else Не указан
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if($visit->complaints)
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Жалобы и симптомы</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-0">{{ $visit->complaints }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Действия -->
                <div class="col-12 col-md-4 mt-4 mt-md-0">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>Действия
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($visit->status->name === 'Запланирован')
                                    @if($visit->starts_at->diffInHours(now()) >= 2)
                                        <form method="POST" action="{{ route('client.profile.visits.cancel', $visit) }}" 
                                              onsubmit="return confirm('Вы уверены, что хотите отменить визит?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                <i class="bi bi-x-circle me-2"></i>Отменить визит
                                            </button>
                                        </form>
                                    @else
                                        <div class="alert alert-warning p-2 mb-0">
                                            <small>
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Отмена возможна не менее чем за 2 часа
                                            </small>
                                        </div>
                                    @endif
                                @elseif($visit->status->name === 'Отменен' || $visit->status->name === 'Завершен')
                                    <form method="POST" action="{{ route('client.profile.visits.reschedule', $visit) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success w-100">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Повторить запись
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Новая запись
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Контактная информация -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-telephone me-2"></i>Контакты
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Телефон филиала:</small><br>
                                <strong>{{ $visit->schedule->branch->phone ?? 'Не указан' }}</strong>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Адрес:</small><br>
                                <strong>{{ $visit->schedule->branch->address }}</strong>
                            </div>
                            @if($visit->schedule->branch->email)
                            <div>
                                <small class="text-muted">Email:</small><br>
                                <strong>{{ $visit->schedule->branch->email }}</strong>
                            </div>
                            @endif
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
.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.card {
    // Убираем hover эффекты для карточек
}

.badge {
    font-size: 0.75rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
}
</style>
@endpush
