@extends('layouts.client')

@section('title', 'История заказов - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="orders" />

        <!-- Основной контент -->
        <div class="col-12 col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                <h2 class="h3 mb-3 mb-sm-0">История заказов</h2>
                <a href="{{ route('client.services') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Новый заказ
                </a>
            </div>

            <!-- Фильтры -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('client.profile.orders') }}">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="search" class="form-label">Поиск</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Номер заказа или услуга..." value="{{ request('search') }}">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="status" class="form-label">Статус</label>
                                <select class="form-select" id="status" name="status" data-tomselect data-placeholder="Все статусы">
                                    <option value="">Все статусы</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}" 
                                                {{ request('status') == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="date_from" class="form-label">Дата с</label>
                                <input type="text" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}" placeholder="дд.мм.гггг" data-datepicker readonly>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="date_to" class="form-label">Дата по</label>
                                <input type="text" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}" placeholder="дд.мм.гггг" data-datepicker readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-search me-1"></i>Найти
                                </button>
                                <a href="{{ route('client.profile.orders') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Сбросить
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список заказов -->
            @if($orders->count() > 0)
                @foreach($orders as $order)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="card-title mb-0 me-3">
                                        Заказ #{{ $order->id }}
                                    </h5>
                                    <span class="badge 
                                        @if($order->status->name === 'Новый') bg-primary
                                        @elseif($order->status->name === 'Подтвержден') bg-info
                                        @elseif($order->status->name === 'В обработке') bg-warning
                                        @elseif($order->status->name === 'Отправлен') bg-info
                                        @elseif($order->status->name === 'Доставлен') bg-success
                                        @elseif($order->status->name === 'Отменен') bg-danger
                                        @else bg-secondary
                                        @endif">
                                        {{ $order->status->name }}
                                    </span>
                                </div>
                                
                                <div class="row text-muted small">
                                    <div class="col-md-6">
                                        <i class="bi bi-calendar me-1"></i>
                                        <strong>Дата:</strong> {{ $order->created_at->format('d.m.Y H:i') }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-currency-dollar me-1"></i>
                                        <strong>Сумма:</strong> {{ number_format($order->total_amount, 0, ',', ' ') }} ₽
                                    </div>
                                </div>
                                
                                <div class="row text-muted small mt-1">
                                    <div class="col-md-6">
                                        <i class="bi bi-building me-1"></i>
                                        <strong>Филиал:</strong> {{ $order->branch->name }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-box me-1"></i>
                                        <strong>Товаров:</strong> {{ $order->items->count() }} шт.
                                    </div>
                                </div>
                                
                                @if($order->notes)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Примечание:</strong> {{ Str::limit($order->notes, 100) }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            
                            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('client.profile.orders.show', $order) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>Подробнее
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Пагинация -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @else
                <div class="card border-0 bg-light">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                        <h3 class="h4 mb-3">Нет заказов</h3>
                        <p class="text-muted mb-4">
                            @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                По выбранным фильтрам заказы не найдены.
                            @else
                                У вас пока нет заказов.
                            @endif
                        </p>
                        <a href="{{ route('client.services') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Сделать заказ
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация TomSelect и AirDatepicker
    if (typeof window.createTomSelect === 'function') {
        const tomSelectElements = document.querySelectorAll('[data-tomselect]');
        tomSelectElements.forEach(element => {
            const placeholder = element.dataset.placeholder || 'Выберите значение...';
            window.createTomSelect(element, {
                placeholder: placeholder,
            });
        });
    }

    if (typeof window.createDatepicker === 'function') {
        const datepickerElements = document.querySelectorAll('[data-datepicker]');
        datepickerElements.forEach(element => {
            window.createDatepicker(element);
        });
    }
});
</script>
@endpush

