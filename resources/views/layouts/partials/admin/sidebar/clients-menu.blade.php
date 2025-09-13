@canany(['clients.read', 'pets.read'])
<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#clientsMenu" aria-expanded="true">
            <span><i class="bi bi-people me-2"></i>Пациенты</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="clientsMenu">
            <ul class="nav flex-column ms-3">
                @can('clients.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.users.index') }}">
                        Клиенты
                    </a>
                </li>
                @endcan
                @can('pets.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pets.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.pets.index') }}">
                        Питомцы
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
</ul>
@endcanany
