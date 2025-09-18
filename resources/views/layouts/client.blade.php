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
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="{{ route('client.index') }}">
                <img src="{{ asset('logo.png') }}" alt="PurrfectCare" class="me-2" style="height: 32px;">
                PurrfectCare
            </a>
            
            <button class="navbar-toggler" type="button" id="navbarToggler" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                    <li class="nav-item me-2">
                        <a href="https://t.me/purrfectcare_bot" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-telegram me-1"></i>Расписание в Telegram
                        </a>
                    </li>
                    @auth
                        <li class="nav-item me-2">
                            <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-plus me-1"></i>Записаться
                            </a>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-link nav-link px-3 text-dark" type="button" id="profileToggle">
                                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                            </button>
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

    <!-- Profile Overlay -->
    <div id="profileOverlay" class="overlay-overlay">
        <div class="overlay-content profile-overlay">
            <div class="overlay-header">
                <h6>Профиль</h6>
            </div>
            <div class="overlay-body">
                <a href="{{ route('client.profile') }}" class="dropdown-item">
                    <i class="bi bi-person"></i>Личный кабинет
                </a>
                <a href="{{ route('client.appointment.appointments') }}" class="dropdown-item">
                    <i class="bi bi-calendar-check"></i>Мои записи
                </a>
                <a href="{{ route('client.profile.visits') }}" class="dropdown-item">
                    <i class="bi bi-clock-history"></i>История визитов
                </a>
                <a href="{{ route('client.profile.orders') }}" class="dropdown-item">
                    <i class="bi bi-bag"></i>Мои заказы
                </a>
                <a href="{{ route('client.profile.pets') }}" class="dropdown-item">
                    <i class="bi bi-heart"></i>Мои питомцы
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('client.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item w-100 text-start border-0 bg-transparent">
                        <i class="bi bi-box-arrow-right"></i>Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-primary mb-3">
                        <a href="{{ route('client.index') }}" class="text-primary text-decoration-none d-flex align-items-center">
                            <img src="{{ asset('logo.png') }}" alt="PurrfectCare" class="me-2" style="height: 28px;">
                            PurrfectCare
                        </a>
                    </h5>
                    <p class="text-muted">Профессиональная ветеринарная помощь для ваших питомцев. Забота, которой они заслуживают.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="text-primary mb-3">Полезные ссылки</h6>
                    <div class="d-flex flex-column">
                        <a href="{{ route('client.services') }}" class="text-muted mb-2 text-decoration-none">
                            Наши услуги
                        </a>
                        <a href="{{ route('client.about') }}" class="text-muted mb-2 text-decoration-none">
                            О клинике
                        </a>
                        <a href="{{ route('client.contacts') }}" class="text-muted mb-2 text-decoration-none">
                            Контакты
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="text-primary mb-3">Быстрые действия</h6>
                    <div class="d-flex flex-column">
                        <a href="{{ route('client.appointment.branches') }}" class="text-muted mb-2 text-decoration-none">
                            Записаться на прием
                        </a>
                        @auth
                        <a href="{{ route('client.profile.visits') }}" class="text-muted mb-2 text-decoration-none">
                            Мои записи
                        </a>
                        <a href="{{ route('client.profile.pets') }}" class="text-muted mb-2 text-decoration-none">
                            Мои питомцы
                        </a>
                        @else
                        <a href="{{ route('client.login') }}" class="text-muted mb-2 text-decoration-none">
                            Войти в кабинет
                        </a>
                        @endauth
                        <a href="https://t.me/purrfectcare_bot" class="text-muted mb-2 text-decoration-none" target="_blank">
                            Расписание в Telegram
                            <i class="bi bi-telegram me-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} PurrfectCare. Все права защищены.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('client.privacy') }}" class="text-muted me-3 small">Политика конфиденциальности</a>
                    <a href="{{ route('client.terms') }}" class="text-muted small">Условия использования</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
