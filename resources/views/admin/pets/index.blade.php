@extends('layouts.admin')

@section('title', 'Питомцы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Питомцы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.pets.create') }}" class="btn btn-primary d-flex flex-row align-items-center gap-2 ms-lg-2 me-3">
            <span class="d-none d-lg-inline-block">Добавить питомца</span>
            <i class="bi bi-plus"></i>
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
            <select name="owner" id="owner" class="form-select">
                <option value="">Все</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" @if(request('owner') == $owner->id) selected @endif>{{ $owner->name }}</option>
                @endforeach
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
        <div class="d-flex gap-2 me-3">
            <!-- TODO: сделать загрузку опшионов с БД -->
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
                    <div class="d-flex flex-column justify-content-center flex-grow-1">
                        <h5 class="card-title mb-1">{{ $pet->name }}</h5>

                        <h6 class="card-subtitle mb-2 text-muted">
                            {{ $pet->breed->species->name }} - {{ $pet->breed->name }}
                        </h6>

                        <div class="d-flex flex-wrap gap-3">
                            <p class="card-text mb-0">
                                <span>Владелец:</span> {{ $pet->client->name }}
                            </p>

                            <p class="card-text mb-0">
                                <span>Дата рождения:</span>
                                {{ \Carbon\Carbon::parse($pet->birth_date)->format('d.m.Y') }}
                            </p>

                            <p class="card-text mb-0">
                                <span>Пол:</span>
                                @if($pet->gender === 'male')
                                    <i class="bi bi-gender-male"></i>
                                    Самец
                                @elseif($pet->gender === 'female')
                                    <i class="bi bi-gender-female"></i>
                                    Самка
                                @else
                                    Неизвестно
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start">
                        <a href="{{ route('admin.pets.edit', $pet) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.pets.destroy', $pet) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить питомца ({{ $pet->name }})?');">
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

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new createTomSelect('#owner', {
            placeholder: 'Выберите владельца...',
        });
    });
</script>
@endpush
