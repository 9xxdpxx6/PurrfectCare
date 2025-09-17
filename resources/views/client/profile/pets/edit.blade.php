@extends('layouts.client')

@section('title', 'Редактировать питомца - PurrfectCare')

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
                        <a href="{{ route('client.profile.orders') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-bag me-2"></i>История заказов
                        </a>
                        <a href="{{ route('client.appointment.appointments') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-plus me-2"></i>Новая запись
                        </a>
                        <a href="{{ route('client.profile.pets') }}" class="list-group-item list-group-item-action active">
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
                <h2 class="h3 mb-0">Редактировать питомца</h2>
                <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>

            <!-- Форма редактирования питомца -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('client.profile.pets.update', $pet) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Основная информация -->
                            <div class="col-12">
                                <h5 class="mb-3">Основная информация</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя питомца *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $pet->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="breed_id" class="form-label">Порода *</label>
                                    <select class="form-select @error('breed_id') is-invalid @enderror" 
                                            id="breed_id" 
                                            name="breed_id" 
                                            required
                                            data-tomselect
                                            data-placeholder="Выберите породу">
                                        <option value="">Выберите породу</option>
                                        @foreach($breeds as $breed)
                                            <option value="{{ $breed->id }}" 
                                                    {{ old('breed_id', $pet->breed_id) == $breed->id ? 'selected' : '' }}>
                                                {{ $breed->name }} ({{ $breed->species->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('breed_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="birthdate" class="form-label">Дата рождения *</label>
                                    <input type="text" 
                                           class="form-control @error('birthdate') is-invalid @enderror" 
                                           id="birthdate" 
                                           name="birthdate" 
                                           value="{{ old('birthdate', $pet->birthdate?->format('d.m.Y')) }}" 
                                           placeholder="дд.мм.гггг"
                                           data-datepicker
                                           readonly
                                           required>
                                    @error('birthdate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Пол *</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender" 
                                            required
                                            data-tomselect
                                            data-placeholder="Выберите пол">
                                        <option value="">Выберите пол</option>
                                        <option value="male" {{ old('gender', $pet->gender) == 'male' ? 'selected' : '' }}>Самец</option>
                                        <option value="female" {{ old('gender', $pet->gender) == 'female' ? 'selected' : '' }}>Самка</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <!-- Кнопки -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                                        Отмена
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Сохранить изменения
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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

.form-label {
    font-weight: 500;
}
</style>
@endpush
