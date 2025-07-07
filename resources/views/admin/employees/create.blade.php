@extends('layouts.admin')

@section('title', 'Добавить сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить сотрудника</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<form method="POST" action="{{ route('admin.employees.store') }}" class="needs-validation" novalidate>
    @csrf
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="phone" class="form-label">Телефон</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="20">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="specialties" class="form-label">Специальности</label>
            <select id="specialties" name="specialties[]" class="form-select @error('specialties') is-invalid @enderror" multiple>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected(collect(old('specialties'))->contains($specialty->id))>{{ $specialty->name }}</option>
                @endforeach
            </select>
            @error('specialties')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="branches" class="form-label">Филиалы</label>
            <select id="branches" name="branches[]" class="form-select @error('branches') is-invalid @enderror" multiple>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(collect(old('branches'))->contains($branch->id))>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branches')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Отмена</a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new createTomSelect('#specialties', {
            placeholder: 'Выберите специальности...',
        });
        new createTomSelect('#branches', {
            searchField: [],
            placeholder: 'Выберите филиалы...',
            onInitialize: function () {
                this.control_input.setAttribute('readonly', 'readonly');
            }
        });
    });
</script>
@endsection

