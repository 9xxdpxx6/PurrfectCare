@extends('layouts.admin')

@section('title', 'Редактировать сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать сотрудника</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="needs-validation" novalidate>
    @csrf
    @method('PATCH')
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->name) }}" required maxlength="255">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" required maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="phone" class="form-label">Телефон</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" required maxlength="20">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="specialties" class="form-label">Специальности</label>
            <select id="specialties" name="specialties[]" class="form-select tomselect @error('specialties') is-invalid @enderror" multiple>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected(collect(old('specialties', $employee->specialties->pluck('id')->toArray()))->contains($specialty->id))>{{ $specialty->name }}</option>
                @endforeach
            </select>
            @error('specialties')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="branches" class="form-label">Филиалы</label>
            <select id="branches" name="branches[]" class="form-select tomselect @error('branches') is-invalid @enderror" multiple>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(collect(old('branches', $employee->branches->pluck('id')->toArray()))->contains($branch->id))>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branches')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4 d-flex flex-column flex-md-row">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Отмена</a>

        <!-- Кнопка "Сбросить пароль" -->
        <a href="{{ route('admin.employees.resetPassword', $employee) }}"
            class="btn btn-outline-warning mt-3 mt-md-0 ms-md-auto"
            onclick="return confirm('Сбросить пароль сотрудника? Новый временный пароль будет показан после сохранения.')">
            <i class="bi bi-key"></i>
            Сбросить пароль
        </a>

        <!-- Кнопка "Сохранить" -->
        <button type="submit" class="btn btn-success mt-3 mt-md-0 ms-md-3">
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
            placeholder: 'Выберите филиалы...',
        });
    });
</script>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.tomselect').forEach(el => {
        new TomSelect(el, {create: false, allowEmptyOption: true});
    });
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => a.classList.remove('show'));
    }, 3000);
</script>
@endpush
