@extends('layouts.client')

@section('title', 'Мои питомцы - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="pets" />

        <!-- Основной контент -->
        <div class="col-lg-9">
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
                    <ul class="mb-0">
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
                            <div class="col-md-6">
                                <label for="search" class="form-label">Поиск</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Имя питомца..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-search me-1"></i>Найти
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('client.profile.pets') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x me-1"></i>Сбросить
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список питомцев -->
            @if($pets->count() > 0)
                <div class="row">
                    @foreach($pets as $pet)
                    <div class="col-12 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <!-- Фото питомца -->
                                    <div class="col-auto">
                                        @if($pet->photo)
                                            <img src="{{ Storage::url($pet->photo) }}" 
                                                 alt="{{ $pet->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-heart text-muted" style="font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Информация о питомце -->
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5 class="card-title mb-2">{{ $pet->name }}</h5>
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-tag me-1"></i>
                                                        <strong>Порода:</strong> {{ $pet->breed->name ?? 'Не указана' }}
                                                    </small>
                                                </div>
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <strong>Возраст:</strong> {{ $pet->birthdate ? $pet->birthdate->age : 'Не указан' }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-gender-ambiguous me-1"></i>
                                                        <strong>Пол:</strong> 
                                                        @if($pet->gender === 'male') Самец
                                                        @elseif($pet->gender === 'female') Самка
                                                        @else Не указан
                                                        @endif
                                                    </small>
                                                </div>
                                                @if($pet->weight)
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-speedometer me-1"></i>
                                                        <strong>Вес:</strong> {{ $pet->weight }} кг
                                                    </small>
                                                </div>
                                                @endif
                                                @if($pet->color)
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="bi bi-palette me-1"></i>
                                                        <strong>Окрас:</strong> {{ $pet->color }}
                                                    </small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Действия -->
                                    <div class="col-auto d-flex flex-column align-items-end">
                                        <a href="{{ route('client.profile.pets.edit', $pet) }}" 
                                           class="btn btn-outline-primary btn-sm mb-2 w-100">
                                            <i class="bi bi-pencil me-1"></i>Редактировать
                                        </a>
                                        
                                        <form method="POST" action="{{ route('client.profile.pets.destroy', $pet) }}" 
                                              onsubmit="return confirm('Вы уверены, что хотите удалить питомца? Это действие нельзя отменить.')"
                                              class="w-100">
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
                </div>

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
