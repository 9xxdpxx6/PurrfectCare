@extends('layouts.admin')

@section('title', 'Уведомления')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@can('notifications.read')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start align-items-sm-center mb-4">
        <h1 class="h3 mb-3 mb-sm-0">Уведомления</h1>
        <div class="page-actions d-flex flex-row flex-nowrap gap-2 ms-sm-auto">
            <button type="button" class="btn btn-outline-primary" id="refreshNotifications">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="d-none d-md-inline ms-1">Обновить</span>
            </button>
            <button type="button" class="btn btn-primary" id="markAllAsRead">
                <i class="bi bi-check-all"></i>
                <span class="d-none d-md-inline ms-1">Отметить все</span>
            </button>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 justify-content-center">
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="statusFilter" class="form-label">Статус</label>
                    <select class="form-select form-select-sm" id="statusFilter">
                        <option value="">Все уведомления</option>
                        <option value="unread">Непрочитанные</option>
                        <option value="read">Прочитанные</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="typeFilter" class="form-label">Тип</label>
                    <select class="form-select form-select-sm" id="typeFilter">
                        <option value="">Все типы</option>
                        <option value="bot_booking">Записи через бота</option>
                        <option value="bot_registration">Регистрации через бота</option>
                        <option value="bot_pet_added">Питомцы через бота</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="sortFilter" class="form-label">Сортировка</label>
                    <select class="form-select form-select-sm" id="sortFilter">
                        <option value="created_desc">Сначала новые</option>
                        <option value="created_asc">Сначала старые</option>
                        <option value="read_desc">Сначала прочитанные</option>
                        <option value="read_asc">Сначала непрочитанные</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="dateFromFilter" class="form-label">Дата от</label>
                    <input type="text" class="form-control form-control-sm" id="dateFromFilter" placeholder="дд.мм.гггг" readonly>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                    <label for="dateToFilter" class="form-label">Дата до</label>
                    <input type="text" class="form-control form-control-sm" id="dateToFilter" placeholder="дд.мм.гггг" readonly>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="clearFilters">
                        <i class="bi bi-x-circle"></i> 
                        <span class="d-none d-lg-inline ms-2">Сбросить</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Список уведомлений -->
    <div class="card">
        <div class="card-body">
            <div id="notificationsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-2 text-muted">Загружаем уведомления...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Шаблон уведомления -->
<template id="notificationTemplate">
    <div class="notification-item border-bottom py-3" data-id="">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start w-100">
            <div class="flex-grow-1 px-2 px-md-3 w-100">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-3 w-100">
                    <div class="d-flex align-items-center mb-2 mb-sm-0">
                        <span class="badge bg-primary me-2 me-sm-3 notification-type"></span>
                        <h6 class="mb-0 notification-title"></h6>
                    </div>
                    <small class="text-muted ms-auto notification-time"></small>
                </div>
                <p class="mb-3 notification-message"></p>
                <div class="notification-details small text-muted mb-3"></div>
                <div class="notification-links d-flex flex-column flex-sm-row gap-2 w-100">
                    <!-- Ссылки будут добавлены динамически -->
                </div>
            </div>
            <div class="ms-2 ms-md-3 me-3 mt-2 mt-md-0 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-primary mark-read-btn" data-bs-toggle="tooltip" data-bs-title="Отметить как прочитанное">
                    <i class="bi bi-check"></i>
                    <span class="d-inline d-md-none ms-1">Прочитать</span>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Модальное окно для деталей уведомления -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали уведомления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Содержимое будет загружено динамически -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
class NotificationsPage {
    constructor() {
        this.currentPage = 1;
        this.perPage = 25; // Увеличиваем до 25 элементов на страницу
        this.filters = {
            status: '',
            type: '',
            dateFrom: '',
            dateTo: '',
            sort: 'created_desc'
        };
        this.csrfToken = this.getCsrfToken();
        this.init();
    }

    init() {
        if (!this.csrfToken) {
            this.showErrorMessage('Ошибка безопасности: CSRF токен не найден. Уведомления могут работать некорректно.');
        }
        
        this.setupEventListeners();
        this.initDatepicker();
        this.loadNotifications();
        this.startPolling();
        this.initTooltips();
    }

