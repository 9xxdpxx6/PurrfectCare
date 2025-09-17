@extends('layouts.client')

@section('title', 'Детали заказа - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-12 col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('client.profile') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-person me-2"></i>Профиль
                        </a>
                        <a href="{{ route('client.profile.visits') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check me-2"></i>История визитов
                        </a>
                        <a href="{{ route('client.profile.orders') }}" class="list-group-item list-group-item-action active">
                            <i class="bi bi-bag me-2"></i>История заказов
                        </a>
                        <a href="{{ route('client.appointment.appointments') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-plus me-2"></i>Новая запись
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="bi bi-heart me-2"></i>Мои питомцы
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-12 col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">Заказ #{{ $order->id }}</h2>
                <a href="{{ route('client.profile.orders') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>

            <!-- Уведомления -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                @php
                    $fieldErrors = ['reason'];
                    $generalErrors = [];
                    foreach ($errors->keys() as $key) {
                        if (!in_array($key, $fieldErrors)) {
                            $generalErrors = array_merge($generalErrors, $errors->get($key));
                        }
                    }
                @endphp
                @if (!empty($generalErrors))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Ошибка:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($generalErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif

            <!-- Информация о заказе -->
            <div class="row">
                <!-- Основная информация -->
                <div class="col-12 col-md-8">
                    <!-- Статус заказа -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Статус заказа
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="badge 
                                    @if($order->status->name === 'Новый') bg-primary
                                    @elseif($order->status->name === 'Подтвержден') bg-info
                                    @elseif($order->status->name === 'В обработке') bg-warning
                                    @elseif($order->status->name === 'Отправлен') bg-info
                                    @elseif($order->status->name === 'Доставлен') bg-success
                                    @elseif($order->status->name === 'Отменен') bg-danger
                                    @else bg-secondary
                                    @endif fs-6 me-3">
                                    {{ $order->status->name }}
                                </span>
                                <span class="text-muted">
                                    @if($order->status->name === 'Новый')
                                        Заказ создан и ожидает подтверждения
                                    @elseif($order->status->name === 'Подтвержден')
                                        Заказ подтвержден и принят в работу
                                    @elseif($order->status->name === 'В обработке')
                                        Заказ обрабатывается
                                    @elseif($order->status->name === 'Отправлен')
                                        Заказ отправлен
                                    @elseif($order->status->name === 'Доставлен')
                                        Заказ доставлен
                                    @elseif($order->status->name === 'Отменен')
                                        Заказ отменен
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Товары в заказе -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-box me-2"></i>Товары в заказе
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Услуга</th>
                                            <th class="text-center">Количество</th>
                                            <th class="text-end">Цена</th>
                                            <th class="text-end">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $item->item->name ?? 'Без названия' }}</strong>
                                                    @if($item->item->description ?? false)
                                                        <br><small class="text-muted">{{ Str::limit($item->item->description, 100) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->price, 0, ',', ' ') }} ₽</td>
                                            <td class="text-end">{{ number_format($item->total, 0, ',', ' ') }} ₽</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Итого:</th>
                                            <th class="text-end">{{ number_format($order->total_amount, 0, ',', ' ') }} ₽</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Информация о заказе -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-file-text me-2"></i>Информация о заказе
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Дата создания</label>
                                    <p class="fw-bold">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Последнее обновление</label>
                                    <p class="fw-bold">{{ $order->updated_at->format('d.m.Y H:i') }}</p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Филиал</label>
                                    <p class="fw-bold">{{ $order->branch->name }}</p>
                                    <small class="text-muted">{{ $order->branch->address }}</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Общая сумма</label>
                                    <p class="fw-bold text-primary fs-5">{{ number_format($order->total_amount, 0, ',', ' ') }} ₽</p>
                                </div>
                            </div>

                            @if($order->notes)
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted">Примечание</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-0">{{ $order->notes }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Действия -->
                <div class="col-12 col-md-4 mt-4 mt-md-0">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>Действия
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('client.services') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Новый заказ
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Контактная информация -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-telephone me-2"></i>Контакты
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Телефон филиала:</small><br>
                                <strong>{{ $order->branch->phone ?? 'Не указан' }}</strong>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Адрес:</small><br>
                                <strong>{{ $order->branch->address }}</strong>
                            </div>
                            @if($order->branch->email)
                            <div>
                                <small class="text-muted">Email:</small><br>
                                <strong>{{ $order->branch->email }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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

.card {
    // Убираем hover эффекты для карточек
}

.badge {
    font-size: 0.75rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endpush
