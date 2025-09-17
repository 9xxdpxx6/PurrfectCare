@extends('layouts.client')

@section('title', 'Мои записи - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Боковая навигация -->
        <x-client.profile-sidebar active="appointments" />

        <!-- Основной контент -->
        <div class="col-lg-9 col-12">
            <!-- Заголовок -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <h2 class="h3 mb-0">Мои записи на прием</h2>
                <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Новая запись
                </a>
            </div>

            @if($visits->count() > 0)
                @foreach($visits as $visit)
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="card-title mb-0 me-3">{{ $visit->pet->name ?? 'Без питомца' }}</h5>
                                            <span class="badge" style="background-color: {{ $visit->status->color }}">
                                                {{ $visit->status->name }}
                                            </span>
                                    </div>
                                    
                                    <div class="row text-muted small">
                                        <div class="col-sm-6">
                                            <i class="bi bi-person me-1"></i>
                                            {{ $visit->schedule->veterinarian->name }}
                                        </div>
                                        <div class="col-sm-6">
                                            <i class="bi bi-building me-1"></i>
                                            {{ $visit->schedule->branch->name }}
                                        </div>
                                    </div>
                                    
                                    <div class="row text-muted small mt-1">
                                        <div class="col-sm-6">
                                            <i class="bi bi-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y') }}
                                        </div>
                                        <div class="col-sm-6">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($visit->starts_at)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($visit->starts_at)->addMinutes(30)->format('H:i') }}
                                        </div>
                                    </div>
                                    
                                    @if($visit->complaints)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <strong>Жалобы:</strong> {{ Str::limit($visit->complaints, 100) }}
                                        </small>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <div class="d-grid gap-2 d-md-block">
                                        @if($visit->status->name === 'Запланирован')
                                            @if($visit->starts_at->diffInHours(now()) >= 2)
                                                <form method="POST" action="{{ route('client.appointment.cancel', $visit) }}" 
                                                      class="d-inline" onsubmit="return confirm('Вы уверены, что хотите отменить запись?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 w-md-auto">
                                                        <i class="bi bi-x-circle me-1"></i>Отменить
                                                    </button>
                                                </form>
                                            @else
                                                <small class="text-muted d-block text-center text-md-start">
                                                    Отмена возможна не менее чем за 2 часа
                                                </small>
                                            @endif
                                        @endif
                                        
                                        <a href="{{ route('client.profile.visits.show', $visit) }}" class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                                            <i class="bi bi-eye me-1"></i>Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="row mt-4">
                <div class="col-12">
                    {{ $visits->links() }}
                </div>
            </div>
            @else
                <div class="card border-0 bg-light">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-calendar-x display-1 text-muted mb-4"></i>
                        <h3 class="h4 mb-3">У вас нет записей</h3>
                        <p class="text-muted mb-4">
                            У вас пока нет записей на прием. Запишитесь на прием, чтобы они появились здесь.
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
.card-title {
    color: #2c3e50;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

.card {
    // Убираем hover эффекты для карточек
}
</style>
@endpush
