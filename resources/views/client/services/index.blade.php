@extends('layouts.client')

@section('title', 'Каталог услуг - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Наши услуги</h1>
                <p class="lead">
                    Полный спектр ветеринарных услуг для ваших питомцев
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
                       value="{{ request('search') }}" placeholder="Введите название услуги...">
            </div>
            <div class="col-md-3">
                <label for="branch_id" class="form-label">Филиал</label>
                <select class="form-select" id="branch_id" name="branch_id">
                    <option value="">Все филиалы</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" 
                                {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort" class="form-label">Сортировка</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По названию</option>
                    <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>По цене</option>
                    <option value="duration" {{ request('sort') == 'duration' ? 'selected' : '' }}>По длительности</option>
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
        @if($services->count() > 0)
            <div class="row g-4">
                @foreach($services as $service)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm service-card d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">{{ $service->name }}</h5>
                                <span class="badge bg-primary">{{ $service->price }} ₽</span>
                            </div>
                            
                            <p class="card-text text-muted mb-3">
                                {{ Str::limit($service->description, 100) }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $service->duration }} мин
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $service->branches->count() }} филиал(ов)
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">Доступно в филиалах:</small>
                                <div class="mt-1">
                                    @foreach($service->branches->take(2) as $branch)
                                        <span class="badge bg-light text-dark me-1">{{ $branch->name }}</span>
                                    @endforeach
                                    @if($service->branches->count() > 2)
                                        <span class="badge bg-secondary">+{{ $service->branches->count() - 2 }} еще</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('client.services.show', $service) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Подробнее
                                    </a>
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
                    {{ $services->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-5">
                            <i class="bi bi-search display-1 text-muted mb-4"></i>
                            <h3 class="h4 mb-3">Услуги не найдены</h3>
                            <p class="text-muted mb-4">
                                По вашему запросу не найдено ни одной услуги. 
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
