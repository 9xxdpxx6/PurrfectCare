@canany(['statistics_general.read', 'statistics_finance.read', 'statistics_efficiency.read', 'statistics_clients.read', 'statistics_medicine.read', 'statistics_conversion.read'])
<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#statisticsMenu" aria-expanded="true">
            <span><i class="bi bi-graph-up me-2"></i>Статистика</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="statisticsMenu">
            <ul class="nav flex-column ms-3">
                @can('statistics_general.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.dashboard') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.dashboard') }}">
                        Общая статистика
                    </a>
                </li>
                @endcan
                @can('statistics_finance.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.financial') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.financial') }}">
                        Финансы
                    </a>
                </li>
                @endcan
                @can('statistics_efficiency.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.operational') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.operational') }}">
                        Эффективность
                    </a>
                </li>
                @endcan
                @can('statistics_clients.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.clients') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.clients') }}">
                        Клиенты
                    </a>
                </li>
                @endcan
                @can('statistics_medicine.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.medical') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.medical') }}">
                        Медицина
                    </a>
                </li>
                @endcan
                @can('statistics_conversion.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.statistics.conversion') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.statistics.conversion') }}">
                        Конверсия
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
</ul>
@endcanany
