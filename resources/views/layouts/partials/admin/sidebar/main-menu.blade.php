<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#mainMenu" aria-expanded="true">
            <span><i class="bi bi-house-door me-2"></i>Основное</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="mainMenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.dashboard') }}">
                        Главная
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.orders.index') }}">
                        Заказы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.services.index') }}">
                        Услуги
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
