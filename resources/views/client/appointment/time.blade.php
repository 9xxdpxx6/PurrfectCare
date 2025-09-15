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
                    <div class="d-flex align-items-center flex-wrap justify-content-center">
                        <div class="step completed">
                            <div class="step-number">1</div>
                            <div class="step-label d-none d-sm-block">Филиал</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step completed">
                            <div class="step-number">2</div>
                            <div class="step-label d-none d-sm-block">Ветеринар</div>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-number">3</div>
                            <div class="step-label d-none d-sm-block">Время</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-label d-none d-sm-block">Подтверждение</div>
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
                @if(count($availableSlotsByDate) > 0)
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-4">Доступные даты и время</h4>
                            
                            @foreach($availableSlotsByDate as $date => $slots)
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">
                                        <i class="bi bi-calendar me-2"></i>
                                        {{ \Carbon\Carbon::parse($date)->locale('ru')->isoFormat('dddd, D MMMM YYYY') }}
                                    </h5>
                                    
                                    <div class="row g-2">
                                        @foreach($slots as $slot)
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <div class="schedule-card" data-schedule-id="{{ $slot['schedule_id'] }}" data-time="{{ $slot['time'] }}">
                                                    <div class="schedule-time">
                                                        {{ $slot['time'] }} - {{ \Carbon\Carbon::parse($slot['time'])->addMinutes(30)->format('H:i') }}
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
                            <h3 class="h4 mb-3">Ветеринар недоступен</h3>
                            <p class="text-muted mb-4">
                                Выбранный ветеринар не работает в данный период или у него уже занят весь день.
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
            
            // Get time from the card data attribute
            const cleanTime = this.dataset.time;
            
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
