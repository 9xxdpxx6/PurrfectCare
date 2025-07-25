@extends('layouts.admin')

@section('title', 'Сотрудники')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Сотрудники</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить сотрудника</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Поиск...">
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="branch" class="form-label mb-1">Филиал</label>
            <select name="branch" id="branch" class="form-select tomselect" data-placeholder="Филиал">
                <option value="">Все</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(request('branch') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="specialty" class="form-label mb-1">Специальность</label>
            <select name="specialty" id="specialty" class="form-select tomselect" data-url="{{ route('admin.employees.specialty-options') }}">
                @if(request('specialty'))
                    @php
                        $selectedSpecialty = \App\Models\Specialty::find(request('specialty'));
                    @endphp
                    @if($selectedSpecialty)
                        <option value="{{ $selectedSpecialty->id }}" selected>{{ $selectedSpecialty->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="name_asc" @selected(request('sort')=='name_asc')>Имя А-Я</option>
                <option value="name_desc" @selected(request('sort')=='name_desc')>Имя Я-А</option>
                <option value="email_asc" @selected(request('sort')=='email_asc')>Email A-Z</option>
                <option value="email_desc" @selected(request('sort')=='email_desc')>Email Z-A</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($employees as $i => $employee)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body flex-grow-1 d-flex flex-column flex-lg-row align-items-lg-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-3">{{ $employee->name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            {{ $employee->specialties->pluck('name')->join(', ') ?: '—' }}
                        </h6>
                        <p class="card-text mb-0">
                            <span>Email:</span> {{ $employee->email }}
                        </p>
                        <p class="card-text mb-0">
                            <span>Телефон:</span> {{ $employee->phone }}
                        </p>
                        <p class="card-text mb-0">
                            <span>Филиал:</span> {{ $employee->branches->pluck('name')->join(', ') ?: '—' }}
                        </p>
                    </div>
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap mt-3 mt-lg-0">
                        <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить" onclick="return confirm('Удалить сотрудника ({{ $employee->name }})?');">
                                <span class="d-none d-lg-inline-block">Удалить</span>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($employees->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-emoji-neutral display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Сотрудники не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте нового сотрудника.</p>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить сотрудника
        </a>
    </div>
@endif

<div class="mt-4">
    {{ $employees->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedValue = '{{ request("specialty") }}';
        
        const specialtySelect = new createTomSelect('#specialty', {
            placeholder: 'Выберите специальность...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                if (selectedValue && !query) {
                    url += '&selected=' + encodeURIComponent(selectedValue);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                        // НЕ вызываем setValue() - значение уже установлено в HTML
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
    });
</script>
@endpush

