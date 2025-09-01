<div class="sidebar bg-body border-end" id="sidebarMenu">
    <div class="px-3">
        <h4 class="mb-4">Админ-панель</h4>

        <!-- Основное -->
        @include('layouts.partials.admin.sidebar.main-menu')
        
        <!-- Статистика -->
        @include('layouts.partials.admin.sidebar.statistics-menu')
        
        <!-- Пациенты -->
        @include('layouts.partials.admin.sidebar.clients-menu')
        
        <!-- Медицина -->
        @include('layouts.partials.admin.sidebar.medicine-menu')
        
        <!-- Организация -->
        @include('layouts.partials.admin.sidebar.organization-menu')
        
        <!-- Уведомления -->
        @can('notifications.read')
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.notifications.index') }}">
                    <i class="bi bi-bell me-2"></i>Уведомления
                </a>
            </li>
        </ul>
        @endcan
        
        <!-- Настройки -->
        @include('layouts.partials.admin.sidebar.settings-menu')
    </div>
</div>
