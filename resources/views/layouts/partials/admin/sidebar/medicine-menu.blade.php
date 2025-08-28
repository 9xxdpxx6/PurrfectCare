<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#medicineMenu" aria-expanded="true">
            <span><i class="bi bi-heart-pulse me-2"></i>Медицина</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="medicineMenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.visits.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.visits.index') }}">
                        Приемы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.vaccinations.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.vaccinations.index') }}">
                        Вакцинации
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.lab-tests.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.lab-tests.index') }}">
                        Анализы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.drugs.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.drugs.index') }}">
                        Препараты
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
