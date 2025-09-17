@extends('layouts.client')

@section('title', 'Мои питомцы - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="pets" />

        <!-- Основной контент -->
        <div class="col-12 col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                <h2 class="h3 mb-3 mb-sm-0">Мои питомцы</h2>
                <a href="{{ route('client.profile.pets.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Добавить питомца
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Ошибка:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Поиск -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('client.profile.pets') }}">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="search" class="form-label">Поиск</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Имя питомца..." value="{{ request('search') }}">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="breed" class="form-label">Порода</label>
                                <select class="form-select" id="breed" name="breed" data-tomselect data-placeholder="Все породы">
                                    <option value="">Все породы</option>
                                    @foreach($breeds as $breed)
                                        <option value="{{ $breed->id }}" 
                                                {{ request('breed') == $breed->id ? 'selected' : '' }}>
                                            {{ $breed->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="gender" class="form-label">Пол</label>
                                <select class="form-select" id="gender" name="gender" data-tomselect data-placeholder="Все">
                                    <option value="">Все</option>
                                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Самец</option>
                                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Самка</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-search me-1"></i>Найти
                                </button>
                                <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Сбросить
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список питомцев -->
            @if($pets->count() > 0)
                @foreach($pets as $pet)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="card-title mb-0 me-3">
                                        {{ $pet->name }}
                                    </h5>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-heart text-muted"></i>
                                    </div>
                                </div>
                                
                                <div class="row text-muted small">
                                    <div class="col-md-6">
                                        <i class="bi bi-tag me-1"></i>
                                        <strong>Порода:</strong> {{ $pet->breed->name ?? 'Не указана' }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-calendar me-1"></i>
                                        <strong>Возраст:</strong> {{ $pet->birthdate ? $pet->birthdate->age . ' ' . (in_array($pet->birthdate->age % 100, [11, 12, 13, 14]) ? 'лет' : ($pet->birthdate->age % 10 == 1 ? 'год' : ($pet->birthdate->age % 10 >= 2 && $pet->birthdate->age % 10 <= 4 ? 'года' : 'лет'))) : 'Не указан' }}
                                    </div>
                                </div>
                                
                                <div class="row text-muted small mt-1">
                                    <div class="col-md-6">
                                        <i class="bi bi-gender-ambiguous me-1"></i>
                                        <strong>Пол:</strong> 
                                        @if($pet->gender === 'male') Самец
                                        @elseif($pet->gender === 'female') Самка
                                        @else Не указан
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        <strong>Добавлен:</strong> {{ $pet->created_at->format('d.m.Y') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('client.profile.pets.edit', $pet) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil me-1"></i>Редактировать
                                    </a>
                                    
                                    <form method="POST" action="{{ route('client.profile.pets.destroy', $pet) }}" 
                                          class="delete-pet-form d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="bi bi-trash me-1"></i>Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Пагинация -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $pets->links() }}
                </div>
            @else
                <div class="card border-0 bg-light">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-heart display-1 text-muted mb-4"></i>
                        <h3 class="h4 mb-3">Нет питомцев</h3>
                        <p class="text-muted mb-4">
                            У вас пока нет зарегистрированных питомцев. Добавьте первого питомца, чтобы записывать его на прием!
                        </p>
                        <a href="{{ route('client.profile.pets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Добавить питомца
                        </a>
                    </div>
                </div>
            @endif
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

.pet-photo {
    width: 80px;
    height: 80px;
    object-fit: cover;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка форм удаления питомцев
    const deleteForms = document.querySelectorAll('.delete-pet-form');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            if (confirm('Вы уверены, что хотите удалить питомца? Это действие нельзя отменить.')) {
                // Показываем состояние загрузки
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Загрузка...';
                
                // Отправляем форму
                this.submit();
            } else {
                // Если пользователь отменил, сбрасываем состояние кнопки
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });

    // Инициализация TomSelect и AirDatepicker
    if (typeof window.createTomSelect === 'function') {
        const tomSelectElements = document.querySelectorAll('[data-tomselect]');
        tomSelectElements.forEach(element => {
            const placeholder = element.dataset.placeholder || 'Выберите значение...';
            window.createTomSelect(element, {
                placeholder: placeholder,
            });
        });
    }

    if (typeof window.createDatepicker === 'function') {
        const datepickerElements = document.querySelectorAll('[data-datepicker]');
        datepickerElements.forEach(element => {
            window.createDatepicker(element);
        });
    }
});
</script>
@endpush
