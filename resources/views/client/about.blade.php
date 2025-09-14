@extends('layouts.client')

@section('title', 'О нас - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">О нашей клинике</h1>
                <p class="lead">
                    Более 10 лет мы заботимся о здоровье и благополучии ваших питомцев
                </p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="h3 mb-4">Наша миссия</h2>
                        <p class="lead text-muted mb-4">
                            Мы стремимся обеспечить высочайший уровень ветеринарной помощи, 
                            используя современные технологии и индивидуальный подход к каждому пациенту.
                        </p>
                        
                        <h3 class="h4 mb-3">Наша команда</h3>
                        <p class="text-muted mb-4">
                            В нашей клинике работают высококвалифицированные ветеринары с многолетним опытом. 
                            Каждый специалист регулярно повышает свою квалификацию и следит за новейшими 
                            достижениями в области ветеринарии.
                        </p>
                        
                        @if($veterinarians->count() > 0)
                        <div class="row g-3 mb-4">
                            @foreach($veterinarians as $vet)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $vet->name }}</h6>
                                        <small class="text-muted">
                                            @foreach($vet->specialties as $specialty)
                                                {{ $specialty->name }}@if(!$loop->last), @endif
                                            @endforeach
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <h3 class="h4 mb-3">Оборудование</h3>
                        <p class="text-muted mb-4">
                            Мы оснащены современным диагностическим и лечебным оборудованием, 
                            которое позволяет проводить точную диагностику и эффективное лечение 
                            различных заболеваний у животных.
                        </p>
                        
                        <h3 class="h4 mb-3">Наши принципы</h3>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Индивидуальный подход к каждому пациенту
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Использование только проверенных и безопасных методов лечения
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Честность и прозрачность в общении с владельцами
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Постоянное совершенствование качества услуг
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
