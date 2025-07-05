@extends('layouts.admin')

@section('title', 'Просмотр расписания')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Расписание {{ $item->shift_starts_at->format('d.m.Y') }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.edit', $item) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-calendar3"></i> Основная информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-6">
                        <table class="table table-borderless w-100">
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-calendar3"></i> 
                                    <span class="d-none d-md-inline">Дата:</span>
                                    <span class="d-md-none">Дата:</span>
                                </td>
                                <td style="width: 50%;">{{ $item->shift_starts_at->format('d.m.Y') }}
                                    <span class="badge bg-secondary">{{ $item->shift_starts_at->locale('ru')->translatedFormat('l') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-clock"></i> 
                                    <span class="d-none d-md-inline">Время начала:</span>
                                    <span class="d-md-none">Начало:</span>
                                </td>
                                <td style="width: 50%;">{{ $item->shift_starts_at->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-clock-fill"></i> 
                                    <span class="d-none d-md-inline">Время окончания:</span>
                                    <span class="d-md-none">Конец:</span>
                                </td>
                                <td style="width: 50%;">{{ $item->shift_ends_at->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-hourglass-split"></i> 
                                    <span class="d-none d-md-inline">Длительность:</span>
                                    <span class="d-md-none">Длит.:</span>
                                </td>
                                <td style="width: 50%;">
                                    @php
                                        $duration = $item->shift_starts_at->diffInHours($item->shift_ends_at);
                                    @endphp
                                    {{ $duration }} {{ $duration == 1 ? 'час' : ($duration < 5 ? 'часа' : 'часов') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-xl-6">
                        <table class="table table-borderless w-100">
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-person-check"></i> 
                                    <span class="d-none d-md-inline">Ветеринар:</span>
                                    <span class="d-md-none">Врач:</span>
                                </td>
                                <td style="width: 50%;">{{ $item->veterinarian->name ?? 'Не указан' }}
                                    @if($item->veterinarian && $item->veterinarian->specialization)
                                        <br><small class="text-muted">{{ $item->veterinarian->specialization }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-building"></i> 
                                    <span class="d-none d-md-inline">Филиал:</span>
                                    <span class="d-md-none">Филиал:</span>
                                </td>
                                <td style="width: 50%;">{{ $item->branch->name ?? 'Не указан' }}
                                    @if($item->branch && $item->branch->address)
                                        <br><small class="text-muted">{{ $item->branch->address }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold" style="width: 50%;">
                                    <i class="bi bi-activity"></i> 
                                    <span class="d-none d-md-inline">Статус:</span>
                                    <span class="d-md-none">Статус:</span>
                                </td>
                                <td style="width: 50%;">
                                    @if($item->shift_ends_at < now())
                                        <span class="badge bg-secondary">Завершено</span>
                                    @elseif($item->shift_starts_at <= now() && $item->shift_ends_at >= now())
                                        <span class="badge bg-success">Активно</span>
                                    @else
                                        <span class="badge bg-info">Запланировано</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Связанные приемы (если есть модель Visit) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-check"></i> Связанные приемы
                </h5>
            </div>
            <div class="card-body">
                @php
                    $visits = \App\Models\Visit::where('schedule_id', $item->id)->with(['client', 'pet', 'status'])->get();
                @endphp
                
                @if($visits->count() > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($visits as $visit)
                            <div class="border rounded p-3 bg-body-tertiary position-relative">
                                <!-- Статус для мобильных (правый верхний угол) -->
                                <div class="position-absolute top-0 end-0 mt-3 me-3 d-md-none">
                                    @if($visit->status)
                                        <span class="badge" style="background-color: {{ $visit->status->color ?? '#6c757d' }}">
                                            {{ $visit->status->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Без статуса</span>
                                    @endif
                                </div>
                                
                                <div class="row align-items-center g-2">
                                    <!-- Время -->
                                    <div class="col-12 col-md-2 mb-2 mb-md-0">
                                        <div class="badge bg-dark fs-6 px-2 py-1">
                                            {{ $visit->starts_at->format('H:i') }}
                                        </div>
                                    </div>
                                    
                                    <!-- Основная информация -->
                                    <div class="col-12 col-md-5 col-xl-4 mb-2 mb-md-0 pe-md-0 pe-5">
                                        <h6 class="mb-1">{{ $visit->client->name ?? 'Не указан' }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ $visit->pet->name ?? 'Питомец не указан' }}
                                        </p>
                                    </div>
                                    
                                    <!-- Статус для десктопа -->
                                    <div class="col-md-3 text-center mb-2 mb-md-0 d-none d-md-block">
                                        @if($visit->status)
                                            <span class="badge" style="background-color: {{ $visit->status->color ?? '#6c757d' }}">
                                                {{ $visit->status->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Без статуса</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Действия -->
                                    <div class="col-12 col-md-2 col-xl-3 text-center">
                                        <a href="{{ route('admin.visits.show', $visit) }}" class="btn btn-outline-primary btn-sm w-100 text-nowrap" title="Подробнее о приеме">
                                            <i class="bi bi-eye"></i> <span class="d-inline d-md-none d-xl-inline">Подробнее</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x display-6"></i>
                        <p class="mt-2">На это время приемы не запланированы</p>
                        <a href="{{ route('admin.visits.create') }}?schedule_id={{ $item->id }}" class="btn btn-outline-primary">
                            <i class="bi bi-plus"></i> Запланировать прием
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Информация о времени -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Статистика времени</h6>
            </div>
            <div class="card-body">
                @php
                    $now = now();
                    $isActive = $item->shift_starts_at <= $now && $item->shift_ends_at >= $now;
                    $isCompleted = $item->shift_ends_at < $now;
                    $isUpcoming = $item->shift_starts_at > $now;
                @endphp
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Текущий статус:</span>
                        <span>
                            @if($isCompleted)
                                <span class="badge bg-secondary">Завершено</span>
                            @elseif($isActive)
                                <span class="badge bg-success">Активно</span>
                            @else
                                <span class="badge bg-info">Запланировано</span>
                            @endif
                        </span>
                    </div>
                    
                    @if($isUpcoming)
                        <div class="d-flex justify-content-between mb-2">
                            <span>До начала:</span>
                            <span>{{ $now->diffForHumans($item->shift_starts_at, true) }}</span>
                        </div>
                    @elseif($isActive)
                        <div class="d-flex justify-content-between mb-2">
                            <span>До окончания:</span>
                            <span>{{ $now->diffForHumans($item->shift_ends_at, true) }}</span>
                        </div>
                    @else
                        <div class="d-flex justify-content-between mb-2">
                            <span>Завершено:</span>
                            <span>{{ $item->shift_ends_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between">
                        <span>Общая продолжительность:</span>
                        <span>{{ $duration }} {{ $duration == 1 ? 'ч' : 'ч' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Действия -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Действия</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.visits.create') }}?schedule_id={{ $item->id }}" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-plus"></i> Запланировать прием
                    </a>
                    <a href="{{ route('admin.schedules.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать расписание
                    </a>
                    <hr>
                    <form action="{{ route('admin.schedules.destroy', $item) }}" method="POST"
                        onsubmit="return confirm('Удалить расписание {{ $item->shift_starts_at->format('d.m.Y H:i') }}?\n\nВнимание: Связанные приемы также могут быть затронуты.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash"></i> Удалить расписание
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 