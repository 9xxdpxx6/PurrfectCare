@extends('layouts.admin')

@section('title', 'Вакцинации')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Вакцинации - {{ $items->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('vaccinations.create')
        <a href="{{ route('admin.vaccinations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить вакцинацию</span>
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск..." value="{{ request('search') }}">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="pet" class="form-label mb-1">Питомец</label>
            <select name="pet" id="pet" class="form-select tomselect" data-url="{{ route('admin.vaccinations.pet-options') }}">
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
            <label for="veterinarian" class="form-label mb-1">Ветеринар</label>
            <select name="veterinarian" id="veterinarian" class="form-select tomselect" data-url="{{ route('admin.vaccinations.veterinarian-options') }}">
                @if(request('veterinarian'))
                    @php
                        $selectedVeterinarian = \App\Models\Employee::find(request('veterinarian'));
                    @endphp
                    @if($selectedVeterinarian)
                        <option value="{{ $selectedVeterinarian->id }}" selected>{{ $selectedVeterinarian->name }}</option>
                    @endif
                @endif
            </select>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-end gap-2 mt-2">
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="date_from" class="form-label mb-1">Дата проведения с</label>
            @php
                $dateFrom = request('date_from');
                if ($dateFrom) {
                    try {
                        $dateFrom = \Carbon\Carbon::parse($dateFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $dateFrom = '';
                    }
                }
            @endphp
            <input type="text" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="date_to" class="form-label mb-1">Дата проведения до</label>
            @php
                $dateTo = request('date_to');
                if ($dateTo) {
                    try {
                        $dateTo = \Carbon\Carbon::parse($dateTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $dateTo = '';
                    }
                }
            @endphp
            <input type="text" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="next_due_from" class="form-label mb-1">Следующая с</label>
            @php
                $nextDueFrom = request('next_due_from');
                if ($nextDueFrom) {
                    try {
                        $nextDueFrom = \Carbon\Carbon::parse($nextDueFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $nextDueFrom = '';
                    }
                }
            @endphp
            <input type="text" name="next_due_from" id="next_due_from" class="form-control" value="{{ $nextDueFrom }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="next_due_to" class="form-label mb-1">Следующая до</label>
            @php
                $nextDueTo = request('next_due_to');
                if ($nextDueTo) {
                    try {
                        $nextDueTo = \Carbon\Carbon::parse($nextDueTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $nextDueTo = '';
                    }
                }
            @endphp
            <input type="text" name="next_due_to" id="next_due_to" class="form-control" value="{{ $nextDueTo }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-control" data-tomselect>
                <option value="">По умолчанию</option>
                <option value="date_asc" @if(request('sort') == 'date_asc') selected @endif>Дата проведения (по возрастанию)</option>
                <option value="date_desc" @if(request('sort') == 'date_desc') selected @endif>Дата проведения (по убыванию)</option>
                <option value="next_due_asc" @if(request('sort') == 'next_due_asc') selected @endif>Следующая (по возрастанию)</option>
                <option value="next_due_desc" @if(request('sort') == 'next_due_desc') selected @endif>Следующая (по убыванию)</option>
                <option value="pet_asc" @if(request('sort') == 'pet_asc') selected @endif>Питомец (А-Я)</option>
                <option value="pet_desc" @if(request('sort') == 'pet_desc') selected @endif>Питомец (Я-А)</option>
                <option value="veterinarian_asc" @if(request('sort') == 'veterinarian_asc') selected @endif>Ветеринар (А-Я)</option>
                <option value="veterinarian_desc" @if(request('sort') == 'veterinarian_desc') selected @endif>Ветеринар (Я-А)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $vaccination)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row align-items-lg-center">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <div class="d-flex flex-row align-items-center gap-3 mb-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-lock"></i>
                                {{ $vaccination->administered_at->format('d.m.Y') }}
                            </h5>
                            @if($vaccination->next_due)
                                <span class="badge bg-secondary">
                                    Следующая: {{ $vaccination->next_due->format('d.m.Y') }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex flex-column">
                                <p class="card-text mb-1">
                                    <strong>Питомец:</strong> {{ $vaccination->pet->name ?? 'Не указан' }}
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Владелец:</strong> {{ $vaccination->pet->client->name ?? 'Не указан' }}
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Ветеринар:</strong> {{ $vaccination->veterinarian->name ?? 'Не указан' }}
                                </p>
                            </div>
                            @if($vaccination->vaccinationType && $vaccination->vaccinationType->drugs && $vaccination->vaccinationType->drugs->count() > 0)
                                <div class="mt-2">
                                    <small><strong>Препараты:</strong>
                                        @foreach($vaccination->vaccinationType->drugs as $drug)
                                            <span class="text-muted">{{ $drug->name }}@if(!$loop->last), @endif</span>
                                        @endforeach
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                        @can('vaccinations.read')
                        <a href="{{ route('admin.vaccinations.show', $vaccination) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        @endcan
                        @can('vaccinations.update')
                        <a href="{{ route('admin.vaccinations.edit', $vaccination) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('vaccinations.delete')
                        <form action="{{ route('admin.vaccinations.destroy', $vaccination) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить вакцинацию от {{ $vaccination->administered_at->format('d.m.Y') }}?');">
                                <span class="d-none d-lg-inline-block">Удалить</span>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-shield-x display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Вакцинации не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новую вакцинацию.</p>
        @can('vaccinations.create')
        <a href="{{ route('admin.vaccinations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить вакцинацию
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
        const selectedPet = '{{ request("pet") }}';
        const selectedVeterinarian = '{{ request("veterinarian") }}';
        
        // TomSelect для питомцев с динамической загрузкой
        new createTomSelect('#pet', {
            placeholder: 'Выберите питомца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                if (selectedPet && !query) {
                    url += '&selected=' + encodeURIComponent(selectedPet);
                }
                
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
        
        // TomSelect для ветеринаров с динамической загрузкой
        new createTomSelect('#veterinarian', {
            placeholder: 'Выберите ветеринара...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                if (selectedVeterinarian && !query) {
                    url += '&selected=' + encodeURIComponent(selectedVeterinarian);
                }
                
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

        // TomSelect для поля сортировки
        new createTomSelect('#sort', {
            placeholder: 'Выберите сортировку...',
            plugins: ['remove_button'],
            allowEmptyOption: true,
            maxOptions: 10,
            persist: false
        });

        // Air Datepickers
        createDatepicker('#date_from');
        createDatepicker('#date_to');
        createDatepicker('#next_due_from');
        createDatepicker('#next_due_to');
    });
</script>
@endpush 