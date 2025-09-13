@canany(['employees.read', 'roles.read', 'schedules.read', 'deliveries.read'])
<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#organizationMenu" aria-expanded="true">
            <span><i class="bi bi-building me-2"></i>Организация</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="organizationMenu">
            <ul class="nav flex-column ms-3">
                @can('employees.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.employees.index') }}">
                        Сотрудники
                    </a>
                </li>
                @endcan
                @can('roles.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.roles.index') }}">
                        Роли
                    </a>
                </li>
                @endcan
                @can('schedules.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.schedules.index') }}">
                        Расписания
                    </a>
                </li>
                @endcan
                @can('deliveries.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.drug-procurements.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.drug-procurements.index') }}">
                        Поставки
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
</ul>
@endcanany
