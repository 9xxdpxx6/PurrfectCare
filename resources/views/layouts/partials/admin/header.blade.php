<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <button class="navbar-toggler sidebar-toggle d-md-none collapsed me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{{ route('admin.dashboard') }}">PurrfectCare</a>
    <div class="w-100"></div>
    <div class="navbar-nav d-flex flex-row align-items-center">
        <div class="nav-item text-nowrap me-3">
            <button class="theme-switch btn btn-link nav-link px-3 text-white" id="themeSwitch" title="Переключить тему">
                <i class="bi bi-sun-fill d-none"></i>
                <i class="bi bi-moon-fill"></i>
            </button>
        </div>
        @can('notifications.read')
        <div class="nav-item text-nowrap me-3">
            <button class="btn btn-link nav-link px-3 text-white position-relative" type="button" id="notificationsToggle">
                <span class="fs-5"><i class="bi bi-bell-fill"></i></span>
                <span class="position-absolute top-0 mt-2 py-0 px-2 fw-normal start-100 translate-middle badge rounded-pill bg-primary notification-badge" id="notificationBadge" style="display: none;">
                    0
                </span>
            </button>
        </div>
        @endcan
        <div class="nav-item text-nowrap">
            <button class="btn btn-link nav-link px-3 text-white position-relative" type="button" id="profileToggle">
                <span class="fs-5"><i class="bi bi-person-fill"></i></span> {{ Auth::guard('admin')->user()?->name }}
            </button>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</header>
