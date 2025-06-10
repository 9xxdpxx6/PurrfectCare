@extends('layouts.admin')

@section('title', 'Добавить поставку')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить поставку</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drug-procurements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <form action="{{ route('admin.drug-procurements.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-6 mb-3">
                <label for="drug_id" class="form-label">Препарат *</label>
                <select name="drug_id" id="drug_id" class="form-select @error('drug_id') is-invalid @enderror" required>
                    <option value="">Выберите препарат</option>
                    @foreach($drugs as $drug)
                        <option value="{{ $drug->id }}" @if(old('drug_id') == $drug->id) selected @endif>{{ $drug->name }}</option>
                    @endforeach
                </select>
                @error('drug_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="supplier_id" class="form-label">Поставщик *</label>
                <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                    <option value="">Выберите поставщика</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @if(old('supplier_id') == $supplier->id) selected @endif>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="delivery_date" class="form-label">Дата поставки *</label>
                @php
                    $deliveryDate = old('delivery_date');
                    if (!$deliveryDate) {
                        $deliveryDate = now()->format('d.m.Y');
                    } else {
                        try {
                            $deliveryDate = \Carbon\Carbon::parse($deliveryDate)->format('d.m.Y');
                        } catch (\Exception $e) {
                            $deliveryDate = $deliveryDate;
                        }
                    }
                @endphp
                <input type="text" name="delivery_date" id="delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ $deliveryDate }}" readonly required>
                @error('delivery_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="manufacture_date" class="form-label">Дата изготовления *</label>
                @php
                    $manufactureDate = old('manufacture_date');
                    if ($manufactureDate) {
                        try {
                            $manufactureDate = \Carbon\Carbon::parse($manufactureDate)->format('d.m.Y');
                        } catch (\Exception $e) {
                            $manufactureDate = $manufactureDate;
                        }
                    }
                @endphp
                <input type="text" name="manufacture_date" id="manufacture_date" class="form-control @error('manufacture_date') is-invalid @enderror" value="{{ $manufactureDate }}" readonly required>
                @error('manufacture_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="packaging_date" class="form-label">Дата упаковки *</label>
                @php
                    $packagingDate = old('packaging_date');
                    if ($packagingDate) {
                        try {
                            $packagingDate = \Carbon\Carbon::parse($packagingDate)->format('d.m.Y');
                        } catch (\Exception $e) {
                            $packagingDate = $packagingDate;
                        }
                    }
                @endphp
                <input type="text" name="packaging_date" id="packaging_date" class="form-control @error('packaging_date') is-invalid @enderror" value="{{ $packagingDate }}" readonly required>
                @error('packaging_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="expiry_date" class="form-label">Срок годности *</label>
                @php
                    $expiryDate = old('expiry_date');
                    if ($expiryDate) {
                        try {
                            $expiryDate = \Carbon\Carbon::parse($expiryDate)->format('d.m.Y');
                        } catch (\Exception $e) {
                            $expiryDate = $expiryDate;
                        }
                    }
                @endphp
                <input type="text" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ $expiryDate }}" readonly required>
                @error('expiry_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-lg-6 mb-3">
                <label for="price" class="form-label">Цена *</label>
                <div class="input-group">
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price') }}" required>
                    <span class="input-group-text">₽</span>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <label for="quantity" class="form-label">Количество *</label>
                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1" value="{{ old('quantity') }}" required>
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
        // Tom Select
        new createTomSelect('#drug_id', {
            placeholder: 'Выберите препарат...',
        });
        new createTomSelect('#supplier_id', {
            placeholder: 'Выберите поставщика...',
        });

        // Air Datepickers
        createDatepicker('#delivery_date');
        createDatepicker('#manufacture_date');
        createDatepicker('#packaging_date');
        createDatepicker('#expiry_date');
    });
</script>
@endpush 