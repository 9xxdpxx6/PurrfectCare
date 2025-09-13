@canany(['main.read', 'orders.read', 'services.read'])
<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-expanded="true">
            <span><i class="bi bi-house-door me-2"></i>Основное</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="mainMenu">
            <ul class="nav flex-column ms-3">
                @can('main.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.dashboard') }}">
                        Главная
                    </a>
                </li>
                @endcan
                @can('orders.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.orders.index') }}">
                        Заказы
                    </a>
                </li>
                @endcan
                @can('services.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.services.index') }}">
                        Услуги
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
</ul>
@endcanany
