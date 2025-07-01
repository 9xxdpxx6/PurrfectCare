@extends('layouts.admin')

@section('title', 'Услуги')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Услуги</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить услугу</span>
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
            <label for="branch" class="form-label mb-1">Филиал</label>
            <select name="branch" id="branch" class="form-select tomselect" data-url="{{ route('admin.services.branch-options') }}">
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
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="name_asc" @if(request('sort') == 'name_asc') selected @endif>По алфавиту (А-Я)</option>
                <option value="name_desc" @if(request('sort') == 'name_desc') selected @endif>По алфавиту (Я-А)</option>
                <option value="price_asc" @if(request('sort') == 'price_asc') selected @endif>По цене (дешевые)</option>
                <option value="price_desc" @if(request('sort') == 'price_desc') selected @endif>По цене (дорогие)</option>
                <option value="duration_asc" @if(request('sort') == 'duration_asc') selected @endif>По времени (короткие)</option>
                <option value="duration_desc" @if(request('sort') == 'duration_desc') selected @endif>По времени (длинные)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $i => $service)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title mb-3">{{ $service->name }}</h5>
                        @if($service->branches->count() > 0)
                            <h6 class="card-subtitle mb-2 text-muted">
                                Филиал{{ $service->branches->count() > 1 ? 'ы' : '' }}: {{ $service->branches->pluck('name')->implode(', ') }}
                            </h6>
                        @else
                            <h6 class="card-subtitle mb-2 text-muted">Филиалы: —</h6>
                        @endif

                        @if($service->description)
                            <p class="card-text mb-2">{{ Str::limit($service->description, 150) }}</p>
                        @else
                            <p class="card-text mb-2 text-muted">Описание не указано</p>
                        @endif

                        <div class="d-flex flex-column gap-1">
                            <p class="card-text mb-0">
                                <span>Продолжительность:</span> 
                                @if($service->duration)
                                    @if($service->duration >= 60)
                                        {{ intval($service->duration / 60) }} ч {{ $service->duration % 60 > 0 ? ($service->duration % 60) . ' мин' : '' }}
                                    @else
                                        {{ $service->duration }} мин
                                    @endif
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="price-container d-flex flex-column align-items-lg-end align-self-start text-nowrap">
                        <p class="card-text">
                            <span>Цена:</span> {{ $service->price !== null ? number_format($service->price, 2, ',', ' ') . ' ₽' : '—' }}
                        </p>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap">
                        <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить услугу ({{ $service->name }})?');">
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
        <i class="bi bi-bandaid display-1 text-muted"></i>
        <p class="text-muted mt-3">Услуги не найдены</p>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedBranch = '{{ request("branch") }}';
        
        // TomSelect для филиалов с динамической загрузкой
        new createTomSelect('#branch', {
            placeholder: 'Выберите филиал...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                if (selectedBranch && !query) {
                    url += '&selected=' + encodeURIComponent(selectedBranch);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        callback(json);
                        // НЕ вызываем setValue() - значение уже установлено в HTML
                    })
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            }
        });

        // Инициализация Bootstrap тултипов
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    });
</script>
@endpush 