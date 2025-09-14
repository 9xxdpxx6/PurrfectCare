@extends('layouts.client')

@section('title', 'Личный кабинет - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('client.profile') }}" class="list-group-item list-group-item-action active">
                            <i class="bi bi-person me-2"></i>Профиль
                        </a>
                        <a href="{{ route('client.profile.visits') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check me-2"></i>История визитов
                        </a>
                        <a href="{{ route('client.appointment.appointments') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-plus me-2"></i>Новая запись
                        </a>
                        <a href="{{ route('client.profile.orders') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag me-2"></i>История заказов
                        </a>
                        <a href="{{ route('client.profile.pets') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart me-2"></i>Мои питомцы
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">Личный кабинет</h2>
                <span class="badge bg-primary">Клиент</span>
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
                    $fieldErrors = ['name', 'email', 'phone', 'address', 'current_password', 'password', 'password_confirmation'];
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
                        <ul class="mb-0">
                            @foreach ($generalErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif

            <!-- Информация о профиле -->
            <div class="row">
                <!-- Основная информация -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person me-2"></i>Основная информация
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('client.profile.update') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Имя</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $user->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Телефон</label>
                                        <input type="text" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Адрес</label>
                                        <input type="text" 
                                               class="form-control @error('address') is-invalid @enderror" 
                                               id="address" 
                                               name="address" 
                                               value="{{ old('address', $user->address) }}">
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Сохранить изменения
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Боковая панель -->
                <div class="col-md-4">
                    <!-- Смена пароля -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-shield-lock me-2"></i>Смена пароля
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('client.profile.password') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Текущий пароль</label>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Новый пароль</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required>
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-key me-2"></i>Изменить пароль
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Статистика -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>Статистика
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Записей на прием:</span>
                                <span class="fw-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Заказов:</span>
                                <span class="fw-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Питомцев:</span>
                                <span class="fw-bold">0</span>
                            </div>
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

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush
