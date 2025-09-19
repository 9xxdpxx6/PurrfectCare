@extends('layouts.admin')

@section('title', 'Редактировать сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать сотрудника</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>


<form method="POST" action="{{ route('admin.employees.update', $employee) }}">
    @csrf
    @method('PATCH')
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->name) }}" maxlength="255">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="phone" class="form-label">Телефон</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" maxlength="20">
            <div class="form-text text-muted">
                <i class="bi bi-info-circle"></i> Поддерживаемые форматы: +7XXXXXXXXXX, 8XXXXXXXXXX, 7XXXXXXXXXX
            </div>
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
        @can('roles.read')
        <div class="col-md-6 col-lg-4">
            <label for="roles" class="form-label">Роли</label>
            <select id="roles" name="roles[]" class="form-select tomselect @error('roles') is-invalid @enderror" multiple data-url="{{ route('admin.roles.options') }}">
                @php
                    $currentRoles = old('roles', $employee->roles->pluck('id')->toArray());
                    $selectedRoles = \Spatie\Permission\Models\Role::whereIn('id', $currentRoles)->get();
                @endphp
                @foreach($selectedRoles as $role)
                    <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('roles')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @endcan
        <div class="col-md-6 col-lg-4">
            <div class="form-check">
                <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $employee->is_active))>
                <label class="form-check-label" for="is_active">
                    Действующий сотрудник
                </label>
                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex flex-column flex-md-row">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
        </a>

        <!-- Кнопка "Сбросить пароль" -->
        <a href="{{ route('admin.employees.resetPassword', $employee) }}"
            class="btn btn-outline-warning mt-3 mt-md-0 ms-md-auto"
            onclick="return confirm('Сбросить пароль для сотрудника {{ $employee->name }}? Новый пароль будет отправлен на email: {{ $employee->email }}')">
            <i class="bi bi-key"></i>
            Сбросить пароль
        </a>

        <!-- Кнопка "Сохранить" -->
        @can('employees.update')
        <button type="submit" class="btn btn-success mt-3 mt-md-0 ms-md-3">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
        @endcan
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
        
        @if(auth()->guard('admin')->user()->can('roles.read'))
        const currentRoles = @json(old('roles', $employee->roles->pluck('id')->toArray()));
        
        new createTomSelect('#roles', {
            placeholder: 'Выберите роли...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть текущие роли и это первая загрузка, передаём их
                if (currentRoles.length > 0 && !query) {
                    url += '&selected=' + currentRoles.join(',');
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                    })
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        @endif
    });
</script>
@endsection

@push('scripts')
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => a.classList.remove('show'));
    }, 3000);
</script>
@endpush
