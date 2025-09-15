<!DOCTYPE html>
<html lang="ru" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PurrfectCare - Ветеринарная клиника')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    @vite(['resources/sass/client.scss', 'resources/js/client.js'])
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ route('client.index') }}">
                <i class="bi bi-heart-pulse me-2"></i>PurrfectCare
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.index') }}">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.about') }}">О нас</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.services') }}">Услуги</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client.contacts') }}">Контакты</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item me-2">
                            <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-plus me-1"></i>Записаться
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('client.profile') }}">Личный кабинет</a></li>
                                <li><a class="dropdown-item" href="{{ route('client.appointment.appointments') }}">Мои записи</a></li>
                                <li><a class="dropdown-item" href="{{ route('client.profile.visits') }}">История визитов</a></li>
                                <li><a class="dropdown-item" href="{{ route('client.profile.orders') }}">Мои заказы</a></li>
                                <li><a class="dropdown-item" href="{{ route('client.profile.pets') }}">Мои питомцы</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('client.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Выйти</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.login') }}">Войти</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('client.register') }}">Регистрация</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-vh-100">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-primary mb-3">
                        <a href="{{ route('client.index') }}" class="text-primary text-decoration-none">
                            <i class="bi bi-heart-pulse me-2"></i>PurrfectCare
                        </a>
                    </h5>
                    <p class="text-muted">Профессиональная ветеринарная помощь для ваших питомцев. Забота, которой они заслуживают.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="text-primary mb-3">Контакты</h6>
                    @php
                        $mainBranch = \App\Models\Branch::first();
                    @endphp
                    @if($mainBranch)
                        <p class="text-muted mb-1">
                            <i class="bi bi-telephone me-2"></i>{{ $mainBranch->phone }}
                        </p>
                        <p class="text-muted mb-1">
                            <i class="bi bi-envelope me-2"></i>info@purrfectcare.ru
                        </p>
                        <p class="text-muted mb-2">
                            <i class="bi bi-geo-alt me-2"></i>{{ $mainBranch->address }}
                        </p>
                        <a href="{{ route('client.contacts') }}" class="text-primary small">
                            <i class="bi bi-building me-1"></i>Все филиалы
                        </a>
                    @else
                        <p class="text-muted mb-1">
                            <i class="bi bi-telephone me-2"></i>+7 (XXX) XXX-XX-XX
                        </p>
                        <p class="text-muted mb-1">
                            <i class="bi bi-envelope me-2"></i>info@purrfectcare.ru
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-geo-alt me-2"></i>г. Москва, ул. Примерная, д. 1
                        </p>
                    @endif
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="text-primary mb-3">Режим работы</h6>
                    @if($mainBranch)
                        <p class="text-muted mb-1">
                            Пн-Пт: {{ $mainBranch->opens_at ? $mainBranch->opens_at->format('H:i') : '9:00' }} - {{ $mainBranch->closes_at ? $mainBranch->closes_at->format('H:i') : '21:00' }}
                        </p>
                        <p class="text-muted mb-1">Сб-Вс: 10:00 - 18:00</p>
                        <p class="text-muted mb-2">Экстренные случаи: 24/7</p>
                        <a href="{{ route('client.contacts') }}" class="text-primary small">
                            <i class="bi bi-clock me-1"></i>Режим всех филиалов
                        </a>
                    @else
                        <p class="text-muted mb-1">Пн-Пт: 9:00 - 21:00</p>
                        <p class="text-muted mb-1">Сб-Вс: 10:00 - 18:00</p>
                        <p class="text-muted">Экстренные случаи: 24/7</p>
                    @endif
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} PurrfectCare. Все права защищены.</p>
                    <p class="text-muted small mt-1">Информация на сайте не является публичной офертой.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('client.privacy') }}" class="text-muted me-3">Политика конфиденциальности</a>
                    <a href="{{ route('client.terms') }}" class="text-muted">Условия использования</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
