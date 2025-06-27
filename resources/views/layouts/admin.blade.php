<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PurrfectCare - Админ-панель')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.css">
    @stack('styles')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('admin.dashboard') }}">PurrfectCare</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav d-flex flex-row align-items-center">
            <div class="nav-item text-nowrap me-3">
                <button class="theme-switch btn btn-link nav-link px-3 text-white" id="themeSwitch" title="Переключить тему">
                    <i class="bi bi-sun-fill d-none"></i>
                    <i class="bi bi-moon-fill"></i>
                </button>
            </div>
            <div class="nav-item text-nowrap">
                <span class="nav-link px-3 text-white">
                    <i class="bi bi-person-circle"></i> {{ Auth::user()?->name }}
                </span>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-house-door"></i> Главная
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> Клиенты
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pets.*') ? 'active' : '' }}" href="{{ route('admin.pets.index') }}">
                                <i class="bi bi-heart"></i> Питомцы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}" href="{{ route('admin.visits.index') }}">
                                <i class="bi bi-calendar-check"></i> Приемы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}" href="{{ route('admin.schedules.index') }}">
                                <i class="bi bi-calendar3"></i> Расписания
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                                <i class="bi bi-cart"></i> Заказы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.drugs.*') ? 'active' : '' }}" href="{{ route('admin.drugs.index') }}">
                                <i class="bi bi-capsule"></i> Препараты
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.drug-procurements.*') ? 'active' : '' }}" href="{{ route('admin.drug-procurements.index') }}">
                                <i class="bi bi-box-seam"></i> Поставки
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.vaccinations.*') ? 'active' : '' }}" href="{{ route('admin.vaccinations.index') }}">
                                <i class="bi bi-shield-check"></i> Вакцинации
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.lab-tests.*') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}">
                                <i class="bi bi-clipboard2-pulse"></i> Анализы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                                <i class="bi bi-person-badge"></i> Сотрудники
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                                <i class="bi bi-gear"></i> Услуги
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}">
                                <i class="bi bi-building"></i> Филиалы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.suppliers.index') }}">
                                <i class="bi bi-truck"></i> Поставщики
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"
                         role="alert"
                         id="auto-hide-alert"
                         style="position: fixed; top: 24px; right: 24px; z-index: 1080; min-width: 320px; max-width: 90vw;">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show"
                         role="alert"
                         style="position: fixed; top: 24px; right: 24px; z-index: 1080; min-width: 320px; max-width: 90vw;">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="conrainer col-12 col-xxl-10 mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-dismissible.fade.show:not(.alert-important)').forEach(function(alert) {
                setTimeout(() => {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 3000);
            });

            // Theme switcher
            const themeSwitch = document.getElementById('themeSwitch');
            const html = document.documentElement;
            const sunIcon = themeSwitch.querySelector('.bi-sun-fill');
            const moonIcon = themeSwitch.querySelector('.bi-moon-fill');

            // Check saved theme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                html.setAttribute('data-bs-theme', 'light');
                sunIcon.classList.remove('d-none');
                moonIcon.classList.add('d-none');
            }

            // Theme switch handler
            themeSwitch.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                html.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                if (newTheme === 'light') {
                    sunIcon.classList.remove('d-none');
                    moonIcon.classList.add('d-none');
                } else {
                    sunIcon.classList.add('d-none');
                    moonIcon.classList.remove('d-none');
                }
            });
        });
    </script>
</body>
</html>
