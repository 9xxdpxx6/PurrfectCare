@extends('layouts.admin')

@section('title', 'Заказы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Заказы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Создать заказ
        </a>
    </div>
</div>

<form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Номер заказа, клиент, питомец, менеджер, заметки...">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="client" class="form-label mb-1">Клиент</label>
            <select name="client" id="client" class="form-select" data-url="{{ route('admin.orders.client-options') }}">
                <option value="">Все клиенты</option>
                @if(request('client'))
                    @php
                        $selectedClient = \App\Models\User::find(request('client'));
                    @endphp
                    @if($selectedClient)
                        <option value="{{ $selectedClient->id }}" selected>{{ $selectedClient->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="pet" class="form-label mb-1">Питомец</label>
            <select name="pet" id="pet" class="form-select" data-url="{{ route('admin.orders.pet-options') }}">
                <option value="">Все питомцы</option>
                @if(request('pet'))
                    @php
                        $selectedPet = \App\Models\Pet::with('client')->find(request('pet'));
                    @endphp
                    @if($selectedPet)
                        <option value="{{ $selectedPet->id }}" selected>{{ $selectedPet->name }} ({{ $selectedPet->client->name ?? 'Без владельца' }})</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="status" class="form-label mb-1">Статус</label>
            <select name="status" id="status" class="form-select" data-url="{{ route('admin.orders.status-options') }}">
                <option value="">Все статусы</option>
                @if(request('status'))
                    @php
                        $selectedStatus = \App\Models\Status::find(request('status'));
                    @endphp
                    @if($selectedStatus)
                        <option value="{{ $selectedStatus->id }}" selected>{{ $selectedStatus->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="branch" class="form-label mb-1">Филиал</label>
            <select name="branch" id="branch" class="form-select" data-url="{{ route('admin.orders.branch-options') }}">
                <option value="">Все филиалы</option>
                @if(request('branch'))
                    @php
                        $selectedBranch = \App\Models\Branch::find(request('branch'));
                    @endphp
                    @if($selectedBranch)
                        <option value="{{ $selectedBranch->id }}" selected>{{ $selectedBranch->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="manager" class="form-label mb-1">Менеджер</label>
            <select name="manager" id="manager" class="form-select" data-url="{{ route('admin.orders.manager-options') }}">
                <option value="">Все менеджеры</option>
                @if(request('manager'))
                    @php
                        $selectedManager = \App\Models\Employee::find(request('manager'));
                    @endphp
                    @if($selectedManager)
                        <option value="{{ $selectedManager->id }}" selected>{{ $selectedManager->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
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
                            <h5 class="card-title mb-1">Заказ #{{ $order->id }}</h5>
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
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-info" title="Просмотр">
                                <span class="d-none d-lg-inline-block">Просмотр</span>
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-warning" title="Редактировать">
                                <span class="d-none d-lg-inline-block">Редактировать</span>
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                    onclick="return confirm('Удалить заказ #{{ $order->id }}? Это действие нельзя отменить.');">
                                    <span class="d-none d-lg-inline-block">Удалить</span>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-cart-x display-1 text-muted"></i>
        <h4 class="mt-3">Заказы не найдены</h4>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новый заказ.</p>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Создать заказ
        </a>
    </div>
@endif

<div class="d-flex justify-content-center mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // TomSelect для фильтров
        new createTomSelect('#client', {
            placeholder: 'Выберите клиента...',
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

        new createTomSelect('#pet', {
            placeholder: 'Выберите питомца...',
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

        new createTomSelect('#status', {
            placeholder: 'Выберите статус...',
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

        new createTomSelect('#branch', {
            placeholder: 'Выберите филиал...',
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

        // Datepickers для дат
        createDatepicker('#created_at_from');
        createDatepicker('#created_at_to');
    });
</script>
@endpush 