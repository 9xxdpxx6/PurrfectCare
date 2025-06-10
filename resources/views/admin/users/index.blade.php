@extends('layouts.admin')

@section('title', 'Клиенты')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Клиенты</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex flex-row align-items-center gap-2 ms-lg-2 me-3">
            <span class="d-none d-lg-inline-block">Добавить клиента</span>
            <i class="bi bi-plus"></i>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Имя, email, телефон, адрес..." value="{{ request('search') }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label for="has_pets" class="form-label mb-1">Питомцы</label>
            <select name="has_pets" id="has_pets" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_pets') == '1') selected @endif>Есть питомцы</option>
                <option value="0" @if(request('has_pets') == '0') selected @endif>Нет питомцев</option>
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label for="has_orders" class="form-label mb-1">Заказы</label>
            <select name="has_orders" id="has_orders" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_orders') == '1') selected @endif>Есть заказы</option>
                <option value="0" @if(request('has_orders') == '0') selected @endif>Нет заказов</option>
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
            <label for="has_visits" class="form-label mb-1">Приемы</label>
            <select name="has_visits" id="has_visits" class="form-select">
                <option value="">Все</option>
                <option value="1" @if(request('has_visits') == '1') selected @endif>Были приемы</option>
                <option value="0" @if(request('has_visits') == '0') selected @endif>Не было приемов</option>
            </select>
        </div>
        <div class="col-md-6 col-lg-2">
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
        <div class="col-12 d-flex justify-content-end">
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
                </a>
                <button type="submit" class="btn btn-outline-primary">
                    <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $user)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">{{ $user->name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="bi bi-envelope"></i> {{ $user->email }}
                        </h6>

                        <div class="row g-1">
                            <div class="col-12 col-lg-6">
                                <p class="card-text mb-0">
                                    <i class="bi bi-telephone"></i> <span>Телефон:</span> {{ $user->phone }}
                                </p>
                            </div>
                            <div class="col-12 col-lg-6">
                                <p class="card-text mb-0">
                                    <i class="bi bi-calendar-plus"></i> <span>Регистрация:</span> {{ $user->created_at->format('d.m.Y') }}
                                </p>
                            </div>
                            @if($user->address)
                                <div class="col-12 col-lg-6">
                                    <p class="card-text mb-0">
                                        <i class="bi bi-geo-alt"></i> <span>Адрес:</span> {{ $user->address }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="stats-container d-flex flex-column align-items-lg-end align-self-start text-nowrap">
                        <p class="card-text mb-0">
                            <i class="bi bi-heart"></i> <span>Питомцы:</span> {{ $user->pets_count }}
                        </p>
                        <p class="card-text mb-0">
                            <i class="bi bi-bag"></i> <span>Заказы:</span> {{ $user->orders_count }}
                        </p>
                        <p class="card-text mb-0">
                            <i class="bi bi-calendar-check"></i> <span>Приемы:</span> {{ $user->visits_count }}
                        </p>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить клиента {{ $user->name }}? Это также удалит всех его питомцев!');">
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

@if($items->count() == 0)
    <div class="text-center py-5">
        <i class="bi bi-people display-1 text-muted"></i>
        <p class="text-muted mt-3">Клиенты не найдены</p>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection 