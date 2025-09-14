@extends('layouts.client')

@section('title', 'Добавить питомца - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm profile-sidebar">
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
        <div class="col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">Добавить питомца</h2>
                <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>

            <!-- Форма добавления питомца -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('client.profile.pets.store') }}" enctype="multipart/form-data" data-validate>
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Основная информация -->
                            <div class="col-12 col-lg-6">
                                <h5 class="mb-3">Основная информация</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя питомца *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="species_id" class="form-label">Вид *</label>
                                    <select class="form-select @error('species_id') is-invalid @enderror" 
                                            id="species_id" 
                                            name="species_id" 
                                            required
                                            data-old-species-id="{{ old('species_id') }}">
                                        <option value="">Выберите вид</option>
                                        @foreach($species as $specie)
                                            <option value="{{ $specie->id }}" 
                                                    {{ old('species_id') == $specie->id ? 'selected' : '' }}>
                                                {{ $specie->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('species_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="breed_id" class="form-label">Порода *</label>
                                    <select class="form-select @error('breed_id') is-invalid @enderror" 
                                            id="breed_id" 
                                            name="breed_id" 
                                            required
                                            data-old-breed-id="{{ old('breed_id') }}">
                                        <option value="">Сначала выберите вид</option>
                                    </select>
                                    @error('breed_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="birthdate" class="form-label">Дата рождения *</label>
                                    <input type="date" 
                                           class="form-control @error('birthdate') is-invalid @enderror" 
                                           id="birthdate" 
                                           name="birthdate" 
                                           value="{{ old('birthdate') }}" 
                                           max="{{ date('Y-m-d') }}" 
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
                                            required>
                                        <option value="">Выберите пол</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Самец</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Самка</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Дополнительная информация -->
                            <div class="col-12 col-lg-6">
                                <h5 class="mb-3">Дополнительная информация</h5>
                                
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Вес (кг)</label>
                                    <input type="number" 
                                           class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" 
                                           name="weight" 
                                           value="{{ old('weight') }}" 
                                           step="0.1" 
                                           min="0.1" 
                                           max="999.9">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="color" class="form-label">Окрас</label>
                                    <input type="text" 
                                           class="form-control @error('color') is-invalid @enderror" 
                                           id="color" 
                                           name="color" 
                                           value="{{ old('color') }}" 
                                           placeholder="Например: рыжий, черно-белый">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="photo" class="form-label">Фото питомца</label>
                                    <input type="file" 
                                           class="form-control @error('photo') is-invalid @enderror" 
                                           id="photo" 
                                           name="photo" 
                                           accept="image/*">
                                    <div class="form-text">Максимальный размер файла: 2MB. Поддерживаемые форматы: JPEG, PNG, JPG, GIF</div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Дополнительная информация о питомце...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                    <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                                        Отмена
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Добавить питомца
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

