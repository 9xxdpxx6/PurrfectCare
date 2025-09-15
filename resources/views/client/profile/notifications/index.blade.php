@extends('layouts.client')

@section('title', 'Уведомления - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="notifications" />

        <!-- Основной контент -->
        <div class="col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                <h2 class="h3 mb-3 mb-sm-0">Уведомления</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                        <i class="bi bi-check-all me-1"></i>Отметить все как прочитанные
                    </button>
                </div>
            </div>

            <!-- Список уведомлений -->
            @if($notifications->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        @foreach($notifications as $notification)
                        <div class="notification-item p-3 border-bottom {{ $notification->unread() ? 'unread' : '' }}" 
                             data-notification-id="{{ $notification->id }}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    @if($notification->data['type'] === 'appointment_created')
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-calendar-check text-primary"></i>
                                        </div>
                                    @elseif($notification->data['type'] === 'pet_added')
                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-heart text-success"></i>
                                        </div>
                                    @elseif($notification->data['type'] === 'order_created')
                                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-bag text-info"></i>
                                        </div>
                                    @elseif($notification->data['type'] === 'registration_successful')
                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person-check text-success"></i>
                                        </div>
                                    @else
                                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-bell text-secondary"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $notification->data['title'] ?? 'Уведомление' }}</h6>
                                            <p class="mb-1 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-2">
                                            @if($notification->unread())
                                                <span class="badge bg-primary rounded-pill" style="width: 8px; height: 8px;"></span>
                                            @endif
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($notification->unread())
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="markAsRead('{{ $notification->id }}')">
                                                                <i class="bi bi-check me-2"></i>Отметить как прочитанное
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteNotification('{{ $notification->id }}')">
                                                            <i class="bi bi-trash me-2"></i>Удалить
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Пагинация -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="card border-0 bg-light">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-bell-slash display-1 text-muted mb-4"></i>
                        <h3 class="h4 mb-3">Нет уведомлений</h3>
                        <p class="text-muted mb-4">
                            У вас пока нет уведомлений. Здесь будут отображаться важные события и обновления.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.notification-item {
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
    border-bottom: none !important;
}
</style>
@endpush

@push('scripts')
<script>
function markAsRead(notificationId) {
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
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            item.classList.remove('unread');
            item.querySelector('.badge')?.remove();
            
            // Обновляем счетчик в шапке
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Перезагружаем страницу для обновления всех уведомлений
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (confirm('Вы уверены, что хотите удалить это уведомление?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                item.remove();
                
                // Обновляем счетчик в шапке
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function updateNotificationCount() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-dropdown .badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                }
            } else {
                badge?.remove();
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endpush
