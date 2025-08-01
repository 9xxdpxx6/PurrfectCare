<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PurrfectCare - Админ-панель')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.css">
    @stack('styles')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: var(--bs-body-bg);
            padding-top: 20px;
            border-right: 1px solid var(--bs-border-color);
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar a {
            text-decoration: none;
            color: var(--bs-body-color);
        }
        .sidebar .nav-link {
            padding: 8px 15px;
            font-size: 15px;
        }
        .sidebar .nav-link:hover {
            background-color: var(--bs-secondary-bg);
            border-radius: 4px;
        }
        .sidebar .collapse .nav-link {
            padding-left: 30px;
            font-size: 14px;
        }
        .sidebar .nav-link.active {
            background-color: var(--bs-primary);
            color: white !important;
            border-radius: 4px;
            font-weight: 500;
        }
        .sidebar .collapse .nav-link.active {
            background-color: var(--bs-primary);
            color: white !important;
            font-weight: 500;
        }
        .sidebar .nav-link:hover:not(.active) {
            background-color: var(--bs-secondary-bg);
            border-radius: 4px;
        }
        .sidebar .collapse .nav-link:hover:not(.active) {
            background-color: var(--bs-secondary-bg);
            border-radius: 4px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar-toggle {
            display: none;
        }
        .collapse-arrow {
            transition: transform 0.3s ease;
            display: inline-block;
        }
        .collapse-arrow.rotated {
            transform: rotate(180deg);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <button class="navbar-toggler sidebar-toggle d-md-none collapsed me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('admin.dashboard') }}">PurrfectCare</a>
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

    <div class="sidebar" id="sidebarMenu">
        <div class="px-3">
            <h4 class="mb-4">Админ-панель</h4>

            <!-- Основное -->
            <ul class="nav flex-column mb-4">
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-expanded="true">
                        <span><i class="bi bi-house-door me-2"></i>Основное</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="mainMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    Главная
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                                    Заказы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                                    Услуги
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Пациенты -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#clientsMenu" aria-expanded="true">
                        <span><i class="bi bi-people me-2"></i>Пациенты</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="clientsMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    Клиенты
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pets.*') ? 'active' : '' }}" href="{{ route('admin.pets.index') }}">
                                    Питомцы
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Медицина -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#medicineMenu" aria-expanded="true">
                        <span><i class="bi bi-heart-pulse me-2"></i>Медицина</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="medicineMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}" href="{{ route('admin.visits.index') }}">
                                    Приемы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.vaccinations.*') ? 'active' : '' }}" href="{{ route('admin.vaccinations.index') }}">
                                    Вакцинации
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.lab-tests.*') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}">
                                    Анализы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.drugs.*') ? 'active' : '' }}" href="{{ route('admin.drugs.index') }}">
                                    Препараты
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Организация -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#organizationMenu" aria-expanded="true">
                        <span><i class="bi bi-building me-2"></i>Организация</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="organizationMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                                    Сотрудники
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}" href="{{ route('admin.schedules.index') }}">
                                    Расписания
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.drug-procurements.*') ? 'active' : '' }}" href="{{ route('admin.drug-procurements.index') }}">
                                    Поставки
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Настройки -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="true">
                        <span><i class="bi bi-gear me-2"></i>Настройки</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="settingsMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-types') ? 'active' : '' }}" href="{{ route('admin.settings.lab-test-types') }}">
                                    Типы анализов
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-params') ? 'active' : '' }}" href="{{ route('admin.settings.lab-test-params') }}">
                                    Параметры анализов
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.statuses') ? 'active' : '' }}" href="{{ route('admin.settings.statuses') }}">
                                    Статусы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.units') ? 'active' : '' }}" href="{{ route('admin.settings.units') }}">
                                    Единицы измерений
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.branches') ? 'active' : '' }}" href="{{ route('admin.settings.branches') }}">
                                    Филиалы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.specialties') ? 'active' : '' }}" href="{{ route('admin.settings.specialties') }}">
                                    Специальности
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.species') ? 'active' : '' }}" href="{{ route('admin.settings.species') }}">
                                    Виды животных
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.breeds') ? 'active' : '' }}" href="{{ route('admin.settings.breeds') }}">
                                    Породы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.suppliers') ? 'active' : '' }}" href="{{ route('admin.settings.suppliers') }}">
                                    Поставщики
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.dictionary-diagnoses') ? 'active' : '' }}" href="{{ route('admin.settings.dictionary-diagnoses') }}">
                                    Диагнозы (словарь)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.dictionary-symptoms') ? 'active' : '' }}" href="{{ route('admin.settings.dictionary-symptoms') }}">
                                    Симптомы (словарь)
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
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
        
        @yield('content')
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts
            document.querySelectorAll('.alert-dismissible.fade.show:not(.alert-important)').forEach(function(alert) {
                setTimeout(() => {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
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

            // Collapse arrow animations
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
                const arrow = link.querySelector('.collapse-arrow');
                const targetId = link.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                
                // Check if elements exist
                if (!arrow || !target) {
                    return;
                }
                
                // Set initial state - if expanded, arrow should point down
                if (target.classList.contains('show')) {
                    arrow.classList.remove('rotated');
                } else {
                    arrow.classList.add('rotated');
                }
                
                // Listen for Bootstrap collapse events
                target.addEventListener('show.bs.collapse', function () {
                    arrow.classList.remove('rotated');
                });
                
                target.addEventListener('hide.bs.collapse', function () {
                    arrow.classList.add('rotated');
                });
            });

            // Auto-expand sections with active items
            function expandActiveSections() {
                const activeLinks = document.querySelectorAll('.sidebar .nav-link.active');
                activeLinks.forEach(link => {
                    const parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        const parentLink = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                        if (parentLink) {
                            parentCollapse.classList.add('show');
                            parentLink.setAttribute('aria-expanded', 'true');
                            const arrow = parentLink.querySelector('.collapse-arrow');
                            if (arrow) {
                                arrow.classList.remove('rotated'); // Point down when expanded
                            }
                        }
                    }
                });
            }

            // Call on page load
            expandActiveSections();

            // Mobile sidebar toggle
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.getElementById('sidebarMenu');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768 && sidebar) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
    </script>
</body>
</html>
