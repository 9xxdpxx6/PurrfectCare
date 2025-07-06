@extends('layouts.admin')

@section('title', 'Редактировать поставку')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать поставку</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drug-procurements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <form action="{{ route('admin.drug-procurements.update', $item) }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="row">
            <div class="col-md-6 col-lg-6 mb-3">
                <label for="drug_id" class="form-label">Препарат</label>
                <select name="drug_id" id="drug_id" class="form-select @error('drug_id') is-invalid @enderror" data-url="{{ route('admin.drug-procurements.drug-options') }}">
                    <option value="">Выберите препарат</option>
                    @if(old('drug_id', $item->drug_id))
                        @php
                            $selectedDrug = old('drug_id') ? \App\Models\Drug::with('unit')->find(old('drug_id')) : $item->drug;
                        @endphp
                        @if($selectedDrug)
                            <option value="{{ $selectedDrug->id }}" selected>{{ $selectedDrug->name }}{{ $selectedDrug->unit ? ' (' . $selectedDrug->unit->symbol . ')' : '' }}</option>
                        @endif
                    @endif
                </select>
                @error('drug_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="supplier_id" class="form-label">Поставщик</label>
                <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" data-url="{{ route('admin.drug-procurements.supplier-options') }}">
                    <option value="">Выберите поставщика</option>
                    @if(old('supplier_id', $item->supplier_id))
                        @php
                            $selectedSupplier = old('supplier_id') ? \App\Models\Supplier::find(old('supplier_id')) : $item->supplier;
                        @endphp
                        @if($selectedSupplier)
                            <option value="{{ $selectedSupplier->id }}" selected>{{ $selectedSupplier->name }}</option>
                        @endif
                    @endif
                </select>
                @error('supplier_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="delivery_date" class="form-label">Дата поставки</label>
                @php
                    $deliveryDate = old('delivery_date', $item->delivery_date);
                    try {
                        $deliveryDate = \Carbon\Carbon::parse($deliveryDate)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $deliveryDate = $deliveryDate;
                    }
                @endphp
                <input type="text" name="delivery_date" id="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ $deliveryDate }}" autocomplete="off">
                @error('delivery_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="manufacture_date" class="form-label">Дата изготовления</label>
                @php
                    $manufactureDate = old('manufacture_date', $item->manufacture_date);
                    try {
                        $manufactureDate = \Carbon\Carbon::parse($manufactureDate)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $manufactureDate = $manufactureDate;
                    }
                @endphp
                <input type="text" name="manufacture_date" id="manufacture_date" class="form-control @error('manufacture_date') is-invalid @enderror" value="{{ $manufactureDate }}" autocomplete="off">
                @error('manufacture_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="packaging_date" class="form-label">Дата упаковки</label>
                @php
                    $packagingDate = old('packaging_date', $item->packaging_date);
                    try {
                        $packagingDate = \Carbon\Carbon::parse($packagingDate)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $packagingDate = $packagingDate;
                    }
                @endphp
                <input type="text" name="packaging_date" id="packaging_date" class="form-control @error('packaging_date') is-invalid @enderror" value="{{ $packagingDate }}" autocomplete="off">
                @error('packaging_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="expiry_date" class="form-label">Срок годности</label>
                @php
                    $expiryDate = old('expiry_date', $item->expiry_date);
                    try {
                        $expiryDate = \Carbon\Carbon::parse($expiryDate)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $expiryDate = $expiryDate;
                    }
                @endphp
                <input type="text" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ $expiryDate }}" autocomplete="off">
                @error('expiry_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="price" class="form-label">Цена</label>
                <div class="input-group">
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', $item->price) }}">
                    <span class="input-group-text">₽</span>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6 col-lg-6 mb-3">
                <label for="quantity" class="form-label">Количество</label>
                <div class="input-group">
                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1" value="{{ old('quantity', $item->quantity) }}">
                    <span class="input-group-text" id="quantity-unit">
                        @if(old('drug_id', $item->drug_id) && $item->drug && $item->drug->unit)
                            {{ $item->drug->unit->symbol }}
                        @else
                            у.е.
                        @endif
                    </span>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('admin.drug-procurements.index') }}" class="btn btn-outline-secondary">Отмена</a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg"></i> Сохранить
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tom Select с динамической загрузкой
        const drugSelect = new createTomSelect('#drug_id', {
            placeholder: 'Выберите препарат...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                const selectedValue = this.getValue();
                if (selectedValue && !query) {
                    url += '&selected=' + encodeURIComponent(selectedValue);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onChange: function(value) {
                // Обновляем единицу измерения при выборе препарата
                updateQuantityUnit(value);
            }
        });
        
        // Функция для обновления единицы измерения
        function updateQuantityUnit(drugId) {
            const quantityUnit = document.getElementById('quantity-unit');
            if (!drugId) {
                quantityUnit.textContent = 'у.е.';
                return;
            }
            
            // Получаем информацию о препарате
            fetch(`{{ route('admin.drug-procurements.drug-options') }}?selected=${drugId}`)
                .then(response => response.json())
                .then(data => {
                    const drug = data.find(item => item.value == drugId);
                    if (drug && drug.text) {
                        // Извлекаем единицу измерения из текста (в скобках)
                        const match = drug.text.match(/\(([^)]+)\)$/);
                        if (match) {
                            quantityUnit.textContent = match[1];
                        } else {
                            quantityUnit.textContent = 'у.е.';
                        }
                    } else {
                        quantityUnit.textContent = 'у.е.';
                    }
                })
                .catch(() => {
                    quantityUnit.textContent = 'у.е.';
                });
        }
        
        new createTomSelect('#supplier_id', {
            placeholder: 'Выберите поставщика...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                const selectedValue = this.getValue();
                if (selectedValue && !query) {
                    url += '&selected=' + encodeURIComponent(selectedValue);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });

        // Air Datepickers
        createDatepicker('#delivery_date');
        createDatepicker('#manufacture_date');
        createDatepicker('#packaging_date');
        createDatepicker('#expiry_date');
    });
</script>
@endpush 