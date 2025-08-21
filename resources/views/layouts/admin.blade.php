<!DOCTYPE html>
<html lang="ru" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PurrfectCare - Админ-панель')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.css">
    @stack('styles')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* Стили для оверлеев */
        .overlay-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: none;
        }

        .overlay-content {
            position: absolute;
            top: 54px;
            right: 40px;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            min-width: 300px;
            max-width: 400px;
            max-height: 500px;
            overflow: hidden;
            animation: overlaySlideIn 0.2s ease-out;
        }

        /* Специальные стили для оверлея уведомлений */
        .notifications-overlay.overlay-content {
            display: flex;
            flex-direction: column;
        }

        @keyframes overlaySlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) translateX(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0) translateX(0);
            }
        }

        .notifications-overlay {
            width: 350px;
        }

        .notifications-overlay .overlay-content {
            max-height: 500px;
            overflow: hidden;
        }

        .notifications-overlay .overlay-header {
            flex-shrink: 0;
            padding: 1rem;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .notifications-overlay #notificationsList {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 350px;
            padding: 0;
        }

        .notifications-overlay .notification-item {
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1rem;
            margin: 0;
            background: none;
            transition: background-color 0.2s ease;
        }

        .notifications-overlay .notification-item:last-child {
            border-bottom: none;
        }

        .notifications-overlay .notification-item:hover {
            background-color: var(--bs-light);
        }

        [data-bs-theme="dark"] .notifications-overlay .notification-item:hover {
            background-color: var(--bs-dark);
        }

        /* Фиксируем кнопку "Все уведомления" внизу */
        .notifications-overlay .overlay-item:last-child {
            flex-shrink: 0;
            border-top: 1px solid var(--bs-border-color);
            margin-top: auto;
            background-color: var(--bs-body-bg);
        }

        /* Стили для скроллбара */
        .notifications-overlay #notificationsList::-webkit-scrollbar {
            width: 6px;
        }

        .notifications-overlay #notificationsList::-webkit-scrollbar-track {
            background: transparent;
        }

        .notifications-overlay #notificationsList::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
            border-radius: 3px;
        }

        .notifications-overlay #notificationsList::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary);
        }

        /* Стили для Firefox */
        .notifications-overlay #notificationsList {
            scrollbar-width: thin;
            scrollbar-color: var(--bs-border-color) transparent;
        }

        .user-overlay {
            width: 250px;
        }

        .overlay-header {
            padding: 1rem;
            background-color: var(--bs-light);
            border-bottom: 1px solid var(--bs-border-color);
            font-weight: 600;
        }

        [data-bs-theme="dark"] .overlay-header {
            background-color: var(--bs-dark);
            color: var(--bs-light);
        }

        .overlay-divider {
            height: 1px;
            background-color: var(--bs-border-color);
            margin: 0;
        }

        .overlay-item {
            padding: 0.75rem 1rem;
            transition: background-color 0.2s ease;
        }

        .overlay-item:hover {
            background-color: var(--bs-light);
        }

        [data-bs-theme="dark"] .overlay-item:hover {
            background-color: var(--bs-dark);
        }



        .overlay-item a {
            color: var(--bs-body-color);
            text-decoration: none;
            display: block;
        }

        .overlay-item a:hover {
            color: var(--primary-color);
        }

        .overlay-item:last-child {
            border-bottom: none;
        }

        /* Стили для активного состояния кнопок */
        .nav-link.active,
        .btn.active {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        /* Устанавливаем высоту header для оверлеев */
        header {
            height: var(--header-height);
        }

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
        
        /* Адаптивные стили для таблиц */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 576px) {
            .table-responsive table {
                font-size: 0.875rem;
            }
            
            .table-responsive th,
            .table-responsive td {
                padding: 0.5rem 0.25rem;
                white-space: nowrap;
            }
            
            .table-responsive .table-sm th,
            .table-responsive .table-sm td {
                padding: 0.25rem 0.125rem;
            }
            
            /* Скрываем менее важные колонки на мобильных */
            .table-responsive .d-none-mobile {
                display: none !important;
            }
            
            /* Уменьшаем размеры карточек на мобильных */
            .card-body {
                padding: 1rem 0.75rem;
            }
            
            .card-header {
                padding: 0.75rem;
            }
            
            /* Адаптируем кнопки на мобильных */
            .btn-toolbar .btn-group {
                flex-wrap: wrap;
                gap: 0.25rem;
            }
            
            .btn-toolbar .btn {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
        }
        
        @media (max-width: 768px) {
            /* Скрываем некоторые колонки на планшетах */
            .table-responsive .d-none-tablet {
                display: none !important;
            }
            
            /* Улучшение отображения графиков на планшетах */
            canvas {
                max-width: 100%;
                height: auto !important;
            }
            
            /* Адаптация метрик на планшетах */
            .col-md-3 {
                margin-bottom: 1rem;
            }
        }

        /* Декоративные карточки навигации и KPI */
        .nav-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border-radius: 12px;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .nav-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.25); }

        .kpi-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border-radius: 12px;
        }
        
        /* Дополнительные стили для очень маленьких экранов */
        @media (max-width: 480px) {
            .h2 {
                font-size: 1.25rem;
            }
            
            .card-title {
                font-size: 0.875rem;
            }
            
            .card-body h3 {
                font-size: 1rem;
            }
            
            .card-body h4 {
                font-size: 0.875rem;
            }
            
            /* Уменьшение отступов на очень маленьких экранах */
            .main-content {
                padding: 10px;
            }
            
            .card-body {
                padding: 0.75rem;
            }
        }

                 /* Перенесено в resources/sass/app.scss */
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
            <div class="nav-item text-nowrap me-3">
                <button class="btn btn-link nav-link px-3 text-white position-relative" type="button" id="notificationsToggle">
                    <span class="fs-5"><i class="bi bi-bell-fill"></i></span>
                    <span class="position-absolute top-0 mt-2 py-0 px-2 fw-normal start-100 translate-middle badge rounded-pill bg-primary notification-badge" id="notificationBadge" style="display: none;">
                        0
                    </span>
                </button>
            </div>
            <div class="nav-item text-nowrap">
                <button class="btn btn-link nav-link px-3 text-white" type="button" id="userToggle">
                    <span class="fs-5"><i class="bi bi-person-fill"></i></span> {{ Auth::guard('admin')->user()?->name }}
                </button>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <!-- Оверлей уведомлений -->
    <div id="notificationsOverlay" class="overlay-overlay" style="display: none;">
        <div class="overlay-content notifications-overlay">
            <div class="overlay-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 col-6">Уведомления</h6>
                <button class="btn btn-sm btn-link text-decoration-none col-6" id="markAllAsRead">
                    Отметить все как прочитанные
                </button>
            </div>
            <div id="notificationsList">
                <div class="text-center text-muted py-3">
                    <i class="bi bi-bell-slash"></i>
                    <p class="mb-0">Нет новых уведомлений</p>
                </div>
            </div>
            <div class="overlay-item">
                <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none">
                    <i class="bi bi-list-ul me-2"></i>Все уведомления
                </a>
            </div>
        </div>
    </div>

    <!-- Оверлей пользователя -->
    <div id="userOverlay" class="overlay-overlay" style="display: none;">
        <div class="overlay-content user-overlay">
            <div class="overlay-header">
                <h6 class="mb-0">Профиль</h6>
            </div>
            <div class="overlay-divider"></div>
            <div class="overlay-item">
                <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i>Выйти
                </a>
            </div>
        </div>
    </div>

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

                <!-- Статистика -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#statisticsMenu" aria-expanded="true">
                        <span><i class="bi bi-graph-up me-2"></i>Статистика</span>
                        <i class="bi bi-chevron-down collapse-arrow"></i>
                    </a>
                    <div class="collapse show" id="statisticsMenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.dashboard') ? 'active' : '' }}" href="{{ route('admin.statistics.dashboard') }}">
                                    Общая статистика
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.financial') ? 'active' : '' }}" href="{{ route('admin.statistics.financial') }}">
                                    Финансы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.operational') ? 'active' : '' }}" href="{{ route('admin.statistics.operational') }}">
                                    Эффективность
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.clients') ? 'active' : '' }}" href="{{ route('admin.statistics.clients') }}">
                                    Клиенты
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.medical') ? 'active' : '' }}" href="{{ route('admin.statistics.medical') }}">
                                    Медицина
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.statistics.conversion') ? 'active' : '' }}" href="{{ route('admin.statistics.conversion') }}">
                                    Конверсия
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

                <!-- Уведомления -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                        <i class="bi bi-bell me-2"></i>Уведомления
                    </a>
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
                                <a class="nav-link {{ request()->routeIs('admin.lab-tests.types.*') ? 'active' : '' }}" href="{{ route('admin.lab-tests.types.index') }}">
                                    Типы анализов
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.lab-tests.params.*') ? 'active' : '' }}" href="{{ route('admin.lab-tests.params.index') }}">
                                    Параметры анализов
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.vaccination-types.*') ? 'active' : '' }}" href="{{ route('admin.vaccination-types.index') }}">
                                    Типы вакцинаций
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.statuses.*') ? 'active' : '' }}" href="{{ route('admin.settings.statuses.index') }}">
                                    Статусы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.units.*') ? 'active' : '' }}" href="{{ route('admin.settings.units.index') }}">
                                    Единицы измерений
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.branches.*') ? 'active' : '' }}" href="{{ route('admin.settings.branches.index') }}">
                                    Филиалы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.specialties.*') ? 'active' : '' }}" href="{{ route('admin.settings.specialties.index') }}">
                                    Специальности
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.species.*') ? 'active' : '' }}" href="{{ route('admin.settings.species.index') }}">
                                    Виды животных
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.breeds.*') ? 'active' : '' }}" href="{{ route('admin.settings.breeds.index') }}">
                                    Породы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.settings.suppliers.index') }}">
                                    Поставщики
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.diagnoses.*') ? 'active' : '' }}" href="{{ route('admin.settings.diagnoses.index') }}">
                                    Диагнозы (словарь)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.symptoms.*') ? 'active' : '' }}" href="{{ route('admin.settings.symptoms.index') }}">
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
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.4.0/air-datepicker.min.js"></script>

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

            // Load saved collapse states first
            function loadSavedMenuStates() {
                // Set flag to prevent saving during initial load
                window.isLoadingMenuState = true;
                
                document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
                    const arrow = link.querySelector('.collapse-arrow');
                    const targetId = link.getAttribute('data-bs-target');
                    const target = document.querySelector(targetId);
                    
                    // Check if elements exist
                    if (!arrow || !target) {
                        return;
                    }
                    
                    // Load saved state from localStorage
                    const savedState = localStorage.getItem(`menu_${targetId}`);
                    if (savedState === 'collapsed') {
                        target.classList.remove('show');
                        link.setAttribute('aria-expanded', 'false');
                        arrow.classList.add('rotated');
                    } else if (savedState === 'expanded') {
                        target.classList.add('show');
                        link.setAttribute('aria-expanded', 'true');
                        arrow.classList.remove('rotated');
                    }
                    
                    // Set initial state - if expanded, arrow should point down
                    if (target.classList.contains('show')) {
                        arrow.classList.remove('rotated');
                    } else {
                        arrow.classList.add('rotated');
                    }
                });
                
                // Clear flag after loading
                setTimeout(() => {
                    window.isLoadingMenuState = false;
                }, 500);
            }

            // Auto-expand sections with active items
            function expandActiveSections() {
                const activeLinks = document.querySelectorAll('.sidebar .nav-link.active');
                activeLinks.forEach(link => {
                    const parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        const parentLink = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                        if (parentLink) {
                            // Always expand sections with active items, regardless of saved state
                            parentCollapse.classList.add('show');
                            parentLink.setAttribute('aria-expanded', 'true');
                            const arrow = parentLink.querySelector('.collapse-arrow');
                            if (arrow) {
                                arrow.classList.remove('rotated'); // Point down when expanded
                            }
                            // Save the expanded state
                            localStorage.setItem(`menu_#${parentCollapse.id}`, 'expanded');
                        }
                    }
                });
            }

            // Load saved states first, then expand active sections
            loadSavedMenuStates();
            expandActiveSections();

            // Add event listeners for collapse state changes
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
                const arrow = link.querySelector('.collapse-arrow');
                const targetId = link.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                
                if (!arrow || !target) {
                    return;
                }
                
                // Listen for Bootstrap collapse events
                target.addEventListener('show.bs.collapse', function () {
                    arrow.classList.remove('rotated');
                    // Save state to localStorage only if not loading
                    if (!window.isLoadingMenuState) {
                        localStorage.setItem(`menu_${targetId}`, 'expanded');
                    }
                });
                
                target.addEventListener('hide.bs.collapse', function () {
                    arrow.classList.add('rotated');
                    // Save state to localStorage only if not loading
                    if (!window.isLoadingMenuState) {
                        localStorage.setItem(`menu_${targetId}`, 'collapsed');
                    }
                });
                
                // Use MutationObserver to watch for class changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class' && !window.isLoadingMenuState) {
                            const isExpanded = target.classList.contains('show');
                            const state = isExpanded ? 'expanded' : 'collapsed';
                            localStorage.setItem(`menu_${targetId}`, state);
                        }
                    });
                });
                
                // Start observing
                observer.observe(target, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                
                // Also listen for manual clicks on the toggle
                link.addEventListener('click', function() {
                    setTimeout(() => {
                        if (!window.isLoadingMenuState) {
                            const isExpanded = target.classList.contains('show');
                            const state = isExpanded ? 'expanded' : 'collapsed';
                            localStorage.setItem(`menu_${targetId}`, state);
                        }
                    }, 100);
                });
            });

            // Save and restore sidebar scroll position
            const sidebarElement = document.getElementById('sidebarMenu');
            if (sidebarElement) {
                // Restore scroll position on page load with a small delay
                const savedScrollTop = localStorage.getItem('sidebar_scroll_top');
                if (savedScrollTop) {
                    // Use setTimeout to ensure DOM is fully loaded
                    setTimeout(() => {
                        sidebarElement.scrollTop = parseInt(savedScrollTop);
                    }, 100);
                }
                
                // Save scroll position when scrolling
                sidebarElement.addEventListener('scroll', function() {
                    localStorage.setItem('sidebar_scroll_top', sidebarElement.scrollTop);
                });
            }

            // Function to clear saved menu state (can be called when needed)
            function clearMenuState() {
                // Clear all menu collapse states
                document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(link => {
                    const targetId = link.getAttribute('data-bs-target');
                    localStorage.removeItem(`menu_${targetId}`);
                });
                // Clear scroll position
                localStorage.removeItem('sidebar_scroll_top');
            }

            // Optional: Clear menu state on logout or user change
            // Uncomment the following line if you want to clear state on page unload
            // window.addEventListener('beforeunload', clearMenuState);

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
            
            // Дополнительные улучшения для мобильных устройств
            // Улучшение отображения таблиц на мобильных
            const tables = document.querySelectorAll('.table-responsive table');
            tables.forEach(table => {
                // Добавляем класс для лучшего отображения на мобильных
                if (window.innerWidth <= 768) {
                    table.classList.add('table-sm');
                }
            });
            
            // Адаптация графиков на мобильных устройствах
            const canvases = document.querySelectorAll('canvas');
            canvases.forEach(canvas => {
                if (window.innerWidth <= 768) {
                    canvas.style.maxHeight = '200px';
                }
            });
            
            // Обработка изменения размера окна
            window.addEventListener('resize', function() {
                const isMobile = window.innerWidth <= 768;
                const isSmallMobile = window.innerWidth <= 480;
                
                tables.forEach(table => {
                    if (isMobile) {
                        table.classList.add('table-sm');
                    } else {
                        table.classList.remove('table-sm');
                    }
                });
                
                canvases.forEach(canvas => {
                    if (isMobile) {
                        canvas.style.maxHeight = '200px';
                    } else {
                        canvas.style.maxHeight = '';
                    }
                });
            });

            // Система уведомлений
            class NotificationManager {
                constructor() {
                    this.badge = document.getElementById('notificationBadge');
                    this.list = document.getElementById('notificationsList');
                    this.markAllBtn = document.getElementById('markAllAsRead');
                    this.overlay = document.getElementById('notificationsOverlay');
                    this.toggle = document.getElementById('notificationsToggle');
                    this.isOpen = false;
                    this.init();
                }

                init() {
                    this.loadNotifications();
                    this.setupEventListeners();
                    this.startPolling();
                }

                setupEventListeners() {
                    this.markAllBtn.addEventListener('click', () => this.markAllAsRead());
                    
                    // Переключатель оверлея уведомлений
                    this.toggle.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleOverlay();
                    });

                    // Закрытие при клике по темной области
                    this.overlay.addEventListener('click', (e) => {
                        if (e.target === this.overlay) {
                            this.closeOverlay();
                        }
                    });

                    // Предотвращаем закрытие при скроллинге
                    this.list.addEventListener('scroll', (e) => {
                        e.stopPropagation();
                    });

                    // Закрытие при нажатии Escape
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            this.closeOverlay();
                        }
                    });
                }

                async loadNotifications() {
                    try {
                        const response = await fetch('/admin/notifications/recent');
                        if (response.ok) {
                            const data = await response.json();
                            this.updateBadge(data.unread_count);
                            this.updateNotificationsList(data.notifications);
                        }
                    } catch (error) {
                        console.error('Failed to load notifications:', error);
                    }
                }

                updateBadge(count) {
                    if (count > 0) {
                        this.badge.textContent = count;
                        this.badge.style.display = 'block';
                    } else {
                        this.badge.style.display = 'none';
                    }
                }

                updateNotificationsList(notifications) {
                    if (notifications.length === 0) {
                        this.list.innerHTML = `
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-bell-slash"></i>
                                <p class="mb-0">Нет новых уведомлений</p>
                            </div>
                        `;
                        return;
                    }

                    this.list.innerHTML = notifications.map(notification => `
                        <div class="notification-item ${notification.read_at ? 'text-muted' : ''}" 
                             data-notification-id="${notification.id}">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${notification.data.title}</div>
                                    <div class="small">${notification.data.message}</div>
                                    <div class="text-muted small">
                                        ${new Date(notification.created_at).toLocaleString('ru-RU')}
                                    </div>
                                    <div class="notification-links mt-2">
                                        ${this.generateNotificationLinks(notification.data)}
                                    </div>
                                </div>
                                ${!notification.read_at ? '<span class="badge bg-primary ms-2">Новое</span>' : ''}
                            </div>
                        </div>
                    `).join('');

                    // Добавляем обработчики кликов
                    this.list.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', () => this.markAsRead(item.dataset.notificationId));
                    });
                }

                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`/admin/notifications/${notificationId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            this.loadNotifications(); // Перезагружаем список
                        }
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                }

                async markAllAsRead() {
                    try {
                        const response = await fetch('/admin/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            this.loadNotifications(); // Перезагружаем список
                        }
                    } catch (error) {
                        console.error('Failed to mark all notifications as read:', error);
                    }
                }

                toggleOverlay() {
                    if (this.isOpen) {
                        this.closeOverlay();
                    } else {
                        this.openOverlay();
                    }
                }

                openOverlay() {
                    this.overlay.style.display = 'block';
                    this.isOpen = true;
                    this.toggle.classList.add('active');
                }

                closeOverlay() {
                    this.overlay.style.display = 'none';
                    this.isOpen = false;
                    this.toggle.classList.remove('active');
                }

                startPolling() {
                    // Обновляем уведомления каждые 30 секунд
                    setInterval(() => this.loadNotifications(), 30000);
                    
                    // Обновляем при фокусе на вкладке
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.loadNotifications();
                        }
                    });
                }

                generateNotificationLinks(data) {
                    let links = '';
                    
                    if (data && data.data) {
                        // Ссылка на клиента
                        if (data.data.client_id) {
                            links += `<a href="/admin/users/${data.data.client_id}" class="btn btn-sm btn-outline-primary me-2 mb-2">
                                <i class="bi bi-person"></i>
                            </a>`;
                        }
                        
                        // Ссылка на питомца
                        if (data.data.pet_id) {
                            links += `<a href="/admin/pets/${data.data.pet_id}" class="btn btn-sm btn-outline-success me-2 mb-2">
                                <i class="bi bi-heart"></i>
                            </a>`;
                        }
                        
                        // Ссылка на приём
                        if (data.data.visit_id) {
                            links += `<a href="/admin/visits/${data.data.visit_id}" class="btn btn-sm btn-outline-info me-2 mb-2">
                                <i class="bi bi-calendar-check"></i>
                            </a>`;
                        }
                    }
                    
                    return links;
                }
            }

            // Инициализируем менеджер уведомлений
            const notificationManager = new NotificationManager();

            // Управление оверлеем пользователя
            const userToggle = document.getElementById('userToggle');
            const userOverlay = document.getElementById('userOverlay');
            let userOverlayOpen = false;

            userToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleUserOverlay();
            });

            function toggleUserOverlay() {
                if (userOverlayOpen) {
                    closeUserOverlay();
                } else {
                    openUserOverlay();
                }
            }

            function openUserOverlay() {
                userOverlay.style.display = 'block';
                userOverlayOpen = true;
                userToggle.classList.add('active');
            }

            function closeUserOverlay() {
                userOverlay.style.display = 'none';
                userOverlayOpen = false;
                userToggle.classList.remove('active');
            }

            // Закрытие при клике по темной области
            userOverlay.addEventListener('click', (e) => {
                if (e.target === userOverlay) {
                    closeUserOverlay();
                }
            });

            // Закрытие при нажатии Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeUserOverlay();
                    notificationManager.closeOverlay();
                }
            });

            // Закрытие при клике вне оверлеев (если нужно)
            document.addEventListener('click', (e) => {
                // Проверяем, что клик не по кнопкам и не по содержимому оверлеев
                if (!e.target.closest('.overlay-content') && 
                    !e.target.closest('#notificationsToggle') && 
                    !e.target.closest('#userToggle')) {
                    closeUserOverlay();
                    notificationManager.closeOverlay();
                }
            });
        });
    </script>
</body>
</html>

