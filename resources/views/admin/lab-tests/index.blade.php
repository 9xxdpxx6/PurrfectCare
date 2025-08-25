@extends('layouts.admin')

@section('title', 'Анализы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Анализы - {{ $items->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.lab-tests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить анализ</span>
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
            <label for="pet" class="form-label mb-1">Питомец</label>
            <select name="pet" id="pet" class="form-select tomselect" data-url="{{ route('admin.lab-tests.pet-options') }}">
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
            <select name="veterinarian" id="veterinarian" class="form-select tomselect" data-url="{{ route('admin.lab-tests.veterinarian-options') }}">
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
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="lab_test_type" class="form-label mb-1">Тип анализа</label>
            <select name="lab_test_type" id="lab_test_type" class="form-select tomselect" data-url="{{ route('admin.lab-tests.lab-test-type-options') }}">
                @if(request('lab_test_type'))
                    @php
                        $selectedLabTestType = \App\Models\LabTestType::find(request('lab_test_type'));
                    @endphp
                    @if($selectedLabTestType)
                        <option value="{{ $selectedLabTestType->id }}" selected>{{ $selectedLabTestType->name }}</option>
                    @endif
                @endif
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="received_at_desc" @if(request('sort') == 'received_at_desc') selected @endif>Дата получения (новые)</option>
                <option value="received_at_asc" @if(request('sort') == 'received_at_asc') selected @endif>Дата получения (старые)</option>
                <option value="completed_at_desc" @if(request('sort') == 'completed_at_desc') selected @endif>Дата завершения (новые)</option>
                <option value="completed_at_asc" @if(request('sort') == 'completed_at_asc') selected @endif>Дата завершения (старые)</option>
                <option value="pet_name_asc" @if(request('sort') == 'pet_name_asc') selected @endif>Питомец (А-Я)</option>
                <option value="pet_name_desc" @if(request('sort') == 'pet_name_desc') selected @endif>Питомец (Я-А)</option>
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="received_at_from" class="form-label mb-1">Дата получения с</label>
            @php
                $receivedAtFrom = request('received_at_from');
                if ($receivedAtFrom) {
                    try {
                        $receivedAtFrom = \Carbon\Carbon::parse($receivedAtFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $receivedAtFrom = $receivedAtFrom;
                    }
                }
            @endphp
            <input type="text" name="received_at_from" id="received_at_from" class="form-control" value="{{ $receivedAtFrom }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="received_at_to" class="form-label mb-1">Дата получения до</label>
            @php
                $receivedAtTo = request('received_at_to');
                if ($receivedAtTo) {
                    try {
                        $receivedAtTo = \Carbon\Carbon::parse($receivedAtTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $receivedAtTo = $receivedAtTo;
                    }
                }
            @endphp
            <input type="text" name="received_at_to" id="received_at_to" class="form-control" value="{{ $receivedAtTo }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="completed_at_from" class="form-label mb-1">Дата завершения с</label>
            @php
                $completedAtFrom = request('completed_at_from');
                if ($completedAtFrom) {
                    try {
                        $completedAtFrom = \Carbon\Carbon::parse($completedAtFrom)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $completedAtFrom = $completedAtFrom;
                    }
                }
            @endphp
            <input type="text" name="completed_at_from" id="completed_at_from" class="form-control" value="{{ $completedAtFrom }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="completed_at_to" class="form-label mb-1">Дата завершения до</label>
            @php
                $completedAtTo = request('completed_at_to');
                if ($completedAtTo) {
                    try {
                        $completedAtTo = \Carbon\Carbon::parse($completedAtTo)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $completedAtTo = $completedAtTo;
                    }
                }
            @endphp
            <input type="text" name="completed_at_to" id="completed_at_to" class="form-control" value="{{ $completedAtTo }}" readonly placeholder="дд.мм.гггг">
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.lab-tests.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $labTest)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                    <div class="d-flex flex-column h-100 flex-lg-row w-100 gap-3 align-items-lg-center">
                        <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                            <h5 class="card-title mb-1">{{ $labTest->labTestType->name }}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                Питомец: {{ $labTest->pet->name }} ({{ $labTest->pet->client->name ?? 'Без владельца' }})
                            </h6>
                            <div class="row w-100 g-1">
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-muted">Получен:&nbsp;</span>
                                        <span>{{ $labTest->received_at->format('d.m.Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">Ветеринар:&nbsp;</span>
                                        <span>{{ $labTest->veterinarian->name }}</span>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="text-muted">Завершен:&nbsp;</span>
                                        <span>{{ $labTest->completed_at ? $labTest->completed_at->format('d.m.Y') : 'Не завершен' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted">Результатов:&nbsp;</span>
                                        <span>{{ $labTest->results->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start text-nowrap">
                            <a href="{{ route('admin.lab-tests.show', $labTest) }}" class="btn btn-outline-info">
                                <span class="d-none d-lg-inline-block">Просмотр</span>
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.lab-tests.edit', $labTest) }}" class="btn btn-outline-warning">
                                <span class="d-none d-lg-inline-block">Редактировать</span>
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.lab-tests.destroy', $labTest) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Удалить анализ от {{ $labTest->received_at->format('d.m.Y') }}?');">
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

@if($items->count() == 0)
    <div class="text-center py-5">
        <i class="bi bi-clipboard-data display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Анализы не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новый анализ.</p>
        <a href="{{ route('admin.lab-tests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Добавить анализ
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
        const selectedPet = '{{ request("pet") }}';
        const selectedVeterinarian = '{{ request("veterinarian") }}';
        const selectedLabTestType = '{{ request("lab_test_type") }}';
        
        // TomSelect для питомцев с динамической загрузкой
        new createTomSelect('#pet', {
            placeholder: 'Выберите питомца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
                
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
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
                
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
        
        // TomSelect для типов анализов с динамической загрузкой
        new createTomSelect('#lab_test_type', {
            placeholder: 'Выберите тип анализа...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
                
                if (selectedLabTestType && !query) {
                    url += '&selected=' + encodeURIComponent(selectedLabTestType);
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

        // Air Datepickers
        createDatepicker('#received_at_from');
        createDatepicker('#received_at_to');
        createDatepicker('#completed_at_from');
        createDatepicker('#completed_at_to');
    });
</script>
@endpush 