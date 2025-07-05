@extends('layouts.admin')

@section('title', 'Просмотр услуги')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Услуга: {{ $item->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.services.edit', $item) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Основная информация -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> {{ $item->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-cash-stack"></i> Цена:</strong>
                            {{ $item->price !== null ? number_format($item->price, 2, ',', ' ') . ' ₽' : '—' }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-clock"></i> Продолжительность:</strong>
                            @if($item->duration)
                                @if($item->duration >= 60)
                                    {{ intval($item->duration / 60) }} ч {{ $item->duration % 60 > 0 ? ($item->duration % 60) . ' мин' : '' }}
                                @else
                                    {{ $item->duration }} мин
                                @endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Дата добавления:</strong>
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y H:i') }}
                        </p>
                    </div>
                </div>
                @if($item->description)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-2">
                                <strong><i class="bi bi-card-text"></i> Описание:</strong>
                            </p>
                            <p class="text-muted mb-0">{{ $item->description }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($item->branches->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Филиалы</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($item->branches as $branch)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-1">{{ $branch->name }}</h6>
                                    @if($branch->address)
                                        <p class="text-muted mb-1">{{ $branch->address }}</p>
                                    @endif
                                    @if($branch->phone)
                                        <p class="text-muted mb-0">{{ $branch->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Статистика -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Статистика
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Оказано раз:</span>
                    <strong>{{ $ordersCount }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Доступна в филиалах:</span>
                    <strong>{{ $item->branches->count() }}</strong>
                </div>
                @if($ordersCount > 0)
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Оказывается с:</span>
                        <strong>{{ $firstOrderDate ? \Carbon\Carbon::parse($firstOrderDate)->format('d.m.Y') : '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Последнее оказание:</span>
                        <strong>{{ $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate)->format('d.m.Y') : '—' }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Действия -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Действия
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.services.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать услугу
                    </a>

                    <hr>

                    <form action="{{ route('admin.services.destroy', $item) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить услугу?')">
                            <i class="bi bi-trash"></i> Удалить услугу
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 