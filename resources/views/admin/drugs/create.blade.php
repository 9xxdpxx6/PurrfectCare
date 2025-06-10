@extends('layouts.admin')

@section('title', 'Добавить препарат')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить препарат</h1>
    <a href="{{ route('admin.drugs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад к списку
    </a>
</div>

<form method="POST" action="{{ route('admin.drugs.store') }}" class="needs-validation" novalidate>
    @csrf
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Название препарата</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="price" class="form-label">Цена (₽)</label>
            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="quantity" class="form-label">Количество</label>
            <input type="number" min="0" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="unit_id" class="form-label">Единица измерения</label>
            <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
                <option value="">Выберите единицу</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @if(old('unit_id') == $unit->id) selected @endif>{{ $unit->name }} ({{ $unit->symbol }})</option>
                @endforeach
            </select>
            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="form-check mt-4">
                <input class="form-check-input @error('prescription_required') is-invalid @enderror" type="checkbox" id="prescription_required" name="prescription_required" value="1" @if(old('prescription_required')) checked @endif>
                <label class="form-check-label" for="prescription_required">
                    Требуется рецепт
                </label>
                @error('prescription_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.drugs.index') }}" class="btn btn-outline-secondary">Отмена</a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new createTomSelect('#unit_id', {
            placeholder: 'Выберите единицу измерения...'
        });
    });
</script>
@endsection 