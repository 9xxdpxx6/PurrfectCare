@php
    $notifications = auth()->user()->notifications()->latest()->take(5)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<div class="dropdown">
    <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount }}
                <span class="visually-hidden">непрочитанных уведомлений</span>
            </span>
        @endif
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 300px;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Уведомления</span>
            @if($unreadCount > 0)
                <small class="text-muted">{{ $unreadCount }} новых</small>
            @endif
        </li>
        
        @if($notifications->count() > 0)
            <li><hr class="dropdown-divider"></li>
            @foreach($notifications as $notification)
                <li>
                    <a class="dropdown-item notification-item {{ $notification->unread() ? 'unread' : '' }}" 
                       href="#" 
                       data-notification-id="{{ $notification->id }}"
                       data-bs-toggle="tooltip" 
                       data-bs-placement="top" 
                       title="">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-2">
                                @if($notification->data['type'] === 'appointment_created')
                                    <i class="bi bi-calendar-check text-primary"></i>
                                @elseif($notification->data['type'] === 'pet_added')
                                    <i class="bi bi-heart text-success"></i>
                                @elseif($notification->data['type'] === 'order_created')
                                    <i class="bi bi-bag text-info"></i>
                                @elseif($notification->data['type'] === 'registration_successful')
                                    <i class="bi bi-person-check text-success"></i>
                                @else
                                    <i class="bi bi-bell text-secondary"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold small">{{ $notification->data['title'] ?? 'Уведомление' }}</div>
                                <div class="text-muted small">{{ $notification->data['message'] ?? '' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                            @if($notification->unread())
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary rounded-pill" style="width: 8px; height: 8px;"></span>
                                </div>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
            
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-center" href="{{ route('client.profile.notifications') }}">
                    <i class="bi bi-list-ul me-1"></i>Все уведомления
                </a>
            </li>
        @else
            <li>
                <div class="dropdown-item text-center text-muted py-3">
                    <i class="bi bi-bell-slash display-6 d-block mb-2"></i>
                    <small>Нет уведомлений</small>
                </div>
            </li>
        @endif
    </ul>
</div>

@push('styles')
<style>
.notification-dropdown {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-item:last-child {
    border-bottom: none;
}

.dropdown-item:focus {
    background-color: #e9ecef;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Отключаем тултипы для элементов уведомлений
    document.querySelectorAll('.notification-item').forEach(function(item) {
        // Отключаем тултип
        item.setAttribute('data-bs-toggle', 'tooltip');
        item.setAttribute('data-bs-placement', 'top');
        item.setAttribute('title', '');
    });
    
    // Обработка клика по уведомлению
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.dataset.notificationId;
            
            // Отмечаем уведомление как прочитанное
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Убираем стиль непрочитанного
                    this.classList.remove('unread');
                    this.querySelector('.badge')?.remove();
                    
                    // Обновляем счетчик
                    updateNotificationCount();
                    
                    // Закрываем dropdown
                    const dropdown = bootstrap.Dropdown.getInstance(this.closest('.dropdown').querySelector('[data-bs-toggle="dropdown"]'));
                    if (dropdown) {
                        dropdown.hide();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    
    // Обновление счетчика уведомлений
    function updateNotificationCount() {
        fetch('/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-dropdown .badge');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    } else {
                        // Создаем badge если его нет
                        const button = document.querySelector('.btn[data-bs-toggle="dropdown"]');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        newBadge.textContent = data.count;
                        button.appendChild(newBadge);
                    }
                } else {
                    badge?.remove();
                }
            })
            .catch(error => console.error('Error:', error));
    }
});
</script>
@endpush
