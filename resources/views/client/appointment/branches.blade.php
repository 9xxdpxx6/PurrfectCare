@extends('layouts.client')

@section('title', 'Выбор филиала - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Запись на прием</h1>
                <p class="lead">
                    Выберите филиал для записи на прием
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
                        <div class="step active">
                            <div class="step-number">1</div>
                            <div class="step-label d-none d-sm-block">Филиал</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-label d-none d-sm-block">Ветеринар</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
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

<!-- Branches Selection -->
<section class="py-5">
    <div class="container">
        @if($branches->count() > 0)
            <div class="row g-4">
                @foreach($branches as $branch)
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="card h-100 border-0 shadow-sm branch-card">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <div class="branch-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <h4 class="card-title mt-3">{{ $branch->name }}</h4>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-geo-alt text-primary me-2 mt-1"></i>
                                    <div>
                                        <strong>Адрес:</strong><br>
                                        <span class="text-muted">{{ $branch->address }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-telephone text-primary me-2 mt-1"></i>
                                    <div>
                                        <strong>Телефон:</strong><br>
                                        <span class="text-muted">{{ $branch->phone }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-clock text-primary me-2 mt-1"></i>
                                    <div>
                                        <strong>Режим работы:</strong><br>
                                        <span class="text-muted">
                                            {{ $branch->opens_at ? $branch->opens_at->format('H:i') : '9:00' }} - 
                                            {{ $branch->closes_at ? $branch->closes_at->format('H:i') : '21:00' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <a href="{{ route('client.appointment.veterinarians', ['branch_id' => $branch->id]) }}" 
                                   class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-right me-2"></i>Выбрать филиал
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-5">
                            <i class="bi bi-building display-1 text-muted mb-4"></i>
                            <h3 class="h4 mb-3">Филиалы не найдены</h3>
                            <p class="text-muted mb-4">
                                В данный момент нет доступных филиалов для записи.
                            </p>
                            <a href="{{ route('client.contacts') }}" class="btn btn-primary">
                                <i class="bi bi-telephone me-1"></i>Связаться с нами
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
.branch-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.branch-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 2rem;
}

.card-title {
    color: #2c3e50;
    font-weight: 600;
}
</style>
@endpush
