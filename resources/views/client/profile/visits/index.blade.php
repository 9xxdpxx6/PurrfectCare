@extends('layouts.client')

@section('title', 'История визитов - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="visits" />

        <!-- Основной контент -->
        <div class="col-lg-9">
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 mb-0">История визитов</h2>
            </div>

            <!-- Фильтры -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('client.profile.visits') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Поиск</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Ветеринар или питомец..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Статус</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Все статусы</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->name }}" 
                                                {{ request('status') == $status->name ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">Дата с</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">Дата по</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-search me-1"></i>Найти
                                </button>
                                <a href="{{ route('client.profile.visits') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Сбросить
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список визитов -->
            @if($visits->count() > 0)
                @foreach($visits as $visit)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="card-title mb-0 me-3">
                                        {{ \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') }}
                                    </h5>
                                    <span class="badge 
                                        @if($visit->status->name === 'Запланирован') bg-primary
                                        @elseif($visit->status->name === 'Завершен') bg-success
                                        @elseif($visit->status->name === 'Отменен') bg-danger
                                        @else bg-secondary
                                        @endif">
                                        {{ $visit->status->name }}
                                    </span>
                                </div>
                                
                                <div class="row text-muted small">
                                    <div class="col-md-6">
                                        <i class="bi bi-person me-1"></i>
                                        <strong>Ветеринар:</strong> {{ $visit->schedule->veterinarian->name }}
                                    </div>
                                    <div class="col-md-6">
                                        <i class="bi bi-building me-1"></i>
                                        <strong>Филиал:</strong> {{ $visit->schedule->branch->name }}
                                    </div>
                                </div>
                                
                                @if($visit->pet)
                                <div class="row text-muted small mt-1">
                                    <div class="col-md-6">
                                        <i class="bi bi-heart me-1"></i>
                                        <strong>Питомец:</strong> {{ $visit->pet->name }}
                                    </div>
                                </div>
                                @endif
                                
                                @if($visit->complaints)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Жалобы:</strong> {{ Str::limit($visit->complaints, 100) }}
                                    </small>
                                </div>
                                @endif
                            </div>
                            
                            <div class="col-md-4 text-md-end">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('client.profile.visits.show', $visit) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>Подробнее
                                    </a>
                                    
                                    @if($visit->status->name === 'Запланирован')
                                        @if($visit->starts_at->diffInHours(now()) >= 2)
                                            <form method="POST" action="{{ route('client.profile.visits.cancel', $visit) }}" 
                                                  class="d-inline" onsubmit="return confirm('Вы уверены, что хотите отменить визит?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                    <i class="bi bi-x-circle me-1"></i>Отменить
                                                </button>
                                            </form>
                                        @else
                                            <small class="text-muted">
                                                Отмена возможна не менее чем за 2 часа
                                            </small>
                                        @endif
                                    @elseif($visit->status->name === 'Отменен' || $visit->status->name === 'Завершен')
                                        <form method="POST" action="{{ route('client.profile.visits.reschedule', $visit) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Повторить запись
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Пагинация -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $visits->appends(request()->query())->links() }}
                </div>
            @else
                <div class="card border-0 bg-light">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-calendar-x display-1 text-muted mb-4"></i>
                        <h3 class="h4 mb-3">Нет визитов</h3>
                        <p class="text-muted mb-4">
                            @if(request()->hasAny(['status', 'date_from', 'date_to', 'search']))
                                По выбранным фильтрам визиты не найдены.
                            @else
                                У вас пока нет записей на прием.
                            @endif
                        </p>
                        <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                            <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.card {
    // Убираем hover эффекты для карточек
}

.badge {
    font-size: 0.75rem;
}
</style>
@endpush
