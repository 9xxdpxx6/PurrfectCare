@extends('layouts.client')

@section('title', 'Каталог услуг - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">
                    @if($type === 'analyses')
                        Наши анализы
                    @elseif($type === 'vaccinations')
                        Наши вакцинации
                    @else
                        Наши услуги
                    @endif
                </h1>
                <p class="lead">
                    @if($type === 'analyses')
                        Полный спектр лабораторных исследований для ваших питомцев
                    @elseif($type === 'vaccinations')
                        Все виды вакцинаций для защиты ваших питомцев
                    @else
                        Полный спектр ветеринарных услуг для ваших питомцев
                    @endif
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="py-4 bg-light">
    <div class="container">
        <form method="GET" action="{{ route('client.services') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Поиск по названию</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Введите название...">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Тип</label>
                <select class="form-select" id="type" name="type" data-tomselect data-placeholder="Все типы">
                    <option value="services" {{ request('type', 'services') == 'services' ? 'selected' : '' }}>Услуги</option>
                    <option value="analyses" {{ request('type') == 'analyses' ? 'selected' : '' }}>Анализы</option>
                    <option value="vaccinations" {{ request('type') == 'vaccinations' ? 'selected' : '' }}>Вакцинации</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort" class="form-label">Сортировка</label>
                <select class="form-select" id="sort" name="sort" data-tomselect data-placeholder="По названию А-Я">
                    <option value="name_asc" {{ request('sort', 'name_asc') == 'name_asc' ? 'selected' : '' }}>По названию А-Я</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>По названию Я-А</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>По цене возрастание</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>По цене убывание</option>
                    @if($type === 'services')
                        <option value="duration_asc" {{ request('sort') == 'duration_asc' ? 'selected' : '' }}>По длительности возрастание</option>
                        <option value="duration_desc" {{ request('sort') == 'duration_desc' ? 'selected' : '' }}>По длительности убывание</option>
                    @endif
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Найти
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Services Grid -->
<section class="py-5">
    <div class="container">
        @if($items->count() > 0)
            <div class="row g-4">
                @foreach($items as $item)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm service-card d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">{{ $item->name }}</h5>
                                <span class="badge bg-primary">{{ $item->price }} ₽</span>
                            </div>
                            
                            <p class="card-text text-muted mb-3">
                                {{ Str::limit($item->description, 100) }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                @if($type === 'services')
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $item->duration }} мин
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Доступно
                                    </small>
                                @else
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>
                                        {{ $type === 'analyses' ? 'Анализ' : 'Вакцинация' }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Доступно
                                    </small>
                                @endif
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    @if($type === 'services')
                                        <a href="{{ route('client.services.show', $item) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Подробнее
                                        </a>
                                    @endif
                                    <a href="{{ route('client.appointment') }}" 
                                       class="btn btn-primary">
                                        <i class="bi bi-calendar-plus me-1"></i>Записаться
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    {{ $items->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-5">
                            <i class="bi bi-search display-1 text-muted mb-4"></i>
                            <h3 class="h4 mb-3">
                                @if($type === 'analyses')
                                    Анализы не найдены
                                @elseif($type === 'vaccinations')
                                    Вакцинации не найдены
                                @else
                                    Услуги не найдены
                                @endif
                            </h3>
                            <p class="text-muted mb-4">
                                По вашему запросу не найдено ни одного элемента. 
                                Попробуйте изменить параметры поиска.
                            </p>
                            <a href="{{ route('client.services') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>Сбросить фильтры
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
.service-card {
    cursor: pointer;
    
    &:hover {
        transform: none;
        box-shadow: none;
    }
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация TomSelect
    if (typeof window.createTomSelect === 'function') {
        const tomSelectElements = document.querySelectorAll('[data-tomselect]');
        tomSelectElements.forEach(element => {
            const placeholder = element.dataset.placeholder || 'Выберите значение...';
            window.createTomSelect(element, {
                placeholder: placeholder,
                allowEmptyOption: true,
                create: false
            });
        });
    }
});
</script>
@endpush
