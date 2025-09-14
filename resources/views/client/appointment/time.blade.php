@extends('layouts.client')

@section('title', 'Выбор времени - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Выбор времени</h1>
                <p class="lead">
                    Выберите удобное время для приема у {{ $veterinarian->name }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Progress Steps -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <div class="d-flex align-items-center">
                        <div class="step completed">
                            <div class="step-number">1</div>
                            <div class="step-label">Филиал</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step completed">
                            <div class="step-number">2</div>
                            <div class="step-label">Ветеринар</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-number">3</div>
                            <div class="step-label">Время</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-label">Подтверждение</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Time Selection -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                @if($schedulesByDate->count() > 0)
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4">Доступные даты и время</h4>
                            
                            @foreach($schedulesByDate as $date => $schedules)
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="bi bi-calendar me-2"></i>
                                        {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('dddd, D MMMM YYYY') }}
                                    </h5>
                                    
                                    <div class="row g-2">
                                        @foreach($schedules as $schedule)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="schedule-card" data-schedule-id="{{ $schedule->id }}">
                                                    <div class="schedule-time">
                                                        {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->format('H:i') }} - 
                                                        {{ \Carbon\Carbon::parse($schedule->shift_ends_at)->format('H:i') }}
                                                    </div>
                                                    <div class="schedule-status">
                                                        <span class="badge bg-success">Доступно</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Длительность приема составляет 30 минут. 
                                    Выберите время начала приема.
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card border-0 bg-light">
                        <div class="card-body p-5 text-center">
                            <i class="bi bi-calendar-x display-1 text-muted mb-4"></i>
                            <h3 class="h4 mb-3">Нет доступного времени</h3>
                            <p class="text-muted mb-4">
                                У выбранного ветеринара нет свободного времени для записи.
                            </p>
                            <a href="{{ route('client.appointment.veterinarians', ['branch_id' => $branch->id]) }}" 
                               class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i>Выбрать другого ветеринара
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleCards = document.querySelectorAll('.schedule-card');
    
    scheduleCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selection from other cards
            scheduleCards.forEach(c => c.classList.remove('selected'));
            
            // Select current card
            this.classList.add('selected');
            
            // Get schedule ID
            const scheduleId = this.dataset.scheduleId;
            
            // Get time from the card
            const timeText = this.querySelector('.schedule-time').textContent.trim();
            const cleanTime = timeText.split(' - ')[0].trim();
            
            // Build confirmation URL
            const confirmUrl = '{{ route("client.appointment.confirm") }}';
            const url = new URL(confirmUrl, window.location.origin);
            url.searchParams.set('branch_id', '{{ $branch->id }}');
            url.searchParams.set('veterinarian_id', '{{ $veterinarian->id }}');
            url.searchParams.set('schedule_id', scheduleId);
            url.searchParams.set('time', cleanTime);
            
            // Redirect to confirmation page
            window.location.href = url.toString();
        });
    });
});
</script>
@endpush
