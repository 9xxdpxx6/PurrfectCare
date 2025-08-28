<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#statisticsMenu" aria-expanded="true">
            <span><i class="bi bi-graph-up me-2"></i>Статистика</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="statisticsMenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.dashboard') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.dashboard') }}">
                        Общая статистика
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.financial') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.financial') }}">
                        Финансы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.operational') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.operational') }}">
                        Эффективность
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.clients') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.clients') }}">
                        Клиенты
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.medical') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.medical') }}">
                        Медицина
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.conversion') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.conversion') }}">
                        Конверсия
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
