@extends('layouts.admin')

@section('title', 'Поставки препаратов')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Поставки препаратов</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drug-procurements.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить поставку</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Препарат или поставщик..." value="{{ request('search') }}">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="supplier" class="form-label mb-1">Поставщик</label>
            <select name="supplier" id="supplier" class="form-select">
                <option value="">Все поставщики</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @if(request('supplier') == $supplier->id) selected @endif>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="drug" class="form-label mb-1">Препарат</label>
            <select name="drug" id="drug" class="form-select">
                <option value="">Все препараты</option>
                @foreach($drugs as $drug)
                    <option value="{{ $drug->id }}" @if(request('drug') == $drug->id) selected @endif>{{ $drug->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="delivery_date_desc" @if(request('sort') == 'delivery_date_desc') selected @endif>Дата поставки (новые)</option>
                <option value="delivery_date_asc" @if(request('sort') == 'delivery_date_asc') selected @endif>Дата поставки (старые)</option>
                <option value="expiry_date_asc" @if(request('sort') == 'expiry_date_asc') selected @endif>Срок годности (близкие)</option>
                <option value="expiry_date_desc" @if(request('sort') == 'expiry_date_desc') selected @endif>Срок годности (дальние)</option>
                <option value="price_asc" @if(request('sort') == 'price_asc') selected @endif>По цене (дешевые)</option>
                <option value="price_desc" @if(request('sort') == 'price_desc') selected @endif>По цене (дорогие)</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="delivery_date_from" class="form-label mb-1">Дата поставки с</label>
            @php
                $deliveryDateFrom = request('delivery_date_from');
                if ($deliveryDateFrom) {
                    try {
                        $deliveryDateFrom = \Carbon\Carbon::parse($deliveryDateFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $deliveryDateFrom = $deliveryDateFrom;
                    }
                }
            @endphp
            <input type="text" name="delivery_date_from" id="delivery_date_from" class="form-control" value="{{ $deliveryDateFrom }}" readonly>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="delivery_date_to" class="form-label mb-1">Дата поставки до</label>
            @php
                $deliveryDateTo = request('delivery_date_to');
                if ($deliveryDateTo) {
                    try {
                        $deliveryDateTo = \Carbon\Carbon::parse($deliveryDateTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $deliveryDateTo = $deliveryDateTo;
                    }
                }
            @endphp
            <input type="text" name="delivery_date_to" id="delivery_date_to" class="form-control" value="{{ $deliveryDateTo }}" readonly>
        </div>
        <div class="flex-grow-1" style="min-width:120px;">
            <label for="price_from" class="form-label mb-1">Цена от</label>
            <input type="number" name="price_from" id="price_from" class="form-control" step="0.01" placeholder="0.00" value="{{ request('price_from') }}">
        </div>
        <div class="flex-grow-1" style="min-width:120px;">
            <label for="price_to" class="form-label mb-1">Цена до</label>
            <input type="number" name="price_to" id="price_to" class="form-control" step="0.01" placeholder="0.00" value="{{ request('price_to') }}">
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.drug-procurements.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $procurement)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="d-flex flex-column h-100 flex-lg-row w-100 gap-3 align-items-lg-center">
                        <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                            <h5 class="card-title mb-1">{{ $procurement->drug->name }}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                Поставщик: {{ $procurement->supplier->name }}
                            </h6>
                            <div class="row w-100 g-1">
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-muted">Изготовлен:&nbsp;</span>
                                        <span>{{ $procurement->manufacture_date->format('d.m.Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">Упакован:&nbsp;</span>
                                        <span>{{ $procurement->packaging_date->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-muted">Поставлен:&nbsp;</span>
                                        <span>{{ $procurement->delivery_date->format('d.m.Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">Годен до:&nbsp;</span>
                                        <span>{{ $procurement->expiry_date->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="price-container d-flex flex-column align-items-lg-end align-self-start text-nowrap">
                            <p class="card-text">
                                <span>Стоимость:</span> {{ number_format($procurement->price, 2, ',', ' ') }} ₽
                            </p>
                            <p class="card-text">
                                <span>Количество:</span> {{ $procurement->quantity }}{{ $procurement->drug->unit ? ' ' . $procurement->drug->unit->symbol : '' }}
                            </p>
                        </div>
                        <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap">
                            <a href="{{ route('admin.drug-procurements.show', $procurement) }}" class="btn btn-outline-info" title="Просмотр">
                                <span class="d-none d-lg-inline-block">Просмотр</span>
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.drug-procurements.edit', $procurement) }}" class="btn btn-outline-warning" title="Редактировать">
                                <span class="d-none d-lg-inline-block">Редактировать</span>
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.drug-procurements.destroy', $procurement) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                    onclick="return confirm('Удалить поставку?');">
                                    <span class="d-none d-lg-inline-block">Удалить</span>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($items->count() == 0)
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <p class="text-muted mt-3">Поставки не найдены</p>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tom Select
        new createTomSelect('#supplier', {
            placeholder: 'Выберите поставщика...',
        });
        new createTomSelect('#drug', {
            placeholder: 'Выберите препарат...',
        });

        // Air Datepickers
        createDatepicker('#delivery_date_from');
        createDatepicker('#delivery_date_to');
    });
</script>
@endpush 