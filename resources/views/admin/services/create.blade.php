@extends('layouts.admin')

@section('title', 'Добавить услугу')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить услугу</h1>
    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Назад</span>
    </a>
</div>

<form method="POST" action="{{ route('admin.services.store') }}" class="needs-validation" novalidate>
    @csrf
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Название услуги</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="price" class="form-label">Цена (₽)</label>
            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="duration" class="form-label">Продолжительность (минуты)</label>
            <input type="number" min="1" max="1440" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration') }}" required>
            @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label">Описание услуги</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="1000">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Максимум 1000 символов</div>
        </div>
        <div class="col-12">
            <label for="branches" class="form-label">Филиалы</label>
            <select multiple class="form-select @error('branches') is-invalid @enderror" id="branches" name="branches[]" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @if(is_array(old('branches')) && in_array($branch->id, old('branches'))) selected @endif>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branches')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Выберите один или несколько филиалов</div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">Отмена</a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new createTomSelect('#branches', {
            placeholder: 'Выберите филиалы...',
            plugins: ['remove_button'],
            hidePlaceholder: false
        });
    });
</script>
@endsection 