    initTooltips() {
            // Инициализируем Bootstrap тултипы
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) {
            console.error('CSRF token not found!');
            return null;
        }
        return token.getAttribute('content');
    }

    initDatepicker() {
        if (typeof window.createDatepicker === 'function') {
            // Datepicker для даты "от"
            this.datepickerFrom = createDatepicker('#dateFromFilter', {
                dateFormat: 'dd.MM.yyyy',
                autoClose: true,
                onSelect: ({formattedDate, date, datepicker}) => {
                    if (date) {
                        // Формируем дату в локальной временной зоне, чтобы избежать смещения UTC
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        this.filters.dateFrom = `${year}-${month}-${day}`;
                    } else {
                        this.filters.dateFrom = '';
                    }
                    this.currentPage = 1;
                    this.loadNotifications();
                }
            });
            
            // Datepicker для даты "до"
            this.datepickerTo = createDatepicker('#dateToFilter', {
                dateFormat: 'dd.MM.yyyy',
                autoClose: true,
                onSelect: ({formattedDate, date, datepicker}) => {
                    if (date) {
                        // Формируем дату в локальной временной зоне, чтобы избежать смещения UTC
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        this.filters.dateTo = `${year}-${month}-${day}`;
                    } else {
                        this.filters.dateTo = '';
                    }
                    this.currentPage = 1;
                    this.loadNotifications();
                }
            });
        } else {
            // Fallback для стандартных input
            const dateFromInput = document.getElementById('dateFromFilter');
            const dateToInput = document.getElementById('dateToFilter');
            
            if (dateFromInput) {
                dateFromInput.addEventListener('change', () => {
                    this.filters.dateFrom = dateFromInput.value;
                    this.currentPage = 1;
                    this.loadNotifications();
                });
            }
            
            if (dateToInput) {
                dateToInput.addEventListener('change', () => {
                    this.filters.dateTo = dateToInput.value;
                    this.currentPage = 1;
                    this.loadNotifications();
                });
            }
        }
    }

    setupEventListeners() {
        
        // Фильтры
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        const sortFilter = document.getElementById('sortFilter');
        const clearFiltersBtn = document.getElementById('clearFilters');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filters.status = e.target.value;
                this.currentPage = 1;
                this.loadNotifications();
            });
        }
        
        if (typeFilter) {
            typeFilter.addEventListener('change', (e) => {
                this.filters.type = e.target.value;
                this.currentPage = 1;
                this.loadNotifications();
            });
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', (e) => {
                this.filters.sort = e.target.value;
                this.currentPage = 1;
                this.loadNotifications();
            });
        }
        
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                this.clearFilters();
            });
        }

        // Кнопки
        const refreshBtn = document.getElementById('refreshNotifications');
        const markAllBtn = document.getElementById('markAllAsRead');
        
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadNotifications();
            });
        }
        
        if (markAllBtn) {
            markAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            });
        }

        // Обработка кликов по уведомлениям
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const notificationItem = e.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;
                this.showNotificationDetails(notificationId);
            }

            if (e.target.closest('.mark-read-btn')) {
                e.stopPropagation();
                const notificationItem = e.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;
                this.markAsRead(notificationId);
            }
            
            // Проверяем, кликнули ли по кнопке "Отметить все"
            if (e.target.closest('#markAllAsRead')) {
                e.preventDefault();
                e.stopPropagation();
                this.markAllAsRead();
            }
        });
        
    }

    async loadNotifications() {
        try {
            const container = document.getElementById('notificationsContainer');
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-2 text-muted">Загружаем уведомления...</p>
                </div>
            `;

            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...this.filters
            });

            const response = await fetch(`/admin/notifications/data?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки уведомлений');
            }

            const data = await response.json();
            
            // Проверяем, что получили данные
            if (!data.notifications || !Array.isArray(data.notifications)) {
                throw new Error('Неверный формат данных уведомлений');
            }

            this.renderNotifications(data.notifications);
            this.updatePagination(data.pagination);

        } catch (error) {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationsContainer').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-danger">Ошибка загрузки уведомлений</p>
                    <button class="btn btn-primary" onclick="notificationsPage.loadNotifications()">
                        Попробовать снова
                    </button>
                </div>
            `;
        }
    }
    
    // Метод для загрузки только недавних уведомлений (для попапа)
    async loadRecentNotifications(limit = 5) {
        try {
            const params = new URLSearchParams({
                page: 1,
                per_page: limit,
                sort: 'created_desc'
            });

            const response = await fetch(`/admin/notifications/data?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки недавних уведомлений');
            }

            const data = await response.json();
            return data.notifications;

        } catch (error) {
            console.error('Error loading recent notifications:', error);
            return [];
        }
    }

    renderNotifications(notifications) {
        const container = document.getElementById('notificationsContainer');
        
        if (notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-muted">Нет уведомлений</p>
                </div>
            `;
            return;
        }

        const template = document.getElementById('notificationTemplate');
        container.innerHTML = '';

        notifications.forEach(notification => {
            const clone = template.content.cloneNode(true);
            const item = clone.querySelector('.notification-item');
            
            item.dataset.id = notification.id;
            item.classList.toggle('unread', !notification.read_at);
            
            if (notification.read_at) {
                item.classList.add('opacity-75');
            }

            // Заполняем данные
            item.querySelector('.notification-type').textContent = this.getTypeLabel(notification.data.type);
            item.querySelector('.notification-title').textContent = notification.data.title;
            item.querySelector('.notification-message').textContent = notification.data.message;
            
            item.querySelector('.notification-time').textContent = this.formatTime(notification.created_at);
            
            // Детали уведомления
            const details = item.querySelector('.notification-details');
            details.innerHTML = this.formatNotificationDetails(notification.data);

            // Добавляем ссылки на созданные элементы
            const links = item.querySelector('.notification-links');
            links.innerHTML = this.generateNotificationLinks(notification.data);

            // Скрываем кнопку для прочитанных
            if (notification.read_at) {
                item.querySelector('.mark-read-btn').style.display = 'none';
            }

            container.appendChild(clone);
        });
        
        // Инициализируем тултипы для новых элементов
        this.initTooltips();
    }

    getTypeLabel(type) {
        const labels = {
            'bot_booking': 'Запись',
            'website_booking': 'Запись',
            'bot_registration': 'Регистрация',
            'bot_pet_added': 'Питомец'
        };
        return labels[type] || type;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // меньше минуты
            return 'Только что';
        } else if (diff < 3600000) { // меньше часа
            const minutes = Math.floor(diff / 60000);
            return `${minutes} мин. назад`;
        } else if (diff < 86400000) { // меньше дня
            const hours = Math.floor(diff / 3600000);
            return `${hours} ч. назад`;
        } else {
            return date.toLocaleDateString('ru-RU');
        }
    }

    formatNotificationDetails(data) {
        let details = '';
        
        if (data.data) {
            Object.entries(data.data).forEach(([key, value]) => {
                // Пропускаем технические поля, ID и пустые значения
                if (key !== 'type' && 
                    value && 
                    !key.endsWith('_id') && 
                    !key.includes('id') &&
                    key !== 'visit_id' &&
                    key !== 'pet_id' &&
                    key !== 'user_id') {
                    const label = this.getFieldLabel(key);
                    details += `<div class="mb-1"><strong>${label}:</strong> ${value}</div>`;
                }
            });
        }
        
        return details;
    }

    getFieldLabel(key) {
        const labels = {
            'client_name': 'Клиент',
            'veterinarian_name': 'Ветеринар',
            'appointment_time': 'Время приема',
            'appointment_date': 'Дата приема',
            'branch_name': 'Филиал',
            'pet_name': 'Питомец',
            'species_name': 'Вид животного',
            'breed_name': 'Порода',
            'phone': 'Телефон',
            'email': 'Email',
            'address': 'Адрес',
            'telegram': 'Telegram'
        };
        return labels[key] || key;
    }

    generateNotificationLinks(data) {
        let links = '';
        
        if (data.data && data.type) {
            switch (data.type) {
                case 'bot_booking':
                    // Для записи через бота - ссылка на конкретный приём
                    if (data.data.visit_id) {
                        links += `<a href="/admin/visits/${data.data.visit_id}" class="btn btn-sm btn-outline-info me-2 mb-2">
                            <i class="bi bi-calendar-check"></i> Просмотр приёма
                        </a>`;
                    }
                    // Общая ссылка на все приёмы
                    links += `<a href="/admin/visits" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-list"></i> Все приёмы
                    </a>`;
                    break;
                    
                case 'bot_pet_added':
                    // Для добавления питомца - ссылка на конкретного питомца
                    if (data.data.pet_id) {
                        links += `<a href="/admin/pets/${data.data.pet_id}" class="btn btn-sm btn-outline-success me-2 mb-2">
                            <i class="bi bi-heart"></i> Просмотр питомца
                        </a>`;
                    }
                    // Общая ссылка на всех питомцев
                    links += `<a href="/admin/pets" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-list"></i> Все питомцы
                    </a>`;
                    break;
                    
                case 'bot_registration':
                    // Для регистрации - ссылка на конкретного клиента
                    if (data.data.user_id) {
                        links += `<a href="/admin/users/${data.data.user_id}" class="btn btn-sm btn-outline-primary me-2 mb-2">
                            <i class="bi bi-person"></i> Просмотр клиента
                        </a>`;
                    }
                    // Общая ссылка на всех клиентов
                    links += `<a href="/admin/users" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-list"></i> Все клиенты
                    </a>`;
                    break;
                    
                case 'website_booking':
                    // Для записи через сайт - ссылка на конкретный приём
                    if (data.data.visit_id) {
                        links += `<a href="/admin/visits/${data.data.visit_id}" class="btn btn-sm btn-outline-info me-2 mb-2">
                            <i class="bi bi-calendar-check"></i> Просмотр приёма
                        </a>`;
                    }
                    // Общая ссылка на все приёмы
                    links += `<a href="/admin/visits" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-list"></i> Все приёмы
                    </a>`;
                    break;
            }
        }
        
        return links;
    }

    async markAsRead(notificationId) {
        try {
            // Проверяем CSRF токен
            if (!this.csrfToken) {
                this.showErrorMessage('Ошибка безопасности: CSRF токен не найден');
                return;
            }

            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (!item) return;

            // Показываем индикатор загрузки на кнопке
            const markReadBtn = item.querySelector('.mark-read-btn');
            if (markReadBtn) {
                const originalContent = markReadBtn.innerHTML;
                markReadBtn.disabled = true;
                markReadBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            }

            const response = await fetch(`/admin/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            if (response.ok) {
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('opacity-75');
                    if (markReadBtn) {
                        markReadBtn.style.display = 'none';
                    }
                }
                
                // Обновляем счетчик в header
                this.updateHeaderCounter();
            } else {
                // Если ответ не успешный, показываем ошибку
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Ошибка при отметке уведомления';
                this.showErrorMessage(errorMessage);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
            this.showErrorMessage('Ошибка при отметке уведомления: ' + error.message);
        } finally {
            // Восстанавливаем кнопку в случае ошибки
            const item = document.querySelector(`[data-id="${notificationId}"]`);
            if (item) {
                const markReadBtn = item.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.disabled = false;
                    markReadBtn.innerHTML = '<i class="bi bi-check"></i>';
                }
            }
        }
    }

    async markAllAsRead() {
        try {
            // Проверяем CSRF токен
            if (!this.csrfToken) {
                this.showErrorMessage('Ошибка безопасности: CSRF токен не найден');
                return;
            }

            // Показываем индикатор загрузки
            const button = document.getElementById('markAllAsRead');
            if (!button) {
                console.error('Button markAllAsRead not found!');
                return;
            }
            
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Обработка...';

            const response = await fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (response.ok) {
                // Обновляем все уведомления на странице
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('opacity-75');
                    const markReadBtn = item.querySelector('.mark-read-btn');
                    if (markReadBtn) {
                        markReadBtn.style.display = 'none';
                    }
                });

                // Обновляем счетчик в header
                this.updateHeaderCounter();

                // Показываем уведомление об успехе
                this.showSuccessMessage('Все уведомления отмечены как прочитанные');
                
                // Перезагружаем список уведомлений для обновления данных
                this.loadNotifications();
            } else {
                // Если ответ не успешный, показываем ошибку
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || 'Ошибка при отметке уведомлений';
                this.showErrorMessage(errorMessage);
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showErrorMessage('Ошибка при отметке уведомлений: ' + error.message);
        } finally {
            // Восстанавливаем кнопку
            const button = document.getElementById('markAllAsRead');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-check-all"></i> Отметить все';
            }
        }
    }

    clearFilters() {
        // Сбрасываем значения фильтров
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('sortFilter').value = 'created_desc';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';
        
        // Сбрасываем даты в datepicker'ах
        if (this.datepickerFrom) {
            if (typeof this.datepickerFrom.clear === 'function') {
                this.datepickerFrom.clear();
            } else if (typeof this.datepickerFrom.destroy === 'function') {
                this.datepickerFrom.destroy();
                this.initDatepicker();
            }
        }
        if (this.datepickerTo) {
            if (typeof this.datepickerTo.clear === 'function') {
                this.datepickerTo.clear();
            } else if (typeof this.datepickerTo.destroy === 'function') {
                this.datepickerTo.destroy();
                this.initDatepicker();
            }
        }
        
        // Сбрасываем фильтры в объекте
        this.filters = {
            status: '',
            type: '',
            dateFrom: '',
            dateTo: '',
            sort: 'created_desc'
        };
        
        // Сбрасываем на первую страницу и загружаем уведомления
        this.currentPage = 1;
        this.loadNotifications();
    }

    showNotificationDetails(notificationId) {
        // Здесь можно добавить логику для показа деталей уведомления
        // Например, открыть модальное окно с подробной информацией
    }

    updatePagination(pagination) {
        const container = document.getElementById('notificationsContainer');
        
        if (!pagination || pagination.total <= this.perPage) {
            return; // Пагинация не нужна
        }
        
        // Создаем пагинацию
        const paginationHtml = this.createPaginationHTML(pagination);
        
        // Добавляем после списка уведомлений
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'mt-4';
        paginationContainer.innerHTML = paginationHtml;
        
        // Удаляем старую пагинацию если есть
        const oldPagination = container.nextElementSibling;
        if (oldPagination && oldPagination.classList.contains('mt-4')) {
            oldPagination.remove();
        }
        
        container.after(paginationContainer);
        
        // Добавляем обработчики для кнопок пагинации
        this.setupPaginationHandlers(pagination);
    }
    
    createPaginationHTML(pagination) {
        const { current_page, last_page, total, from, to } = pagination;
        
        let paginationHtml = `
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <div class="text-muted mb-2 mb-sm-0 small">
                    Показано ${from}-${to} из ${total} уведомлений
                </div>
                <nav aria-label="Навигация по страницам">
                    <ul class="pagination pagination-sm mb-0">
        `;
        
        // Кнопка "Предыдущая"
        if (current_page > 1) {
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" data-page="${current_page - 1}" aria-label="Предыдущая">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </li>
            `;
        }
        
        // Номера страниц
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);
        
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" data-page="1">1</button>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === current_page ? 'active' : ''}">
                    <button class="page-link" data-page="${i}">${i}</button>
                </li>
            `;
        }
        
        if (endPage < last_page) {
            if (endPage < last_page - 1) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" data-page="${last_page}">${last_page}</button>
                </li>
            `;
        }
        
        // Кнопка "Следующая"
        if (current_page < last_page) {
            paginationHtml += `
                <li class="page-item">
                    <button class="page-link" data-page="${current_page + 1}" aria-label="Следующая">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </li>
            `;
        }
        
        paginationHtml += `
                    </ul>
                </nav>
            </div>
        `;
        
        return paginationHtml;
    }
    
    setupPaginationHandlers(pagination) {
        const paginationContainer = document.querySelector('.pagination');
        if (!paginationContainer) return;
        
        paginationContainer.addEventListener('click', (e) => {
            if (e.target.closest('.page-link') && !e.target.closest('.page-item.disabled')) {
                e.preventDefault();
                const pageLink = e.target.closest('.page-link');
                const page = pageLink.dataset.page;
                
                if (page && page !== this.currentPage.toString()) {
                    this.currentPage = parseInt(page);
                    this.loadNotifications();
                    
                    // Прокручиваем к началу списка
                    document.getElementById('notificationsContainer').scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                }
            }
        });
    }

    startPolling() {
        // Обновляем каждые 30 секунд
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    showSuccessMessage(message) {
        // Простое уведомление об успехе
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    showErrorMessage(message) {
        // Простое уведомление об ошибке
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    updateHeaderCounter() {
        // Обновляем счетчик в header через notificationManager
        if (window.notificationManager) {
            window.notificationManager.loadNotifications();
        }
        
        // Также можно обновить счетчик напрямую через API
        this.loadUnreadCount();
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('/admin/notifications/recent', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    updateNotificationBadge(count) {
        // Обновляем бейдж с количеством непрочитанных уведомлений
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.notificationsPage = new NotificationsPage();
});

// Глобальный метод для получения недавних уведомлений (для попапа)
window.getRecentNotifications = async function(limit = 5) {
    if (window.notificationsPage) {
        return await window.notificationsPage.loadRecentNotifications(limit);
    }
    return [];
};
</script>
@endpush

@push('styles')
<style>
/* Основные стили уведомлений */
.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.notification-item.unread {
    background-color: rgba(13, 110, 253, 0.05);
    border-left: 3px solid #0d6efd;
}

.notification-item.unread:hover {
    background-color: rgba(13, 110, 253, 0.1);
}

/* Анимации */
.notification-item {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover эффекты для кнопок */
.notification-links .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Темная тема */
@media (prefers-color-scheme: dark) {
    .notification-item:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .notification-item.unread {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .notification-item.unread:hover {
        background-color: rgba(13, 110, 253, 0.15);
    }
}

/* Компактные кнопки в шапке на мобильных */
@media (max-width: 767.98px) {
    .page-actions .btn {
        padding: 0.35rem 0.5rem;
    }
}
</style>
@endpush
