@canany(['settings_analysis_types.read', 'settings_analysis_parameters.read', 'settings_vaccination_types.read', 'settings_statuses.read', 'settings_units.read', 'settings_branches.read', 'settings_specialties.read', 'settings_animal_types.read', 'settings_breeds.read', 'settings_suppliers.read', 'settings_diagnoses.read', 'settings_symptoms.read'])
<ul class="nav flex-column mb-4">
    <li class="nav-item">
        <a class="nav-link d-flex justify-content-between align-items-center text-body" href="#" data-bs-toggle="collapse" data-bs-target="#settingsMenu" aria-expanded="true">
            <span><i class="bi bi-gear me-2"></i>Настройки</span>
            <i class="bi bi-chevron-down collapse-arrow"></i>
        </a>
        <div class="collapse show" id="settingsMenu">
            <ul class="nav flex-column ms-3">
                @can('settings_analysis_types.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-types.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.lab-test-types.index') }}">
                        Типы анализов
                    </a>
                </li>
                @endcan
                @can('settings_analysis_parameters.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.lab-test-params.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.lab-test-params.index') }}">
                        Параметры анализов
                    </a>
                </li>
                @endcan
                @can('settings_vaccination_types.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.vaccination-types.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.vaccination-types.index') }}">
                        Типы вакцинаций
                    </a>
                </li>
                @endcan
                @can('settings_statuses.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.statuses.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.statuses.index') }}">
                        Статусы
                    </a>
                </li>
                @endcan
                @can('settings_units.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.units.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.units.index') }}">
                        Единицы измерений
                    </a>
                </li>
                @endcan
                @can('settings_branches.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.branches.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.branches.index') }}">
                        Филиалы
                    </a>
                </li>
                @endcan
                @can('settings_specialties.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.specialties.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.specialties.index') }}">
                        Специальности
                    </a>
                </li>
                @endcan
                @can('settings_animal_types.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.species.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.species.index') }}">
                        Виды животных
                    </a>
                </li>
                @endcan
                @can('settings_breeds.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.breeds.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.breeds.index') }}">
                        Породы
                    </a>
                </li>
                @endcan
                @can('settings_suppliers.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.suppliers.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.suppliers.index') }}">
                        Поставщики
                    </a>
                </li>
                @endcan
                @can('settings_diagnoses.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.diagnoses.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.diagnoses.index') }}">
                        Диагнозы (словарь)
                    </a>
                </li>
                @endcan
                @can('settings_symptoms.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.symptoms.*') ? 'text-primary active' : 'text-body' }}" href="{{ route('admin.settings.symptoms.index') }}">
                        Симптомы (словарь)
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </li>
</ul>
@endcanany
