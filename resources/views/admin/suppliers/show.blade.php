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
                    <div class="col-sm-4 fw-bold">Добавлен:</div>
                    <div class="col-sm-8">{{ $supplier->created_at->format('d.m.Y') }}</div>
                </div>
                
                @if($lastDelivery)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Последняя поставка:</div>
                    <div class="col-sm-8">{{ $lastDelivery->delivery_date->format('d.m.Y') }}</div>
                </div>
                @endif
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
                    <div class="col-sm-6">{{ number_format($totalValue, 2, ',', ' ') }} ₽</div>
                </div>
                

            </div>
        </div>
    </div>
</div>

<!-- Последние поставки -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
                                    <i class="bi bi-truck"></i> Последние поставки ({{ $procurementsTotal }})
        </h5>
    </div>
    <div class="card-body">
        @if($supplier->procurements->count() > 0)
            <div class="d-flex flex-column gap-3">
                        @foreach($supplier->procurements as $procurement)
                    <div class="border rounded p-3 bg-body-tertiary">
                        <div class="row align-items-center g-2">
                            <!-- Строка 1 для MD: Препарат и дата -->
                            <div class="col-12 col-md-12 col-xl-3 mb-2 mb-xl-0">
                                <div class="d-md-flex d-xl-block justify-content-between align-items-center">
                                    <h6 class="mb-1 mb-md-0 mb-xl-1">
                                        <a href="{{ route('admin.drugs.show', $procurement->drug) }}" class="text-decoration-none">
                                            {{ $procurement->drug->name ?? 'Неизвестный препарат' }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> {{ $procurement->delivery_date->format('d.m.Y') }}
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Строка 2 для MD: Количество и цена -->
                            <div class="col-12 col-md-6 col-xl-2 mb-2 mb-xl-0">
                                <div class="mb-1">
                                    <small class="text-muted">Количество:</small> {{ $procurement->quantity }}
                                </div>
                                <div>
                                    <small class="text-muted">Цена:</small> {{ number_format($procurement->price, 2, ',', ' ') }} ₽
                                </div>
                            </div>
                            
                            <!-- Строка 2 для MD: Сумма -->
                            <div class="col-12 col-md-6 col-xl-2 mb-2 mb-xl-0">
                                <div class="text-end">
                                    <small class="text-muted d-block">Сумма</small>
                                    <span class="fw-bold">{{ number_format($procurement->price * $procurement->quantity, 2, ',', ' ') }} ₽</span>
                                </div>
                            </div>
                            
                            <!-- Строка 3 для MD: Даты производства и упаковки -->
                            <div class="col-12 col-md-6 col-xl-2 mb-2 mb-xl-0">
                                <div class="gap-md-3">
                                    <div class="mb-1 mb-md-0 mb-xl-1">
                                        <small class="text-muted">Произв.:</small> {{ $procurement->manufacture_date->format('d.m.Y') }}
                                    </div>
                                    <div>
                                        <small class="text-muted">Упак.:</small> {{ $procurement->packaging_date->format('d.m.Y') }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Строка 3 для MD: Срок годности и действие -->
                            <div class="col-12 col-md-6 col-xl-2 mb-2 mb-xl-0">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                    @php
                                        $isExpired = $procurement->expiry_date->isPast();
                                        $isExpiringSoon = $procurement->expiry_date->diffInDays(now()) <= 30;
                                    @endphp
                                        <small class="text-muted d-block">
                                            {{ $isExpired ? 'Просрочен' : 'Годен до' }}
                                        </small>
                                    <span class="badge {{ $isExpired ? 'bg-danger' : ($isExpiringSoon ? 'bg-warning' : 'bg-success') }}">
                                        {{ $procurement->expiry_date->format('d.m.Y') }}
                                    </span>
                                    </div>
                                    <div class="d-block d-xl-none">
                                        <a href="{{ route('admin.drug-procurements.show', $procurement) }}" class="btn btn-outline-primary btn-sm" title="Подробнее">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-xl-1 mb-2 mb-xl-0 d-none d-xl-block">
                                <div class="text-end">
                                    <a href="{{ route('admin.drug-procurements.show', $procurement) }}" class="btn btn-outline-primary btn-sm" title="Подробнее">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                        @endforeach
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