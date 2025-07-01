@extends('layouts.admin')

@section('title', 'Приёмы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Приёмы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.visits.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Создать приём</span>
        </a>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:180px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск по жалобам, заметкам, клиенту..." value="{{ request('search') }}">
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
                    <option value="{{ $pet->id }}" @if(request('pet') == $pet->id) selected @endif>{{ $pet->name }} ({{ $pet->client->name }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="status" class="form-label mb-1">Статус</label>
            <select name="status" id="status" class="form-select">
                <option value="">Все статусы</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @if(request('status') == $status->id) selected @endif>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-end gap-2 mt-2">
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="date_from" class="form-label mb-1">Дата с</label>
            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="flex-grow-1" style="min-width:140px;">
            <label for="date_to" class="form-label mb-1">Дата до</label>
            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-select">
                <option value="">По умолчанию</option>
                <option value="date_asc" @if(request('sort') == 'date_asc') selected @endif>Дата (сначала старые)</option>
                <option value="date_desc" @if(request('sort') == 'date_desc') selected @endif>Дата (сначала новые)</option>
                <option value="client_asc" @if(request('sort') == 'client_asc') selected @endif>Клиент (А-Я)</option>
                <option value="client_desc" @if(request('sort') == 'client_desc') selected @endif>Клиент (Я-А)</option>
                <option value="pet_asc" @if(request('sort') == 'pet_asc') selected @endif>Питомец (А-Я)</option>
                <option value="pet_desc" @if(request('sort') == 'pet_desc') selected @endif>Питомец (Я-А)</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($items as $visit)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm d-flex flex-lg-row align-items-lg-center @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">
                <div class="card-body h-100 flex-grow-1 d-flex flex-column flex-lg-row align-items-lg-center">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <div class="d-flex flex-row align-items-center gap-3 mb-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-check"></i>
                                {{ \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') }}
                            </h5>
                            @if($visit->status)
                                <span class="badge" style="background-color: {{ $visit->status->color ?? '#6c757d' }}">{{ $visit->status->name }}</span>
                            @endif
                        </div>
                        <div class="mt-auto">
                            <div class="d-flex flex-wrap gap-3">
                                <p class="card-text mb-0">
                                    <strong>Клиент:</strong> {{ $visit->client->name ?? 'Не указан' }}
                                </p>
                                <p class="card-text mb-0">
                                    <strong>Питомец:</strong> {{ $visit->pet->name ?? 'Не указан' }}
                                </p>
                                @if($visit->schedule && $visit->schedule->veterinarian)
                                    <p class="card-text mb-0">
                                        <strong>Ветеринар:</strong> {{ $visit->schedule->veterinarian->name ?? 'Не указан' }}
                                    </p>
                                @endif
                            </div>
                            @if($visit->complaints)
                                <div class="mt-2">
                                    <small><strong>Жалобы:</strong> {{ Str::limit($visit->complaints, 100) }}</small>
                                </div>
                            @endif
                            @if($visit->notes)
                                <div class="mt-1">
                                    <small><strong>Заметки:</strong> {{ Str::limit($visit->notes, 100) }}</small>
                                </div>
                            @endif
                            @if($visit->symptoms && $visit->symptoms->count() > 0)
                                <div class="mt-2">
                                    <small><strong>Симптомы:</strong>
                                        @foreach($visit->symptoms as $symptom)
                                            <span class="text-warning">{{ $symptom->getName() }}@if(!$loop->last), @endif</span>
                                        @endforeach
                                    </small>
                                </div>
                            @endif
                            @if($visit->diagnoses && $visit->diagnoses->count() > 0)
                                <div class="mt-2">
                                    <small><strong>Диагнозы:</strong>
                                        @foreach($visit->diagnoses as $diagnosis)
                                            <span class="text-info">{{ $diagnosis->getName() }}@if(!$loop->last), @endif</span>
                                        @endforeach
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
                        <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-info" title="Просмотр">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('admin.visits.edit', $visit) }}" class="btn btn-outline-warning" title="Редактировать">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.visits.destroy', $visit) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" title="Удалить"
                                onclick="return confirm('Удалить приём {{ \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') }}?');">
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
        <h3 class="mt-3 text-muted">Приёмы не найдены</h3>
        <p class="text-muted">Попробуйте изменить параметры поиска или создайте новый приём.</p>
        <a href="{{ route('admin.visits.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Создать приём
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
        new createTomSelect('#client', {
            placeholder: 'Выберите клиента...',
        });
        
        new createTomSelect('#pet', {
            placeholder: 'Выберите питомца...',
        });
        
        new createTomSelect('#status', {
            placeholder: 'Выберите статус...',
        });
    });
</script>
@endpush 