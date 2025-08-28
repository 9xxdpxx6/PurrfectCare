@extends('layouts.admin')

@section('title', 'Препараты')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Препараты - {{ $items->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.drugs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить препарат</span>
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
            <label for="supplier" class="form-label mb-1">Поставщик</label>
            <select name="supplier" id="supplier" class="form-select tomselect" data-url="{{ route('admin.drugs.supplier-options') }}">
                @if(request('supplier'))
                    @php
                        $selectedSupplier = \App\Models\Supplier::find(request('supplier'));
                    @endphp
                    @if($selectedSupplier)
                        <option value="{{ $selectedSupplier->id }}" selected>{{ $selectedSupplier->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="unit" class="form-label mb-1">Единица измерения</label>
            <select name="unit" id="unit" class="form-select">
                <option value="">Все</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @if(request('unit') == $unit->id) selected @endif>{{ $unit->name }} ({{ $unit->symbol }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="prescription_required" class="form-label mb-1">Требует рецепт</label>
            <select name="prescription_required" id="prescription_required" class="form-control" data-tomselect>
                <option value="">Не важно</option>
                <option value="1" @if(request('prescription_required') == '1') selected @endif>Да</option>
                <option value="0" @if(request('prescription_required') == '0') selected @endif>Нет</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-control" data-tomselect>
                <option value="">По умолчанию</option>
                <option value="name_asc" @if(request('sort') == 'name_asc') selected @endif>По названию (А-Я)</option>
                <option value="name_desc" @if(request('sort') == 'name_desc') selected @endif>По названию (Я-А)</option>
                <option value="price_asc" @if(request('sort') == 'price_asc') selected @endif>По цене (дешевые)</option>
                <option value="price_desc" @if(request('sort') == 'price_desc') selected @endif>По цене (дорогие)</option>
                <option value="quantity_asc" @if(request('sort') == 'quantity_asc') selected @endif>По количеству (меньше)</option>
                <option value="quantity_desc" @if(request('sort') == 'quantity_desc') selected @endif>По количеству (больше)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.drugs.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $i => $drug)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title mb-3">
                            {{ $drug->name }}
                            @if($drug->prescription_required)
                                <i class="bi bi-prescription text-warning" data-bs-toggle="tooltip" data-bs-title="Только по рецепту!"></i>
                            @endif
                        </h5>
                        @if(!empty($drug->suppliers_display) && count($drug->suppliers_display))
                            <h6 class="card-subtitle mb-2 text-muted">
                                Поставщик{{ count($drug->suppliers_display) > 1 ? 'и' : '' }}: {{ implode(', ', $drug->suppliers_display) }}
                            </h6>
                        @else
                            <h6 class="card-subtitle mb-2 text-muted">Поставщики: —</h6>
                        @endif

                        @if($drug->latest_procurement)
                            <div class="d-flex flex-column gap-1">
                                <p class="card-text mb-0">
                                    <span>Изготовлен:</span> {{ $drug->latest_procurement->manufacture_date ? $drug->latest_procurement->manufacture_date->format('d.m.Y') : '—' }}
                                </p>
                                <p class="card-text mb-0">
                                    <span>Упакован:</span> {{ $drug->latest_procurement->packaging_date ? $drug->latest_procurement->packaging_date->format('d.m.Y') : '—' }}
                                </p>
                                <p class="card-text mb-0 @if($drug->latest_procurement->expiry_date && $drug->latest_procurement->expiry_date->lt(\Carbon\Carbon::now())) text-danger @elseif($drug->latest_procurement->expiry_date && $drug->latest_procurement->expiry_date->lte(\Carbon\Carbon::now()->addDays(30))) text-warning @endif">
                                    <span>Годен до:</span> {{ $drug->latest_procurement->expiry_date ? $drug->latest_procurement->expiry_date->format('d.m.Y') : '—' }}
                                </p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-1">
                                <p class="card-text mb-0"><span>Изготовлен:</span> —</p>
                                <p class="card-text mb-0"><span>Упакован:</span> —</p>
                                <p class="card-text mb-0"><span>Годен до:</span> —</p>
                            </div>
                        @endif
                    </div>

                    <div class="price-container d-flex flex-column align-items-lg-end align-self-start text-nowrap">
                        <p class="card-text">
                            <span>Цена:</span> {{ $drug->price !== null ? number_format($drug->price, 2, ',', ' ') . ' ₽' : '—' }}
                        </p>
                        <p class="card-text">
                            <span>Количество:</span> {{ $drug->quantity !== null ? $drug->quantity . ($drug->unit ? ' ' . $drug->unit->symbol : '') : '—' }}
                        </p>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap">
                        <a href="{{ route('admin.drugs.show', $drug) }}" class="btn btn-outline-info">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.drugs.edit', $drug) }}" class="btn btn-outline-warning">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.drugs.destroy', $drug) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Удалить препарат ({{ $drug->name }})?');">
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
        <i class="bi bi-capsule-pill display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Препараты не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новый препарат.</p>
        <a href="{{ route('admin.drugs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить препарат
        </a>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedSupplier = '{{ request("supplier") }}';
        
        // TomSelect для поставщиков с динамической загрузкой
        new createTomSelect('#supplier', {
            placeholder: 'Выберите поставщика...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Если есть выбранное значение и это первая загрузка, передаём его
                if (selectedSupplier && !query) {
                    url += '&selected=' + encodeURIComponent(selectedSupplier);
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
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // Обычный TomSelect для единиц измерения
        new createTomSelect('#unit', {
            placeholder: 'Выберите единицу...',
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        // TomSelect для поля "Требует рецепт"
        new createTomSelect('#prescription_required', {
            placeholder: 'Выберите...',
            plugins: ['remove_button'],
            allowEmptyOption: true,
            maxOptions: 5,
            persist: false
        });

        // TomSelect для поля сортировки
        new createTomSelect('#sort', {
            placeholder: 'Выберите сортировку...',
            plugins: ['remove_button'],
            allowEmptyOption: true,
            maxOptions: 10,
            persist: false
        });
    });
</script>
@endpush
