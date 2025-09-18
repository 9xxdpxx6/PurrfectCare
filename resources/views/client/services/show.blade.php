@extends('layouts.client')

@section('title', $service->name . ' - PurrfectCare')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="py-3 bg-light">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('client.index') }}">Главная</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('client.services') }}">Услуги</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
        </ol>
    </div>
</nav>

<!-- Service Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h1 class="h2 mb-0">{{ $service->name }}</h1>
                            <div class="text-end">
                                <div class="h3 text-primary mb-0">{{ $service->price }} ₽</div>
                                <small class="text-muted">за {{ $service->duration }} минут</small>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-clock text-primary me-2"></i>
                                    <span class="fw-bold">Длительность:</span>
                                    <span class="ms-2">{{ $service->duration }} минут</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt text-primary me-2"></i>
                                    <span class="fw-bold">Доступно в:</span>
                                    <span class="ms-2">{{ $service->branches->count() }} филиалах</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h3 class="h4 mb-3">Описание услуги</h3>
                        <div class="text-muted">
                            {!! nl2br(e($service->description)) !!}
                        </div>
                        
                        @if($service->branches->count() > 0)
                        <hr>
                        
                        <h3 class="h4 mb-3">Доступно в филиалах</h3>
                        <div class="row g-3">
                            @foreach($service->branches as $branch)
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">{{ $branch->name }}</h6>
                                        <p class="card-text small text-muted mb-2">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $branch->address }}
                                        </p>
                                        <p class="card-text small text-muted mb-2">
                                            <i class="bi bi-telephone me-1"></i>{{ $branch->phone }}
                                        </p>
                                        <p class="card-text small text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $branch->opens_at ? $branch->opens_at->format('H:i') : '9:00' }} - 
                                            {{ $branch->closes_at ? $branch->closes_at->format('H:i') : '21:00' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="h5 mb-3">Записаться на услугу</h4>
                        <div class="d-grid gap-2">
                            @auth
                                @if(auth()->user()->hasVerifiedEmail())
                                    <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary btn-lg">
                                        <i class="bi bi-calendar-plus me-2"></i>Записаться онлайн
                                    </a>
                                @else
                                    <a href="{{ route('client.verify-email') }}" class="btn btn-warning btn-lg">
                                        <i class="bi bi-envelope-exclamation me-2"></i>Подтвердить email
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('client.login') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-calendar-plus me-2"></i>Записаться онлайн
                                </a>
                            @endauth
                            <a href="tel:+7XXXXXXXXXX" class="btn btn-outline-primary">
                                <i class="bi bi-telephone me-2"></i>Позвонить
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <h6 class="text-muted mb-2">Стоимость услуги</h6>
                            <div class="h3 text-primary mb-0">{{ $service->price }} ₽</div>
                            <small class="text-muted">за {{ $service->duration }} минут</small>
                        </div>
                    </div>
                </div>
                
                <!-- Related Services -->
                @if($relatedServices->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="h5 mb-3">Похожие услуги</h4>
                        @foreach($relatedServices as $relatedService)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <a href="{{ route('client.services.show', $relatedService) }}" 
                                       class="text-decoration-none">
                                        {{ $relatedService->name }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ $relatedService->price }} ₽</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.card-title a {
    color: #2c3e50;
    transition: color 0.2s ease-in-out;
}

.card-title a:hover {
    color: #007bff;
}

.breadcrumb {
    background: none;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
    color: #6c757d;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush
