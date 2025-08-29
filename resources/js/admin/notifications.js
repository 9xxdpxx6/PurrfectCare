// Система уведомлений для админ-панели
document.addEventListener('DOMContentLoaded', function() {
    // Система уведомлений
    class NotificationManager {
        constructor() {
            this.badge = document.getElementById('notificationBadge');
            this.list = document.getElementById('notificationsList');
            this.markAllBtn = document.getElementById('markAllAsRead');
            this.overlay = document.getElementById('notificationsOverlay');
            this.toggle = document.getElementById('notificationsToggle');
            this.isOpen = false;
            
            if (this.badge && this.list && this.markAllBtn && this.overlay && this.toggle) {
                this.init();
            }
        }

        init() {
            this.loadNotifications();
            this.setupEventListeners();
            this.startPolling();
        }

        setupEventListeners() {
            if (this.markAllBtn) {
                this.markAllBtn.addEventListener('click', () => this.markAllAsRead());
            }
            
            // Переключатель оверлея уведомлений
            if (this.toggle) {
                this.toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleOverlay();
                });
            }

            // Закрытие при клике по темной области
            if (this.overlay) {
                this.overlay.addEventListener('click', (e) => {
                    if (e.target === this.overlay) {
                        this.closeOverlay();
                    }
                });
            }

            // Предотвращаем закрытие при скроллинге
            if (this.list) {
                this.list.addEventListener('scroll', (e) => {
                    e.stopPropagation();
                });
            }

            // Закрытие при нажатии Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeOverlay();
                }
            });
        }

        async loadNotifications() {
            try {
                const response = await fetch('/admin/notifications/recent');
                if (response.ok) {
                    const data = await response.json();
                    this.updateBadge(data.unread_count);
                    this.updateNotificationsList(data.notifications);
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        }

        updateBadge(count) {
            if (this.badge) {
                if (count > 0) {
                    this.badge.textContent = count;
                    this.badge.style.display = 'block';
                } else {
                    this.badge.style.display = 'none';
                }
            }
        }

        updateNotificationsList(notifications) {
            if (!this.list) return;
            
            if (notifications.length === 0) {
                this.list.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-bell-slash"></i>
                        <p class="mb-0">Нет новых уведомлений</p>
                    </div>
                `;
                return;
            }

            this.list.innerHTML = notifications.map(notification => {
                return `
                <div class="border-bottom p-3 ${notification.read_at ? 'text-muted' : ''}" 
                     data-notification-id="${notification.id}"
                     style="cursor: pointer; transition: background-color 0.2s ease;">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${notification.data.title}</div>
                            <div class="small">${notification.data.message}</div>
                            <div class="text-muted small">
                                ${new Date(notification.created_at).toLocaleString('ru-RU')}
                            </div>
                            <div class="notification-links mt-2">
                                ${this.generateNotificationLinks(notification.data)}
                            </div>
                        </div>
                        ${!notification.read_at ? '<span class="badge bg-primary ms-2">Новое</span>' : ''}
                    </div>
                </div>
            `;
            }).join('');

            // Добавляем обработчики кликов и hover эффекты
            this.list.querySelectorAll('[data-notification-id]').forEach(item => {
                item.addEventListener('click', () => this.markAsRead(item.dataset.notificationId));
                
                // Добавляем hover эффект с поддержкой темной темы
                item.addEventListener('mouseenter', () => {
                    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    item.classList.add(isDark ? 'bg-dark' : 'bg-light');
                });
                item.addEventListener('mouseleave', () => {
                    item.classList.remove('bg-light', 'bg-dark');
                });
            });
        }

        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/admin/notifications/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.loadNotifications(); // Перезагружаем список
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        }

        async markAllAsRead() {
            try {
                const response = await fetch('/admin/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.loadNotifications(); // Перезагружаем список
                }
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error);
            }
        }

        toggleOverlay() {
            if (this.isOpen) {
                this.closeOverlay();
            } else {
                this.openOverlay();
            }
        }

        openOverlay() {
            if (this.overlay) {
                this.overlay.style.display = 'block';
                this.isOpen = true;
                if (this.toggle) this.toggle.classList.add('active');
            }
        }

        closeOverlay() {
            if (this.overlay) {
                this.overlay.style.display = 'none';
                this.isOpen = false;
                if (this.toggle) this.toggle.classList.remove('active');
            }
        }

        startPolling() {
            // Обновляем уведомления каждые 30 секунд
            setInterval(() => this.loadNotifications(), 30000);
            
            // Обновляем при фокусе на вкладке
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.loadNotifications();
                }
            });
        }

        generateNotificationLinks(data) {
            let links = '';
            
            if (data && data.data) {
                // Ссылка на клиента - проверяем разные возможные ключи
                let clientId = null;
                
                // Ищем ID клиента в разных возможных местах
                if (data.data.client_id) {
                    clientId = data.data.client_id;
                } else if (data.data.user_id) {
                    clientId = data.data.user_id;
                } else if (data.data.user && data.data.user.id) {
                    clientId = data.data.user.id;
                } else if (data.data.client && data.data.client.id) {
                    clientId = data.data.client.id;
                }
                
                if (clientId) {
                    links += `<a href="/admin/users/${clientId}" class="btn btn-sm btn-outline-primary me-2 mb-2" title="Просмотр клиента">
                        <i class="bi bi-person"></i>
                    </a>`;
                }
                
                // Ссылка на питомца
                if (data.data.pet_id) {
                    links += `<a href="/admin/pets/${data.data.pet_id}" class="btn btn-sm btn-outline-success me-2 mb-2" title="Просмотр питомца">
                        <i class="bi bi-heart"></i>
                    </a>`;
                }
                
                // Ссылка на приём
                if (data.data.visit_id) {
                    links += `<a href="/admin/visits/${data.data.visit_id}" class="btn btn-sm btn-outline-info me-2 mb-2" title="Просмотр приёма">
                        <i class="bi bi-calendar-check"></i>
                    </a>`;
                }
            }
            
            return links;
        }
    }

    // Инициализируем менеджер уведомлений
    const notificationManager = new NotificationManager();

    // Управление оверлеем профиля
    const profileToggle = document.getElementById('profileToggle');
    const profileOverlay = document.getElementById('profileOverlay');
    let profileOverlayOpen = false;

    if (profileToggle && profileOverlay) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleProfileOverlay();
        });

        function toggleProfileOverlay() {
            if (profileOverlayOpen) {
                closeProfileOverlay();
            } else {
                openProfileOverlay();
            }
        }

        function openProfileOverlay() {
            profileOverlay.style.display = 'block';
            profileOverlayOpen = true;
            profileToggle.classList.add('active');
        }

        function closeProfileOverlay() {
            profileOverlay.style.display = 'none';
            profileOverlayOpen = false;
            profileToggle.classList.remove('active');
        }

        // Закрытие при клике по темной области
        profileOverlay.addEventListener('click', (e) => {
            if (e.target === profileOverlay) {
                closeProfileOverlay();
            }
        });

        // Закрытие при нажатии Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeProfileOverlay();
                if (notificationManager) notificationManager.closeOverlay();
            }
        });

        // Закрытие при клике вне оверлеев (если нужно)
        document.addEventListener('click', (e) => {
            // Проверяем, что клик не по кнопкам и не по содержимому оверлеев
            if (!e.target.closest('.overlay-content') && 
                !e.target.closest('#notificationsToggle') && 
                !e.target.closest('#profileToggle')) {
                closeProfileOverlay();
                if (notificationManager) notificationManager.closeOverlay();
            }
        });
    }
});
