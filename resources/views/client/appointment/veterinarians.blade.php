@extends('layouts.client')

@section('title', 'Выбор ветеринара - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Выбор ветеринара</h1>
                <p class="lead">
                    Выберите ветеринара в филиале "{{ $branch->name }}"
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Steps -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <div class="d-flex align-items-center flex-wrap justify-content-center">
                        <div class="step completed">
                            <div class="step-number">1</div>
                            <div class="step-label d-none d-sm-block">Филиал</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-number">2</div>
                            <div class="step-label d-none d-sm-block">Ветеринар</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-label d-none d-sm-block">Время</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-label d-none d-sm-block">Подтверждение</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Veterinarians Selection -->
<section class="py-5">
    <div class="container">
        <!-- Поиск -->
        <div class="row mb-4">
            <div class="col-12">
                <form method="GET" action="{{ route('client.appointment.veterinarians') }}" class="d-flex flex-column flex-md-row gap-2">
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Поиск по имени или специальности...">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary text-nowrap" type="submit">
                            <i class="bi bi-search me-1"></i>Найти
                        </button>
                        @if(request('search'))
                            <a href="{{ route('client.appointment.veterinarians', ['branch_id' => $branch->id]) }}" 
                               class="btn btn-outline-secondary text-nowrap">
                                <i class="bi bi-x me-1"></i>Очистить
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        @if($veterinarians->count() > 0)
            <div class="row g-4">
                @foreach($veterinarians as $veterinarian)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm veterinarian-card d-flex flex-column">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="text-center mb-4">
                                <div class="veterinarian-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <h4 class="card-title mt-3">{{ $veterinarian->name }}</h4>
                                <p class="text-muted mb-0">Ветеринар</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-primary mb-2">Специализации:</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($veterinarian->specialties as $specialty)
                                        <span class="badge bg-light text-dark">{{ $specialty->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            
                            
                            <div class="mb-4">
                                <h6 class="text-primary mb-2">Доступность:</h6>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2">
                                        <i class="bi bi-check-circle me-1"></i>Доступен
                                    </span>
                                    <small class="text-muted">Принимает записи</small>
                                </div>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-grid">
                                    <a href="{{ route('client.appointment.time', [
                                        'branch_id' => $branch->id,
                                        'veterinarian_id' => $veterinarian->id
                                    ]) }}" 
                                       class="btn btn-primary btn-lg">
                                        <i class="bi bi-arrow-right me-2"></i>Выбрать ветеринара
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="card border-0 bg-light no-hover">
                        <div class="card-body p-5">
                            <i class="bi bi-person-x display-1 text-muted mb-4"></i>
                            <h3 class="h4 mb-3">Ветеринары не найдены</h3>
                             <p class="text-muted mb-4">
                                 @if(request('search'))
                                     По запросу "{{ request('search') }}" в филиале "{{ $branch->name }}" не найдено ветеринаров.
                                 @else
                                     В выбранном филиале "{{ $branch->name }}" нет доступных ветеринаров.
                                 @endif
                             </p>
                            <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>Выбрать другой филиал
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

