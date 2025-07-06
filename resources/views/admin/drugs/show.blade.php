@extends('layouts.admin')

@section('title', 'Просмотр препарата')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 col-12 col-md-7 col-xl-8">Препарат: {{ $item->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drugs.edit', $item) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.drugs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Основная информация -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-capsule"></i> {{ $item->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-cash-stack"></i> Цена:</strong>
                            {{ number_format($item->price, 2, ',', ' ') }} ₽
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-box"></i> Количество на складе:</strong>
                            {{ $item->quantity }}{{ $item->unit ? ' ' . $item->unit->symbol : '' }}
                        </p>
                        @if($item->unit)
                            <p class="mb-2">
                                <strong><i class="bi bi-rulers"></i> Единица измерения:</strong>
                                {{ $item->unit->name }} ({{ $item->unit->symbol }})
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Добавлен:</strong>
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y H:i') }}
                        </p>
                        @if($firstProcurement)
                            <p class="mb-2">
                                <strong><i class="bi bi-truck"></i> Поставляется с:</strong>
                                {{ $firstProcurement->delivery_date->format('d.m.Y') }}
                            </p>
                        @else
                            <p class="mb-2">
                                <strong><i class="bi bi-calendar-check"></i> Обновлен:</strong>
                                {{ \Carbon\Carbon::parse($item->updated_at)->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($item->prescription_required)
                            <p class="mb-2">
                                <strong><i class="bi bi-exclamation-triangle text-warning"></i> Требуется рецепт</strong>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Поставки -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                                            <i class="bi bi-truck"></i> Поставки ({{ $procurementsTotal }})
                </h5>
            </div>
            <div class="card-body">
                @if($procurementsTotal > 0)
                    <div class="d-flex flex-column gap-3">
                                @foreach($item->procurements as $procurement)
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="row align-items-center g-2">
                                    <!-- Поставщик и дата -->
                                    <div class="col-12 col-md-6 col-xl-4 mb-2 mb-xl-0">
                                        <h6 class="mb-1">
                                            <a href="{{ route('admin.suppliers.show', $procurement->supplier) }}" class="text-decoration-none">
                                                {{ $procurement->supplier->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> {{ $procurement->delivery_date->format('d.m.Y') }}
                                        </small>
                                    </div>
                                    
                                    <!-- Количество и цена -->
                                    <div class="col-12 col-md-6 col-xl-4 mb-2 mb-xl-0">
                                        <div class="d-flex justify-content-between justify-content-md-start gap-md-3">
                                            <div>
                                                <small class="text-muted d-block">Количество</small>
                                                <span class="fw-bold">{{ $procurement->quantity }}{{ $item->unit ? $item->unit->symbol : '' }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Цена</small>
                                                <span class="fw-bold">{{ number_format($procurement->price, 2, ',', ' ') }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Срок годности и статус -->
                                    <div class="col-12 col-xl-4 text-xl-end">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-xl-none">
                                                <small class="text-muted d-block">Срок годности</small>
                                            <span class="@if($procurement->expiry_date->lt(\Carbon\Carbon::now())) text-danger @elseif($procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                                                {{ $procurement->expiry_date->format('d.m.Y') }}
                                            </span>
                                            </div>
                                            
                                            <div class="d-none d-xl-block text-end">
                                                <small class="text-muted d-block">Срок годности</small>
                                                <div class="@if($procurement->expiry_date->lt(\Carbon\Carbon::now())) text-danger @elseif($procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                                                    {{ $procurement->expiry_date->format('d.m.Y') }}
                                                </div>
                                            </div>
                                            
                                            <div class="ms-2">
                                            @if($procurement->expiry_date->lt(\Carbon\Carbon::now()))
                                                <span class="badge bg-danger">Просрочен</span>
                                            @elseif($procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30)))
                                                <span class="badge bg-warning">Скоро истечет</span>
                                            @else
                                                <span class="badge bg-success">Годен</span>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">Поставки не найдены</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Статистика -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего поставок:</span>
                    <strong>{{ $procurementsTotal }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Поставщиков:</span>
                    <strong>{{ $item->procurements->pluck('supplier')->unique('id')->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Средняя стоимость:</span>
                    <strong>{{ number_format($item->procurements->avg('price'), 2, ',', ' ') }} ₽</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Общее количество:</span>
                    <strong>{{ $item->procurements->sum('quantity') }}{{ $item->unit ? ' ' . $item->unit->symbol : '' }}</strong>
                </div>
                @if($procurementsTotal > 0)
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Последняя поставка:</span>
                        <strong>{{ $item->procurements->first()->delivery_date->format('d.m.Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Ближайший срок годности:</span>
                        <strong class="@if($item->procurements->min('expiry_date')->lt(\Carbon\Carbon::now())) text-danger @elseif($item->procurements->min('expiry_date')->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                            {{ $item->procurements->min('expiry_date')->format('d.m.Y') }}
                        </strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Действия -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Действия
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.drug-procurements.create', ['drug' => $item->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus"></i> Добавить поставку
                    </a>
                    <a href="{{ route('admin.drugs.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <hr>
                    <form action="{{ route('admin.drugs.destroy', $item) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить препарат?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 