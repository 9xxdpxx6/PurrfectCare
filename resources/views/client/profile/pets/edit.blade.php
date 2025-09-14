@extends('layouts.client')

@section('title', 'Редактировать питомца - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-lg-3 mb-4">
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
        <div class="col-lg-9">
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
                    <form method="POST" action="{{ route('client.profile.pets.update', $pet) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Основная информация -->
                            <div class="col-md-6">
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
                                    <label for="species_id" class="form-label">Вид *</label>
                                    <select class="form-select @error('species_id') is-invalid @enderror" 
                                            id="species_id" 
                                            name="species_id" 
                                            required>
                                        <option value="">Выберите вид</option>
                                        @foreach($species as $specie)
                                            <option value="{{ $specie->id }}" 
                                                    {{ old('species_id', $pet->species_id) == $specie->id ? 'selected' : '' }}>
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
                                            required>
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
                                           value="{{ old('birthdate', $pet->birthdate?->format('Y-m-d')) }}" 
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
                                        <option value="male" {{ old('gender', $pet->gender) == 'male' ? 'selected' : '' }}>Самец</option>
                                        <option value="female" {{ old('gender', $pet->gender) == 'female' ? 'selected' : '' }}>Самка</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Дополнительная информация -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Дополнительная информация</h5>
                                
                                <!-- Текущее фото -->
                                @if($pet->photo)
                                <div class="mb-3">
                                    <label class="form-label">Текущее фото</label>
                                    <div class="text-center">
                                        <img src="{{ Storage::url($pet->photo) }}" 
                                             alt="{{ $pet->name }}" 
                                             class="rounded" 
                                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Вес (кг)</label>
                                    <input type="number" 
                                           class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" 
                                           name="weight" 
                                           value="{{ old('weight', $pet->weight) }}" 
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
                                           value="{{ old('color', $pet->color) }}" 
                                           placeholder="Например: рыжий, черно-белый">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="photo" class="form-label">Новое фото</label>
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
                                              placeholder="Дополнительная информация о питомце...">{{ old('description', $pet->description) }}</textarea>
                                    @error('description')
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const speciesSelect = document.getElementById('species_id');
    const breedSelect = document.getElementById('breed_id');
    const currentBreedId = {{ $pet->breed_id }};
    const currentSpeciesId = {{ $pet->species_id }};
    
    // Загружаем породы при изменении вида
    speciesSelect.addEventListener('change', function() {
        const speciesId = this.value;
        breedSelect.innerHTML = '<option value="">Загрузка...</option>';
        
        if (speciesId) {
            fetch(`/api/breeds-by-species?species_id=${speciesId}`)
                .then(response => response.json())
                .then(breeds => {
                    breedSelect.innerHTML = '<option value="">Выберите породу</option>';
                    breeds.forEach(breed => {
                        const option = document.createElement('option');
                        option.value = breed.id;
                        option.textContent = breed.name;
                        if (breed.id == currentBreedId) {
                            option.selected = true;
                        }
                        breedSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Ошибка загрузки пород:', error);
                    breedSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                });
        } else {
            breedSelect.innerHTML = '<option value="">Сначала выберите вид</option>';
        }
    });
    
    // Загружаем породы для текущего вида при загрузке страницы
    if (currentSpeciesId) {
        speciesSelect.value = currentSpeciesId;
        speciesSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

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
