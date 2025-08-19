@extends('layouts.admin')

@section('title', 'Уведомления')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Уведомления</h1>
        <div>
            <button class="btn btn-outline-primary me-2" id="refreshNotifications">
                <i class="bi bi-arrow-clockwise"></i> Обновить
            </button>
            <button class="btn btn-primary" id="markAllAsRead">
                <i class="bi bi-check-all"></i> Отметить все как прочитанные
            </button>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">Все уведомления</option>
                        <option value="unread">Непрочитанные</option>
                        <option value="read">Прочитанные</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter">
                        <option value="">Все типы</option>
                        <option value="bot_booking">Записи через бота</option>
                        <option value="bot_registration">Регистрации через бота</option>
                        <option value="bot_pet_added">Питомцы через бота</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="dateFilter" placeholder="дд.мм.гггг" readonly>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="bi bi-x-circle"></i> Очистить фильтры
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
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 px-3">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary me-3 notification-type"></span>
                    <h6 class="mb-0 notification-title"></h6>
                    <small class="text-muted ms-auto notification-time"></small>
                </div>
                <p class="mb-3 notification-message"></p>
                <div class="notification-details small text-muted mb-3"></div>
                <div class="notification-links">
                    <!-- Ссылки будут добавлены динамически -->
                </div>
            </div>
            <div class="mx-3">
                <button class="btn btn-sm btn-outline-primary mark-read-btn" title="Отметить как прочитанное">
                    <i class="bi bi-check"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Модальное окно для деталей уведомления -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
@endsection

@push('scripts')
<script>
class NotificationsPage {
    constructor() {
        this.currentPage = 1;
        this.perPage = 20;
        this.filters = {
            status: '',
            type: '',
            date: ''
        };
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initDatepicker();
        this.loadNotifications();
                        this.startPolling();
            }

            initDatepicker() {
                try {
                    // Инициализируем Air Datepicker
                    if (typeof createDatepicker === 'function') {
                        this.datepicker = createDatepicker('#dateFilter', 'ru', {
                            dateFormat: 'dd.mm.yyyy',
                            autoClose: true,
                            onSelect: (formattedDate, date, inst) => {
                                if (date) {
                                    this.filters.date = date.toISOString().split('T')[0];
                                } else {
                                    this.filters.date = '';
                                }
                                this.currentPage = 1;
                                this.loadNotifications();
                            }
                        });
                    } else if (typeof AirDatepicker === 'function') {
                        // Альтернативный способ - прямой вызов AirDatepicker
                        this.datepicker = new AirDatepicker('#dateFilter', {
                            locale: 'ru',
                            dateFormat: 'dd.mm.yyyy',
                            autoClose: true,
                            onSelect: ({formattedDate, date, datepicker}) => {
                                if (date) {
                                    this.filters.date = date.toISOString().split('T')[0];
                                } else {
                                    this.filters.date = '';
                                }
                                this.currentPage = 1;
                                this.loadNotifications();
                            }
                        });
                    } else {
                        console.warn('Air Datepicker не найден, используем стандартный input');
                        // Fallback на стандартный input
                        const dateInput = document.getElementById('dateFilter');
                        dateInput.type = 'date';
                        dateInput.addEventListener('change', (e) => {
                            this.filters.date = e.target.value;
                            this.currentPage = 1;
                            this.loadNotifications();
                        });
                    }
                } catch (error) {
                    console.error('Ошибка инициализации datepicker:', error);
                    // Fallback на стандартный input
                    const dateInput = document.getElementById('dateFilter');
                    dateInput.type = 'date';
                    dateInput.addEventListener('change', (e) => {
                        this.filters.date = e.target.value;
                        this.currentPage = 1;
                        this.loadNotifications();
                    });
                }
            }

