@extends('layouts.client')

@section('title', 'Контакты - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Контакты</h1>
                <p class="lead">
                    Свяжитесь с нами любым удобным способом
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contacts Content -->
<section class="py-5">
    <div class="container">
        @if($branches->count() > 0)
        <div class="row g-4">
            @foreach($branches as $branch)
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">{{ $branch->name }}</h3>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-geo-alt me-2"></i>Адрес
                            </h6>
                            <p class="text-muted mb-0">{{ $branch->address }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-telephone me-2"></i>Телефон
                            </h6>
                            <p class="text-muted mb-0">{{ $branch->phone }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-clock me-2"></i>Режим работы
                            </h6>
                            <p class="text-muted mb-0">
                                {{ $branch->opens_at ? $branch->opens_at->format('H:i') : '9:00' }} - 
                                {{ $branch->closes_at ? $branch->closes_at->format('H:i') : '21:00' }}
                            </p>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-grid gap-2">
                                <a href="tel:{{ $branch->phone }}" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-telephone me-1"></i>Позвонить
                                </a>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="https://t.me/{{ $branch->phone }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                            <i class="bi bi-telegram me-1"></i>Telegram
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="https://wa.me/{{ $branch->phone }}" class="btn btn-outline-success btn-sm w-100" target="_blank">
                                            <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Fallback if no branches -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Контактная информация</h3>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-telephone me-2"></i>Телефон
                            </h6>
                            <p class="text-muted mb-0">+7 (XXX) XXX-XX-XX</p>
                            <small class="text-muted">Основной номер</small>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-envelope me-2"></i>Email
                            </h6>
                            <p class="text-muted mb-0">info@purrfectcare.ru</p>
                            <small class="text-muted">Общие вопросы</small>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-geo-alt me-2"></i>Адрес
                            </h6>
                            <p class="text-muted mb-0">г. Москва, ул. Примерная, д. 1</p>
                            <small class="text-muted">Основной филиал</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- CTA Section -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body p-5">
                        <h3 class="h4 mb-3">Нужна консультация?</h3>
                        <p class="text-muted mb-4">
                            Свяжитесь с нами любым удобным способом или запишитесь на прием онлайн
                        </p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="{{ route('client.appointment') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
