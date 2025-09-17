@extends('layouts.client')

@section('title', 'Детали заказа - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="orders" />

        <!-- Основной контент -->
        <div class="col-12 col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <h2 class="h3 mb-0 me-3">Заказ #{{ $order->id }}</h2>
                    <span class="badge" style="background-color: {{ $order->status->color }}">
                        {{ $order->status->name }}
                    </span>
                </div>
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

                    @php
                        $services = $order->services()->get();
                        $drugs = $order->drugs()->get();
                        $labTests = $order->labTests()->get();
                        $vaccinations = $order->vaccinations()->get();
                    @endphp

                    @if($services->count() > 0)
                        <!-- Услуги -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-gear text-primary me-2"></i>Услуги ({{ $services->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($services as $orderItem)
                                        <div class="border rounded p-3 bg-light">
                                            <div class="row align-items-center g-2">
                                                <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                                    <h6 class="mb-1">
                                                        @if($orderItem->item)
                                                            <a href="{{ route('client.services.show', $orderItem->item) }}" class="text-decoration-none">
                                                                {{ $orderItem->item_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Услуга не найдена</span>
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                                    <div class="d-flex justify-content-between w-100 gap-md-1">
                                                        <div>
                                                            <small class="text-muted d-block">Кол-во</small>
                                                            <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Цена</small>
                                                            <span class="fw-bold">{{ number_format($orderItem->unit_price, 0, ',', ' ') }} ₽</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Сумма</small>
                                                            <div class="fw-bold">
                                                                {{ number_format($orderItem->quantity * $orderItem->unit_price, 0, ',', ' ') }} ₽
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Услуг на сумму:</span>
                                            <span class="fw-bold">{{ number_format($order->servicesTotal(), 0, ',', ' ') }} ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($drugs->count() > 0)
                        <!-- Препараты -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-capsule text-success me-2"></i>Препараты ({{ $drugs->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($drugs as $orderItem)
                                        <div class="border rounded p-3 bg-light">
                                            <div class="row align-items-center g-2">
                                                <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                                    <h6 class="mb-1">
                                                        @if($orderItem->item)
                                                            <a href="{{ route('client.services.show', $orderItem->item) }}" class="text-decoration-none">
                                                                {{ $orderItem->item_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Препарат не найден</span>
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                                    <div class="d-flex justify-content-between w-100 gap-md-1">
                                                        <div>
                                                            <small class="text-muted d-block">Кол-во</small>
                                                            <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Цена</small>
                                                            <span class="fw-bold">{{ number_format($orderItem->unit_price, 0, ',', ' ') }} ₽</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Сумма</small>
                                                            <div class="fw-bold">
                                                                {{ number_format($orderItem->quantity * $orderItem->unit_price, 0, ',', ' ') }} ₽
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Препаратов на сумму:</span>
                                            <span class="fw-bold">{{ number_format($order->drugsTotal(), 0, ',', ' ') }} ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($labTests->count() > 0)
                        <!-- Анализы -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clipboard-data text-info me-2"></i>Анализы ({{ $labTests->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($labTests as $orderItem)
                                        <div class="border rounded p-3 bg-light">
                                            <div class="row align-items-center g-2">
                                                <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                                    <h6 class="mb-1">
                                                        @if($orderItem->item)
                                                            <a href="{{ route('client.services.show', $orderItem->item) }}" class="text-decoration-none">
                                                                {{ $orderItem->item_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Анализ не найден</span>
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                                    <div class="d-flex justify-content-between w-100 gap-md-1">
                                                        <div>
                                                            <small class="text-muted d-block">Кол-во</small>
                                                            <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Цена</small>
                                                            <span class="fw-bold">{{ number_format($orderItem->unit_price, 0, ',', ' ') }} ₽</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Сумма</small>
                                                            <div class="fw-bold">
                                                                {{ number_format($orderItem->quantity * $orderItem->unit_price, 0, ',', ' ') }} ₽
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Анализов на сумму:</span>
                                            <span class="fw-bold">{{ number_format($order->labTestsTotal(), 0, ',', ' ') }} ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($vaccinations->count() > 0)
                        <!-- Вакцинации -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-shield-check text-warning me-2"></i>Вакцинации ({{ $vaccinations->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($vaccinations as $orderItem)
                                        <div class="border rounded p-3 bg-light">
                                            <div class="row align-items-center g-2">
                                                <div class="col-12 col-md-4 col-xl-7 mb-2 mb-xl-0">
                                                    <h6 class="mb-1">
                                                        @if($orderItem->item)
                                                            <a href="{{ route('client.services.show', $orderItem->item) }}" class="text-decoration-none">
                                                                {{ $orderItem->item_name }}
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Вакцинация не найдена</span>
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="col-12 col-md-5 col-xl-3 mb-2 mb-xl-0">
                                                    <div class="d-flex justify-content-between w-100 gap-md-1">
                                                        <div>
                                                            <small class="text-muted d-block">Кол-во</small>
                                                            <span class="fw-bold">{{ $orderItem->quantity }}</span>
                                                        </div>
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Цена</small>
                                                            <span class="fw-bold">{{ number_format($orderItem->unit_price, 0, ',', ' ') }} ₽</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-3 col-xl-2 text-xl-end">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <div class="text-end">
                                                            <small class="text-muted d-block">Сумма</small>
                                                            <div class="fw-bold">
                                                                {{ number_format($orderItem->quantity * $orderItem->unit_price, 0, ',', ' ') }} ₽
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="border-top pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Вакцинаций на сумму:</span>
                                            <span class="fw-bold">{{ number_format($order->vaccinationsTotal(), 0, ',', ' ') }} ₽</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($order->items->count() == 0)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-bag-x display-4 text-muted"></i>
                                <h5 class="mt-3">Состав заказа отсутствует</h5>
                                <p class="text-muted">В данном заказе нет позиций.</p>
                            </div>
                        </div>
                    @endif

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
                                    <p class="fw-bold text-primary fs-5">{{ number_format($order->total, 0, ',', ' ') }} ₽</p>
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