            setupEventListeners() {
        // Фильтры
        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.filters.status = e.target.value;
            this.currentPage = 1;
            this.loadNotifications();
        });

        document.getElementById('typeFilter').addEventListener('change', (e) => {
            this.filters.type = e.target.value;
            this.currentPage = 1;
            this.loadNotifications();
        });

        document.getElementById('dateFilter').addEventListener('change', (e) => {
            this.filters.date = e.target.value;
            this.currentPage = 1;
            this.loadNotifications();
        });

        document.getElementById('clearFilters').addEventListener('click', () => {
            this.clearFilters();
        });

        // Кнопки
        document.getElementById('refreshNotifications').addEventListener('click', () => {
            this.loadNotifications();
        });

        document.getElementById('markAllAsRead').addEventListener('click', () => {
            this.markAllAsRead();
        });

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

            const response = await fetch(`/admin/notifications?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('Ошибка загрузки уведомлений');
            }

            const data = await response.json();
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
    }

    getTypeLabel(type) {
        const labels = {
            'bot_booking': 'Запись',
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
                if (key !== 'type' && value) {
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
            'pet_name': 'Питомец'
        };
        return labels[key] || key;
    }

    generateNotificationLinks(data) {
        let links = '';
        
        if (data.data) {
            // Ссылка на клиента
            if (data.data.client_id) {
                links += `<a href="/admin/users/${data.data.client_id}" class="btn btn-sm btn-outline-primary me-2 mb-2">
                    <i class="bi bi-person"></i> Клиент
                </a>`;
            }
            
            // Ссылка на питомца
            if (data.data.pet_id) {
                links += `<a href="/admin/pets/${data.data.pet_id}" class="btn btn-sm btn-outline-success me-2 mb-2">
                    <i class="bi bi-heart"></i> Питомец
                </a>`;
            }
            
            // Ссылка на приём
            if (data.data.visit_id) {
                links += `<a href="/admin/visits/${data.data.visit_id}" class="btn btn-sm btn-outline-info me-2 mb-2">
                    <i class="bi bi-calendar-check"></i> Приём
                </a>`;
            }
            
            // Ссылка на ветеринара
            if (data.data.veterinarian_id) {
                links += `<a href="/admin/employees/${data.data.veterinarian_id}" class="btn btn-sm btn-outline-warning me-2 mb-2">
                    <i class="bi bi-stethoscope"></i> Ветеринар
                </a>`;
            }
            
            // Ссылка на филиал
            if (data.data.branch_id) {
                links += `<a href="/admin/branches/${data.data.branch_id}" class="btn btn-sm btn-outline-secondary me-2 mb-2">
                    <i class="bi bi-building"></i> Филиал
                </a>`;
            }
        }
        
        return links;
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/admin/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('opacity-75');
                    item.querySelector('.mark-read-btn').style.display = 'none';
                }
                
                // Обновляем счетчик в header
                if (window.notificationManager) {
                    window.notificationManager.loadNotifications();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                // Обновляем все уведомления на странице
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('opacity-75');
                    item.querySelector('.mark-read-btn').style.display = 'none';
                });

                // Обновляем счетчик в header
                if (window.notificationManager) {
                    window.notificationManager.loadNotifications();
                }

                // Показываем уведомление об успехе
                this.showSuccessMessage('Все уведомления отмечены как прочитанные');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            this.showErrorMessage('Ошибка при отметке уведомлений');
        }
    }

    clearFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('dateFilter').value = '';
        
        // Сбрасываем дату в datepicker
        if (this.datepicker) {
            if (typeof this.datepicker.clear === 'function') {
                this.datepicker.clear();
            } else if (typeof this.datepicker.destroy === 'function') {
                // Если нет метода clear, пересоздаем datepicker
                this.datepicker.destroy();
                this.initDatepicker();
            }
        }
        
        this.filters = {
            status: '',
            type: '',
            date: ''
        };
        
        this.currentPage = 1;
        this.loadNotifications();
    }

    showNotificationDetails(notificationId) {
        // Здесь можно добавить логику для показа деталей уведомления
        // Например, открыть модальное окно с подробной информацией
        console.log('Show details for notification:', notificationId);
    }

    updatePagination(pagination) {
        // Здесь можно добавить пагинацию, если нужно
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
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.notificationsPage = new NotificationsPage();
});
</script>
@endpush

@push('styles')
<style>
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

.notification-details div {
    margin-bottom: 0.25rem;
}

.badge.notification-type {
    font-size: 0.75rem;
}

.notification-links .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.notification-links .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .notification-item {
        padding: 1rem 0.5rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
@endpush
