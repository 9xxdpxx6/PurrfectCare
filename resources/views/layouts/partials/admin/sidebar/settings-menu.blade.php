<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="true">
            <span><i class="bi bi-gear me-2"></i>Настройки</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="settingsMenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-types.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.lab-test-types.index') }}">
                        Типы анализов
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-params.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.lab-test-params.index') }}">
                        Параметры анализов
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.vaccination-types.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.vaccination-types.index') }}">
                        Типы вакцинаций
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.statuses.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.statuses.index') }}">
                        Статусы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.units.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.units.index') }}">
                        Единицы измерений
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.branches.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.branches.index') }}">
                        Филиалы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.specialties.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.specialties.index') }}">
                        Специальности
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.species.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.species.index') }}">
                        Виды животных
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.breeds.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.breeds.index') }}">
                        Породы
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.suppliers.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.suppliers.index') }}">
                        Поставщики
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.diagnoses.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.diagnoses.index') }}">
                        Диагнозы (словарь)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.symptoms.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.symptoms.index') }}">
                        Симптомы (словарь)
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
