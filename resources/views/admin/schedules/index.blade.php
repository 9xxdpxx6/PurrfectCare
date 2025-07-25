@extends('layouts.admin')

@section('title', 'Расписания')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Расписания</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary me-2">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Добавить расписание</span>
        </a>
        <a href="{{ route('admin.schedules.create-week') }}" class="btn btn-success">
            <i class="bi bi-calendar-week"></i> <span class="d-none d-lg-inline">Расписание на неделю</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск по ветеринару, спецмальности..." value="{{ request('search') }}">
        </div>
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="veterinarian" class="form-label mb-1">Ветеринар</label>
            <select name="veterinarian" id="veterinarian" class="form-select tomselect" data-url="{{ route('admin.schedules.veterinarian-options') }}">
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
            <label for="branch" class="form-label mb-1">Филиал</label>
            <select name="branch" id="branch" class="form-select tomselect" data-url="{{ route('admin.schedules.branch-options') }}">
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
    </div>
    <div class="d-flex flex-wrap align-items-end gap-2 mt-2">
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="date_from" class="form-label mb-1">Дата с</label>
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
            <label for="date_to" class="form-label mb-1">Дата до</label>
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
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="date_asc" @if(request('sort') == 'date_asc') selected @endif>Дата (сначала старые)</option>
                <option value="date_desc" @if(request('sort') == 'date_desc') selected @endif>Дата (сначала новые)</option>
                <option value="veterinarian_asc" @if(request('sort') == 'veterinarian_asc') selected @endif>Ветеринар (А-Я)</option>
                <option value="veterinarian_desc" @if(request('sort') == 'veterinarian_desc') selected @endif>Ветеринар (Я-А)</option>
                <option value="branch_asc" @if(request('sort') == 'branch_asc') selected @endif>Филиал (А-Я)</option>
                <option value="branch_desc" @if(request('sort') == 'branch_desc') selected @endif>Филиал (Я-А)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $schedule)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <!-- Основная информация -->
                    <div class="d-flex flex-column justify-content-center flex-grow-1">
                        <h5 class="card-title mb-1">
                            <i class="bi bi-calendar3"></i>
                            {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->format('d.m.Y') }}
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($schedule->shift_ends_at)->format('H:i') }}
                            </span>
                        </h5>

                        <h6 class="card-subtitle mb-2 text-muted">
                            {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->locale('ru')->translatedFormat('l') }}
                        </h6>

                        <div class="d-flex flex-wrap gap-3">
                            <p class="card-text mb-0">
                                <strong>Ветеринар:</strong> {{ $schedule->veterinarian->name ?? 'Не указан' }}
                                @if($schedule->veterinarian && $schedule->veterinarian->specialization)
                                    <small class="text-muted">({{ $schedule->veterinarian->specialization }})</small>
                                @endif
                            </p>

                            <p class="card-text mb-0">
                                <strong>Филиал:</strong> {{ $schedule->branch->name ?? 'Не указан' }}
                            </p>
                        </div>

                        @php
                            $duration = \Carbon\Carbon::parse($schedule->shift_starts_at)->diffInHours(\Carbon\Carbon::parse($schedule->shift_ends_at));
                        @endphp
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Продолжительность: {{ $duration }} {{ $duration == 1 ? 'час' : ($duration < 5 ? 'часа' : 'часов') }}
                            </small>
                        </div>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start">
                        <a href="{{ route('admin.schedules.show', $schedule) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить расписание {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->format('d.m.Y H:i') }}?');">
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

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-calendar-x display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Расписания не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новое расписание.</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Добавить расписание
            </a>
            <a href="{{ route('admin.schedules.create-week') }}" class="btn btn-success">
                <i class="bi bi-calendar-week"></i> Расписание на неделю
            </a>
        </div>
    </div>
@endif

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedVeterinarian = '{{ request("veterinarian") }}';
        const selectedBranch = '{{ request("branch") }}';
        
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
        
        // TomSelect для филиалов с динамической загрузкой
        new createTomSelect('#branch', {
            placeholder: 'Выберите филиал...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                if (selectedBranch && !query) {
                    url += '&selected=' + encodeURIComponent(selectedBranch);
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
        createDatepicker('#date_from');
        createDatepicker('#date_to');
    });
</script>
@endpush 