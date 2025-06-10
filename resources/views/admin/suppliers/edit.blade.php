@extends('layouts.admin')

@section('title', 'Редактировать поставщика')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать поставщика</h1>
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад к списку
    </a>
</div>

<form method="POST" action="{{ route('admin.suppliers.update', $item->id) }}" class="needs-validation" novalidate>
    @csrf
    @method('PATCH')
    <div class="row g-3">
        <div class="col-12">
            <label for="name" class="form-label">Название</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Отмена</a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        createDatepicker('#birthdate', {

        });
        new createTomSelect('#client_id', {
            placeholder: 'Выберите владельца...',
        });
        new createTomSelect('#breed_id', {
            placeholder: 'Выберите породу...',
        });
    });
</script>
@endsection
