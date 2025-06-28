@extends('layouts.admin')

@section('title', 'Клиент: ' . $user->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Клиент: {{ $user->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person"></i> Информация о клиенте</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Имя:</div><div class="col-sm-8">{{ $user->name }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Email:</div><div class="col-sm-8">{{ $user->email }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Телефон:</div><div class="col-sm-8">{{ $user->phone }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Адрес:</div><div class="col-sm-8">{{ $user->address }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Дата регистрации:</div><div class="col-sm-8">{{ $user->created_at->format('d.m.Y H:i') }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up"></i> Статистика</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-6 fw-bold">Питомцев:</div><div class="col-sm-6">{{ $user->pets->count() }}</div></div>
                <div class="row mb-3"><div class="col-sm-6 fw-bold">Заказов:</div><div class="col-sm-6">{{ $user->orders->count() }}</div></div>
                <div class="row mb-3"><div class="col-sm-6 fw-bold">Посещений:</div><div class="col-sm-6">{{ $user->visits->count() }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-paw"></i> Последние питомцы</h5>
                <a href="{{ route('admin.pets.create', ['owner' => $user->id]) }}" class="btn btn-sm btn-outline-primary float-end">
                    <i class="bi bi-plus"></i> Добавить питомца
                </a>
            </div>
            <div class="card-body">
                @if($user->pets->count())
                    <ul class="list-group list-group-flush">
                        @foreach($user->pets as $pet)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $pet->name }}</span>
                                <a href="{{ route('admin.pets.show', $pet) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">Нет питомцев</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-bag"></i> Последние заказы</h5>
                <a href="{{ route('admin.orders.create', ['client' => $user->id]) }}" class="btn btn-sm btn-outline-primary float-end">
                    <i class="bi bi-plus"></i> Добавить заказ
                </a>
            </div>
            <div class="card-body">
                @if($user->orders->count())
                    <ul class="list-group list-group-flush">
                        @foreach($user->orders as $order)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Заказ #{{ $order->id }} от {{ $order->created_at->format('d.m.Y') }}</span>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">Нет заказов</div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-check"></i> Последние посещения</h5>
                <a href="{{ route('admin.visits.create', ['client' => $user->id]) }}" class="btn btn-sm btn-outline-primary float-end">
                    <i class="bi bi-plus"></i> Записать на приём
                </a>
            </div>
            <div class="card-body">
                @if($user->visits->count())
                    <ul class="list-group list-group-flush">
                        @foreach($user->visits as $visit)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Посещение #{{ $visit->id }} от {{ $visit->created_at->format('d.m.Y') }}</span>
                                <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">Нет посещений</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 