@extends('layouts.admin')

@section('title', 'Питомец: ' . $pet->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Питомец: {{ $pet->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.pets.edit', $pet) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-paw"></i> Информация о питомце</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Имя:</div><div class="col-sm-8">{{ $pet->name }}</div></div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Владелец:</div>
                    <div class="col-sm-8">
                        @if($pet->client)
                            <a href="{{ route('admin.users.show', $pet->client) }}">{{ $pet->client->name }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Порода:</div><div class="col-sm-8">{{ $pet->breed->name ?? '-' }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Вид:</div><div class="col-sm-8">{{ $pet->breed->species->name ?? '-' }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Дата рождения:</div><div class="col-sm-8">{{ $pet->birthdate ? $pet->birthdate->format('d.m.Y') : '-' }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Пол:</div><div class="col-sm-8">@lang('pet.gender.' . $pet->gender)</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Вес:</div><div class="col-sm-8">{{ $pet->weight ? $pet->weight . ' кг' : '-' }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Температура:</div><div class="col-sm-8">{{ $pet->temperature ? $pet->temperature . ' °C' : '-' }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Дата создания:</div><div class="col-sm-8">{{ $pet->created_at->format('d.m.Y H:i') }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up"></i> Статистика</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-6 fw-bold">Посещений:</div><div class="col-sm-6">{{ $pet->visits->count() }}</div></div>
                <div class="row mb-3"><div class="col-sm-6 fw-bold">Заказов:</div><div class="col-sm-6">{{ $pet->orders->count() }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-calendar-check"></i> Последние посещения</h5></div>
            <div class="card-body">
                @if($pet->visits->count())
                    <ul class="list-group list-group-flush">
                        @foreach($pet->visits as $visit)
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
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-bag"></i> Последние заказы</h5></div>
            <div class="card-body">
                @if($pet->orders->count())
                    <ul class="list-group list-group-flush">
                        @foreach($pet->orders as $order)
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
@endsection 