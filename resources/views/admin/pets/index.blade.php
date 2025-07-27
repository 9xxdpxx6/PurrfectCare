@extends('layouts.admin')

@section('title', 'Питомцы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Питомцы - {{ $items->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.pets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить питомца</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск..." value="{{ request('search') }}">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="owner" class="form-label mb-1">Владелец</label>
            <select name="owner" id="owner" class="form-select tomselect" data-url="{{ route('admin.pets.owner-options') }}">
                @if(request('owner'))
                    @php
                        $selectedOwner = \App\Models\User::find(request('owner'));
                    @endphp
                    @if($selectedOwner)
                        <option value="{{ $selectedOwner->id }}" selected>{{ $selectedOwner->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="gender" class="form-label mb-1">Пол</label>
            <select name="gender" id="gender" class="form-select">
                <option value="">Любой</option>
                <option value="male" @if(request('gender') == 'male') selected @endif>Самец</option>
                <option value="female" @if(request('gender') == 'female') selected @endif>Самка</option>
                <option value="unknown" @if(request('gender') == 'unknown') selected @endif>Неизвестно</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="name_asc" @if(request('sort') == 'name_asc') selected @endif>По алфавиту (А-Я)</option>
                <option value="name_desc" @if(request('sort') == 'name_desc') selected @endif>По алфавиту (Я-А)</option>
                <option value="birth_desc" @if(request('sort') == 'birth_desc') selected @endif>Дата рождения (сначала новые)</option>
                <option value="birth_asc" @if(request('sort') == 'birth_asc') selected @endif>Дата рождения (сначала старые)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $i => $pet)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $pet->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-1">
                                <span>Владелец:</span> {{ $pet->client->name }}
                            </div>
                            <div class="text-muted mb-1">
                                <span>Дата рождения:</span>
                                {{ \Carbon\Carbon::parse($pet->birth_date)->format('d.m.Y') }}
                            </div>
                            <div class="text-muted">
                                <span>Пол:</span>
                                @if($pet->gender === 'male')
                                    Самец <i class="bi bi-gender-male"></i>
                                @elseif($pet->gender === 'female')
                                    Самка <i class="bi bi-gender-female"></i>
                                @else
                                    Неизвестно
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
                        <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.pets.edit', $pet) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.pets.destroy', $pet) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить запись?')">
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

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-heartbreak display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Питомцы не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте нового питомца.</p>
        <a href="{{ route('admin.pets.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить питомца
        </a>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedOwner = '{{ request("owner") }}';
        
        // TomSelect для владельцев с динамической загрузкой
        new createTomSelect('#owner', {
            placeholder: 'Выберите владельца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                if (selectedOwner && !query) {
                    url += '&selected=' + encodeURIComponent(selectedOwner);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
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
