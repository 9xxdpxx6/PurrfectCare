<!-- Оверлей уведомлений -->
<div id="notificationsOverlay" class="overlay-overlay" style="display: none;">
    <div class="overlay-content notifications-overlay bg-body border rounded shadow overflow-hidden">
        <div class="p-3 border-bottom fw-semibold d-flex justify-content-between align-items-center">
            <h6 class="mb-0 col-6">Уведомления</h6>
            <button class="btn btn-sm btn-link text-decoration-none col-6" id="markAllAsRead">
                Отметить все как прочитанные
            </button>
        </div>
        <div id="notificationsList">
            <div class="text-center text-muted py-3">
                <i class="bi bi-bell-slash"></i>
                <p class="mb-0">Нет новых уведомлений</p>
            </div>
        </div>
        <div class="px-3 py-2 border-top mt-auto bg-body">
            <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none d-block text-body">
                <i class="bi bi-list-ul me-2"></i>Все уведомления
            </a>
        </div>
    </div>
</div>

<!-- Оверлей пользователя -->
<div id="userOverlay" class="overlay-overlay" style="display: none;">
    <div class="overlay-content user-overlay bg-body border rounded shadow overflow-hidden">
        <div class="p-3 border-bottom fw-semibold">
            <h6 class="mb-0">Профиль</h6>
        </div>
        <div class="px-3 py-2">
            <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-decoration-none d-block text-body">
                <i class="bi bi-box-arrow-right me-2"></i>Выйти
            </a>
        </div>
    </div>
</div>
