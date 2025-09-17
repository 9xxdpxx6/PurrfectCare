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
                    Мы стремимся разобраться в вопросе заботы о животных и, кажется, знаем ответ!
                </p>
            </div>
        </div>
    </div>
</section>


<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Main Content -->
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-5">
                        <h2 class="h3 mb-4">Наша миссия</h2>
                        <p class="lead text-muted mb-4">
                            Мы считаем, что фундаментом благополучия наших четвероногих друзей служит качественная среда, 
                            объединяющая владельцев домашних животных, ветеринарных врачей и профессионалов зообизнеса – 
                            всех тех, кто пронизан единым культурным кодом любви к животным.
                        </p>
                        
                        <p class="text-muted mb-4">
                            Пространство заботы PurrfectCare гармонично сочетает качественные ветеринарные услуги, 
                            современное диагностическое оборудование, профессиональные консультации специалистов 
                            и компетентный сервис. Наша территория заботы о животных насчитывает 
                            {{ $stats['branches_count'] }} филиалов.
                        </p>
                        
                        <h3 class="h4 mb-3">Наша команда</h3>
                        <p class="text-muted mb-4">
                            В нашей клинике работают {{ $stats['employees_count'] }} высококвалифицированных специалистов 
                            с многолетним опытом. Каждый сотрудник регулярно повышает свою квалификацию и следит за 
                            новейшими достижениями в области ветеринарии. Наши врачи специализируются в различных 
                            областях: хирургии, терапии, диагностике, офтальмологии, кардиологии и других направлениях 
                            ветеринарной медицины.
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
                
                
                <!-- Equipment Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="h3 mb-4 text-center">Оборудование</h2>
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <p class="text-muted mb-4">
                                    Мы оснащены современным диагностическим и лечебным оборудованием, 
                                    которое позволяет проводить точную диагностику и эффективное лечение 
                                    различных заболеваний у животных. Наша клиника использует только 
                                    проверенные и сертифицированные медицинские приборы от ведущих 
                                    мировых производителей.
                                </p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            <span>Рентгенологическое оборудование</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            <span>Ультразвуковая диагностика</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            <span>Лабораторные анализаторы</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            <span>Хирургические инструменты</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <img src="{{ asset('images/client/about/equipment.png') }}" alt="Ветеринарное оборудование" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
