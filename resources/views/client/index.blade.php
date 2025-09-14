@extends('layouts.client')

@section('title', 'Главная - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4 fade-in">
                    Забота о ваших питомцах — наша профессия
                </h1>
                <p class="lead mb-4 fade-in">
                    Современная ветеринарная клиника с полным спектром услуг для здоровья и благополучия ваших четвероногих друзей.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 fade-in">
                    <a href="{{ route('client.appointment') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                    </a>
                    <a href="{{ route('client.services') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-list-ul me-2"></i>Наши услуги
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-heart-pulse display-1 opacity-75 fade-in"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Почему выбирают нас?</h2>
                <p class="lead text-muted">
                    Мы предоставляем комплексный подход к здоровью ваших питомцев
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-shield-check text-primary fs-1"></i>
                        </div>
                        <h5 class="card-title">Профессиональные врачи</h5>
                        <p class="card-text text-muted">
                            Опытные ветеринары с многолетним стажем работы и постоянным повышением квалификации.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-gear text-success fs-1"></i>
                        </div>
                        <h5 class="card-title">Современное оборудование</h5>
                        <p class="card-text text-muted">
                            Новейшее диагностическое и лечебное оборудование для точной диагностики и эффективного лечения.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="bi bi-clock text-info fs-1"></i>
                        </div>
                        <h5 class="card-title">Удобное время работы</h5>
                        <p class="card-text text-muted">
                            Работаем ежедневно с 9:00 до 21:00, включая выходные. Экстренные случаи принимаем круглосуточно.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Наши услуги</h2>
                <p class="lead text-muted">
                    Полный спектр ветеринарных услуг для поддержания здоровья ваших питомцев
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-clipboard-pulse text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Диагностика</h6>
                        <p class="card-text text-muted small">Комплексное обследование и постановка диагноза</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-droplet text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Вакцинация</h6>
                        <p class="card-text text-muted small">Профилактические прививки по графику</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-scissors text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Хирургия</h6>
                        <p class="card-text text-muted small">Плановые и экстренные операции</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-heart text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Стоматология</h6>
                        <p class="card-text text-muted small">Лечение и профилактика заболеваний зубов</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('client.services') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-right me-2"></i>Все услуги
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Готовы записаться на прием?</h2>
                <p class="lead text-muted mb-4">
                    Выберите удобное время и запишитесь на прием к нашим специалистам
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('client.appointment') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-plus me-2"></i>Записаться онлайн
                    </a>
                    <a href="{{ route('client.contacts') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-telephone me-2"></i>Позвонить нам
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
