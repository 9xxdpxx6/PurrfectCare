@extends('layouts.admin')

@section('title', 'Заказы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Заказы - {{ $items->total() }}</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                    @can('orders.export')
                    <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-lg-inline">Экспорт заказов</span>
                    </a>
                    @endcan
                    @can('orders.create')
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Добавить заказ
                    </a>
                    @endcan
                </div>
</div>

<form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Поиск...">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="client" class="form-label mb-1">Клиент</label>
            <select name="client" id="client" class="form-select">
                <option value="">Все клиенты</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @if(request('client') == $client->id) selected @endif>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="pet" class="form-label mb-1">Питомец</label>
            <select name="pet" id="pet" class="form-select">
                <option value="">Все питомцы</option>
                @foreach($pets as $pet)
                    <option value="{{ $pet->id }}" @if(request('pet') == $pet->id) selected @endif>{{ $pet->name }} ({{ $pet->client ? $pet->client->name : 'Без владельца' }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="status" class="form-label mb-1">Статус</label>
            <select name="status" id="status" class="form-select">
                <option value="">Все статусы</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @if(request('status') == $status->id) selected @endif>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="branch" class="form-label mb-1">Филиал</label>
            <select name="branch" id="branch" class="form-select">
                <option value="">Все филиалы</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @if(request('branch') == $branch->id) selected @endif>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-control" data-tomselect>
                <option value="">По умолчанию</option>
                <option value="created_at_desc" @if(request('sort') == 'created_at_desc') selected @endif>Дата создания (новые)</option>
                <option value="created_at_asc" @if(request('sort') == 'created_at_asc') selected @endif>Дата создания (старые)</option>
                <option value="total_desc" @if(request('sort') == 'total_desc') selected @endif>Сумма (большие)</option>
                <option value="total_asc" @if(request('sort') == 'total_asc') selected @endif>Сумма (малые)</option>
                <option value="client_name_asc" @if(request('sort') == 'client_name_asc') selected @endif>Клиент (А-Я)</option>
                <option value="client_name_desc" @if(request('sort') == 'client_name_desc') selected @endif>Клиент (Я-А)</option>
                <option value="pet_name_asc" @if(request('sort') == 'pet_name_asc') selected @endif>Питомец (А-Я)</option>
                <option value="pet_name_desc" @if(request('sort') == 'pet_name_desc') selected @endif>Питомец (Я-А)</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="created_at_from" class="form-label mb-1">Дата создания с</label>
            @php
                $createdAtFrom = request('created_at_from');
                if ($createdAtFrom) {
                    try {
                        $createdAtFrom = \Carbon\Carbon::parse($createdAtFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $createdAtFrom = $createdAtFrom;
                    }
                }
            @endphp
            <input type="text" name="created_at_from" id="created_at_from" class="form-control" value="{{ $createdAtFrom }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="created_at_to" class="form-label mb-1">Дата создания до</label>
            @php
                $createdAtTo = request('created_at_to');
                if ($createdAtTo) {
                    try {
                        $createdAtTo = \Carbon\Carbon::parse($createdAtTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $createdAtTo = $createdAtTo;
                    }
                }
            @endphp
            <input type="text" name="created_at_to" id="created_at_to" class="form-control" value="{{ $createdAtTo }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="total_from" class="form-label mb-1">Сумма от</label>
            <input type="number" name="total_from" id="total_from" class="form-control" value="{{ request('total_from') }}" placeholder="0" min="0" step="0.01">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="total_to" class="form-label mb-1">Сумма до</label>
            <input type="number" name="total_to" id="total_to" class="form-control" value="{{ request('total_to') }}" placeholder="999999" min="0" step="0.01">
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $order)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="d-flex flex-column h-100 flex-lg-row w-100 gap-3 align-items-lg-center">
                        <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                            <h5 class="card-title mb-1">
                                @if($order->is_paid)
                                    <span class="text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Оплачен">
                                        <i class="bi bi-check-all"></i>
                                    </span>
                                @else
                                    <span class="text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Не оплачен">
                                        <i class="bi bi-cash"></i>
                                    </span>
                                @endif
                                Заказ #{{ $order->id }}
                                @if($order->visits && $order->visits->count() > 0)
                                    <i class="bi bi-calendar-check text-info ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Есть приёмы"></i>
                                @endif
                                @if($order->closed_at)
                                    <span class="text-muted fs-6 ms-2">Завершен {{ $order->closed_at->format('d.m.Y H:i') }}</span>
                                @else
                                    <span class="text-muted fs-6 ms-2">в работе</span>
                                @endif
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                Клиент: {{ $order->client->name }} | Питомец: {{ $order->pet->name }}
                            </h6>
                            <div class="row w-100 g-1">
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex mb-1">
                                        <span class="text-muted">Менеджер:&nbsp;</span>
                                        <span>{{ $order->manager->name }}</span>
                                    </div>
                                    <div class="d-flex mb-1">
                                        <span class="text-muted">Филиал:&nbsp;</span>
                                        <span>{{ $order->branch->name }}</span>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex mb-1">
                                        <span class="text-muted">Статус:&nbsp;</span>
                                        <span class="badge" style="background-color: {{ $order->status->color ?? '#6c757d' }}; color: white;">{{ $order->status->name }}</span>
                                    </div>
                                    <div class="d-flex mb-1">
                                        <span class="text-muted">Создан:&nbsp;</span>
                                        <span>{{ $order->created_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column h-100">
                            <div class="text-start">
                                <div class="h5 mb-0">{{ number_format($order->total, 2, ',', ' ') }} ₽</div>
                            </div>
                        </div>

                        <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap mt-3 mt-lg-0">
                            @can('orders.read')
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-info">
                                <span class="d-none d-lg-inline-block">Просмотр</span>
                                <i class="bi bi-eye"></i>
                            </a>
                            @endcan
                            @can('orders.update')
                            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-warning">
                                <span class="d-none d-lg-inline-block">Редактировать</span>
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('orders.delete')
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Удалить заказ #{{ $order->id }}? Это действие нельзя отменить.');">
                                    <span class="d-none d-lg-inline-block">Удалить</span>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-bag-x display-1 text-muted"></i>
        <h4 class="mt-3">Заказы не найдены</h4>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новый заказ.</p>
        @can('orders.create')
        <a href="{{ route('admin.orders.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Добавить заказ
        </a>
        @endcan
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Обычный TomSelect для клиентов (без динамической загрузки)
        if (document.querySelector('#client')) {
            new createTomSelect('#client', {
                placeholder: 'Выберите клиента...',
            });
        }

        // Обычный TomSelect для питомцев (без динамической загрузки)
        if (document.querySelector('#pet')) {
            new createTomSelect('#pet', {
                placeholder: 'Выберите питомца...',
            });
        }

        // Обычный TomSelect для статусов (без динамической загрузки)
        if (document.querySelector('#status')) {
            new createTomSelect('#status', {
                placeholder: 'Выберите статус...',
            });
        }

        // Обычный TomSelect для филиалов (без динамической загрузки)
        if (document.querySelector('#branch')) {
            new createTomSelect('#branch', {
                placeholder: 'Выберите филиал...',
            });
        }

        if (document.querySelector('#manager')) {
            new createTomSelect('#manager', {
                placeholder: 'Выберите менеджера...',
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                load: function(query, callback) {
                    let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
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
        }

        // TomSelect для поля сортировки
        new createTomSelect('#sort', {
            placeholder: 'Выберите сортировку...',
            plugins: ['remove_button'],
            allowEmptyOption: true,
            maxOptions: 10,
            persist: false
        });

        // Datepickers для дат с проверкой существования элементов
        if (document.querySelector('#created_at_from')) {
            createDatepicker('#created_at_from');
        }
        if (document.querySelector('#created_at_to')) {
            createDatepicker('#created_at_to');
        }
    });
</script>
@endpush 