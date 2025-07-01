@extends('layouts.admin')

@section('title', 'Просмотр препарата')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 col-12 col-md-7 col-xl-8">Препарат: {{ $item->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drugs.edit', $item) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.drugs.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-capsule"></i> {{ $item->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-currency-dollar"></i> Цена:</strong>
                            {{ number_format($item->price, 2, ',', ' ') }} ₽
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-box"></i> Количество на складе:</strong>
                            {{ $item->quantity }}{{ $item->unit ? ' ' . $item->unit->symbol : '' }}
                        </p>
                        @if($item->unit)
                            <p class="mb-2">
                                <strong><i class="bi bi-rulers"></i> Единица измерения:</strong>
                                {{ $item->unit->name }} ({{ $item->unit->symbol }})
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-plus"></i> Добавлен:</strong>
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y H:i') }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar-check"></i> Обновлен:</strong>
                            {{ \Carbon\Carbon::parse($item->updated_at)->format('d.m.Y H:i') }}
                        </p>
                        @if($item->prescription_required)
                            <p class="mb-2">
                                <strong><i class="bi bi-exclamation-triangle text-warning"></i> Требуется рецепт</strong>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Поставки -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-truck"></i> Поставки ({{ $item->procurements->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($item->procurements->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Поставщик</th>
                                    <th>Дата поставки</th>
                                    <th>Количество</th>
                                    <th>Цена</th>
                                    <th>Срок годности</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->procurements as $procurement)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.suppliers.show', $procurement->supplier) }}" class="text-decoration-none">
                                                {{ $procurement->supplier->name }}
                                            </a>
                                        </td>
                                        <td>{{ $procurement->delivery_date->format('d.m.Y') }}</td>
                                        <td>{{ $procurement->quantity }}{{ $item->unit ? ' ' . $item->unit->symbol : '' }}</td>
                                        <td>{{ number_format($procurement->price, 2, ',', ' ') }} ₽</td>
                                        <td>
                                            <span class="@if($procurement->expiry_date->lt(\Carbon\Carbon::now())) text-danger @elseif($procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                                                {{ $procurement->expiry_date->format('d.m.Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($procurement->expiry_date->lt(\Carbon\Carbon::now()))
                                                <span class="badge bg-danger">Просрочен</span>
                                            @elseif($procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30)))
                                                <span class="badge bg-warning">Скоро истечет</span>
                                            @else
                                                <span class="badge bg-success">Годен</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Поставки не найдены</p>
                @endif
            </div>
        </div>
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
                    <span>Всего поставок:</span>
                    <strong>{{ $item->procurements->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Уникальных поставщиков:</span>
                    <strong>{{ $item->procurements->pluck('supplier')->unique('id')->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Общая стоимость:</span>
                    <strong>{{ number_format($item->procurements->sum('price'), 2, ',', ' ') }} ₽</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Общее количество:</span>
                    <strong>{{ $item->procurements->sum('quantity') }}{{ $item->unit ? ' ' . $item->unit->symbol : '' }}</strong>
                </div>
                @if($item->procurements->count() > 0)
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Последняя поставка:</span>
                        <strong>{{ $item->procurements->first()->delivery_date->format('d.m.Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Ближайший срок годности:</span>
                        <strong class="@if($item->procurements->min('expiry_date')->lt(\Carbon\Carbon::now())) text-danger @elseif($item->procurements->min('expiry_date')->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                            {{ $item->procurements->min('expiry_date')->format('d.m.Y') }}
                        </strong>
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
                    <a href="{{ route('admin.drug-procurements.create', ['drug' => $item->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus"></i> Добавить поставку
                    </a>
                    <a href="{{ route('admin.drugs.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <hr>
                    <form action="{{ route('admin.drugs.destroy', $item) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Вы уверены, что хотите удалить препарат?')">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 