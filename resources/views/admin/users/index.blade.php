@extends('layouts.admin')

@section('title', 'Клиенты')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Клиенты - {{ $items->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить клиента</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск..." value="{{ request('search') }}">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="has_pets" class="form-label mb-1">Питомцы</label>
            <select name="has_pets" id="has_pets" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_pets') == '1') selected @endif>Есть питомцы</option>
                <option value="0" @if(request('has_pets') == '0') selected @endif>Нет питомцев</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="has_orders" class="form-label mb-1">Заказы</label>
            <select name="has_orders" id="has_orders" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_orders') == '1') selected @endif>Есть заказы</option>
                <option value="0" @if(request('has_orders') == '0') selected @endif>Нет заказов</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="has_visits" class="form-label mb-1">Приемы</label>
            <select name="has_visits" id="has_visits" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_visits') == '1') selected @endif>Были приемы</option>
                <option value="0" @if(request('has_visits') == '0') selected @endif>Не было приемов</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="name_asc" @if(request('sort') == 'name_asc') selected @endif>По имени (А-Я)</option>
                <option value="name_desc" @if(request('sort') == 'name_desc') selected @endif>По имени (Я-А)</option>
                <option value="email_asc" @if(request('sort') == 'email_asc') selected @endif>По email (А-Я)</option>
                <option value="email_desc" @if(request('sort') == 'email_desc') selected @endif>По email (Я-А)</option>
                <option value="created_desc" @if(request('sort') == 'created_desc') selected @endif>Регистрация (новые)</option>
                <option value="created_asc" @if(request('sort') == 'created_asc') selected @endif>Регистрация (старые)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $user)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title mb-3">{{ $user->name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="bi bi-envelope"></i> {{ $user->email }}
                        </h6>
                        <div class="w-100">
                            <div class="text-muted mb-1">
                                <i class="bi bi-telephone"></i> Телефон: {{ $user->phone }}
                            </div>
                            @if($user->address)
                                <div class="text-muted mb-1">
                                    <i class="bi bi-geo-alt"></i> Адрес: {{ $user->address }}
                                </div>
                            @endif
                            <div class="text-muted">
                                <i class="bi bi-calendar-plus"></i> Клиент с {{ \Carbon\Carbon::parse($user->created_at)->format('d.m.Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="stats-container d-flex flex-column align-items-lg-end align-self-start text-nowrap">
                        <p class="card-text">
                            <i class="bi bi-heart"></i> <span>Питомцы:</span> {{ $user->pets_count }}
                        </p>
                        <p class="card-text">
                            <i class="bi bi-bag"></i> <span>Заказы:</span> {{ $user->orders_count }}
                        </p>
                        <p class="card-text">
                            <i class="bi bi-calendar-check"></i> <span>Приемы:</span> {{ $user->visits_count }}
                        </p>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info text-nowrap" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning text-nowrap" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 text-nowrap" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить запись?')">
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
        <i class="bi bi-incognito display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Клиенты не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте нового клиента.</p>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить клиента
        </a>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection 