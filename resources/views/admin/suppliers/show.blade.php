@extends('layouts.admin')

@section('title', 'Поставщик: ' . $supplier->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Поставщик: {{ $supplier->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-building"></i> Информация о поставщике
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Название:</div>
                    <div class="col-sm-8">{{ $supplier->name }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">ID:</div>
                    <div class="col-sm-8">{{ $supplier->id }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Дата создания:</div>
                    <div class="col-sm-8">{{ $supplier->created_at->format('d.m.Y H:i') }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Последнее обновление:</div>
                    <div class="col-sm-8">{{ $supplier->updated_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика поставок
                </h5>
            </div>
            <div class="card-body">
                @php
                    $totalProcurements = $supplier->procurements->count();
                    $totalDrugs = $supplier->procurements->unique('drug_id')->count();
                    $totalQuantity = $supplier->procurements->sum('quantity');
                    $totalValue = $supplier->procurements->sum(function($procurement) {
                        return $procurement->price * $procurement->quantity;
                    });
                    $lastDelivery = $supplier->procurements->sortByDesc('delivery_date')->first();
                @endphp
                
                <div class="row mb-3">
                    <div class="col-sm-6 fw-bold">Всего поставок:</div>
                    <div class="col-sm-6">{{ $totalProcurements }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6 fw-bold">Уникальных препаратов:</div>
                    <div class="col-sm-6">{{ $totalDrugs }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6 fw-bold">Общее количество:</div>
                    <div class="col-sm-6">{{ $totalQuantity }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-6 fw-bold">Общая стоимость:</div>
                    <div class="col-sm-6">{{ number_format($totalValue, 2) }} ₽</div>
                </div>
                
                @if($lastDelivery)
                <div class="row mb-3">
                    <div class="col-sm-6 fw-bold">Последняя поставка:</div>
                    <div class="col-sm-6">{{ $lastDelivery->delivery_date->format('d.m.Y') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Последние поставки -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-truck"></i> Последние поставки ({{ $supplier->procurements->count() }} из {{ $supplier->procurements->count() }})
        </h5>
    </div>
    <div class="card-body">
        @if($supplier->procurements->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Дата поставки</th>
                            <th>Препарат</th>
                            <th>Количество</th>
                            <th>Цена за единицу</th>
                            <th>Общая стоимость</th>
                            <th>Дата производства</th>
                            <th>Дата упаковки</th>
                            <th>Срок годности</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplier->procurements as $procurement)
                            <tr>
                                <td>
                                    <strong>{{ $procurement->delivery_date->format('d.m.Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $procurement->delivery_date->format('H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $procurement->drug->name ?? 'Неизвестный препарат' }}</strong>
                                </td>
                                <td>
                                    <span>{{ $procurement->quantity }}</span>
                                </td>
                                <td>{{ number_format($procurement->price, 2) }} ₽</td>
                                <td>
                                    <strong>{{ number_format($procurement->price * $procurement->quantity, 2) }} ₽</strong>
                                </td>
                                <td>{{ $procurement->manufacture_date->format('d.m.Y') }}</td>
                                <td>{{ $procurement->packaging_date->format('d.m.Y') }}</td>
                                <td>
                                    @php
                                        $isExpired = $procurement->expiry_date->isPast();
                                        $isExpiringSoon = $procurement->expiry_date->diffInDays(now()) <= 30;
                                    @endphp
                                    <span class="badge {{ $isExpired ? 'bg-danger' : ($isExpiringSoon ? 'bg-warning' : 'bg-success') }}">
                                        {{ $procurement->expiry_date->format('d.m.Y') }}
                                    </span>
                                    @if($isExpired)
                                        <br><small class="text-danger">Истёк</small>
                                    @elseif($isExpiringSoon)
                                        <br><small class="text-warning">Скоро истечёт</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-box-seam display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Поставки не найдены</h4>
                <p class="text-muted">У этого поставщика пока нет поставок.</p>
            </div>
        @endif
    </div>
</div>
@endsection 