@extends('layouts.admin')

@section('title', 'Поставка препарата')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Поставка препарата {{ $item->delivery_date->format('d.m.Y') }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('deliveries.update')
        <a href="{{ route('admin.drug-procurements.edit', $item) }}" class="btn btn-outline-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        @endcan
        <a href="{{ route('admin.drug-procurements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-truck"></i> Информация о поставке</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Препарат:</div>
                    <div class="col-sm-8">
                        <a href="{{ route('admin.drugs.show', $item->drug) }}" class="text-decoration-none">{{ $item->drug->name }}</a>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Поставщик:</div>
                    <div class="col-sm-8">{{ $item->supplier->name }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Филиал:</div>
                    <div class="col-sm-8">
                        @if($item->branch)
                            <span class="">{{ $item->branch->name }}</span>
                        @else
                            <span class="text-muted">Не указан</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Количество:</div>
                    <div class="col-sm-8">{{ $item->quantity }}{{ $item->drug->unit ? ' ' . $item->drug->unit->symbol : '' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Цена за единицу:</div>
                    <div class="col-sm-8">{{ number_format($item->price, 2, ',', ' ') }} ₽</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Дата поставки:</div>
                    <div class="col-sm-8">{{ $item->delivery_date->format('d.m.Y') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Изготовлен:</div>
                    <div class="col-sm-8">{{ $item->manufacture_date ? $item->manufacture_date->format('d.m.Y') : '—' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Упакован:</div>
                    <div class="col-sm-8">{{ $item->packaging_date ? $item->packaging_date->format('d.m.Y') : '—' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 fw-bold">Годен до:</div>
                    <div class="col-sm-8">
                        <span class="@if($item->expiry_date->lt(\Carbon\Carbon::now())) text-danger @elseif($item->expiry_date->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                            {{ $item->expiry_date ? $item->expiry_date->format('d.m.Y') : '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('deliveries.update')
                    <a href="{{ route('admin.drug-procurements.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    @endcan
                    <hr>
                    @can('deliveries.delete')
                    <form action="{{ route('admin.drug-procurements.destroy', $item) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить поставку?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 