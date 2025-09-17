@props(['active' => 'profile'])

<div class="col-12 col-lg-3 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <a href="{{ route('client.profile') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'profile' ? 'active' : '' }}">
                    <i class="bi bi-person me-2"></i>Профиль
                </a>
                <a href="{{ route('client.appointment.appointments') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'appointments' ? 'active' : '' }}">
                    <i class="bi bi-calendar-event me-2"></i>Предстоящие записи
                </a>
                <a href="{{ route('client.profile.visits') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'visits' ? 'active' : '' }}">
                    <i class="bi bi-calendar-check me-2"></i>История визитов
                </a>
                <a href="{{ route('client.profile.orders') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'orders' ? 'active' : '' }}">
                    <i class="bi bi-bag me-2"></i>История заказов
                </a>
                <a href="{{ route('client.profile.pets') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'pets' ? 'active' : '' }}">
                    <i class="bi bi-heart me-2"></i>Мои питомцы
                </a>
                <a href="{{ route('client.profile.notifications') }}" 
                   class="list-group-item list-group-item-action {{ $active === 'notifications' ? 'active' : '' }}">
                    <i class="bi bi-bell me-2"></i>Уведомления
                </a>
            </div>
        </div>
    </div>
</div>
