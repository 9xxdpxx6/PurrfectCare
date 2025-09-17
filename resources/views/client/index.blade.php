@extends('layouts.client')

@section('title', 'Главная - PurrfectCare')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white hero-section position-relative" id="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-4 mb-lg-0 hero-blur-block">
                <h1 class="display-4 fw-bold mb-4" style="min-height: 120px; line-height: 1.2;">
                    <span class="hero-title-part-1" style="opacity: 0;">Забота о ваших питомцах —</span>
                    <span class="hero-title-part-2" style="opacity: 0;"><span class="text-warning typewriter-text">наша профессия</span></span>
                </h1>
                <p class="lead mb-4 hero-description" style="opacity: 0;">
                    Современная ветеринарная клиника с полным спектром услуг для здоровья и благополучия ваших четвероногих друзей.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 hero-buttons" style="opacity: 0;">
                    <a href="{{ route('client.services') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-list-ul me-2"></i>Наши услуги
                    </a>
                    <a href="{{ route('client.appointment') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hero Image with Fade Effect -->
    <div class="hero-image-container position-absolute top-0 end-0 h-100 w-50">
        <img src="{{ asset('images/client/hero/vet_holds_cat.png') }}" alt="Ветеринар с котом" class="hero-image h-100 w-100" style="opacity: 0; object-fit: cover; object-position: center;">
        <div class="hero-image-overlay position-absolute top-0 start-0 h-100 w-100"></div>
        <div class="hero-image-fade position-absolute top-0 start-0 h-100 w-100"></div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">PurrfectCare в цифрах</h2>
                <p class="lead text-muted">
                    Наши достижения и опыт работы с вашими питомцами
                </p>
            </div>
        </div>
        
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="card h-100 border-0 shadow-sm stat-card">
                    <div class="card-body text-center p-4">
                        <h3 class="text-primary mb-2 stat-number" data-target="{{ $stats['satisfied_clients'] >= 150 ? $stats['satisfied_clients'] : 1200 }}">{{ $stats['satisfied_clients'] >= 150 ? $stats['satisfied_clients'] : '1200' }}</h3>
                        <p class="text-muted mb-0 fw-semibold">Довольных клиентов</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="card h-100 border-0 shadow-sm stat-card">
                    <div class="card-body text-center p-4">
                        <h3 class="text-primary mb-2 stat-number" data-target="{{ $stats['years_experience'] >= 3 ? $stats['years_experience'] : 7 }}">{{ $stats['years_experience'] >= 3 ? $stats['years_experience'] : '7' }}</h3>
                        <p class="text-muted mb-0 fw-semibold">Лет опыта</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="card h-100 border-0 shadow-sm stat-card">
                    <div class="card-body text-center p-4">
                        <h3 class="text-primary mb-2 stat-number" data-target="{{ $stats['treated_pets'] >= 50 ? round($stats['treated_pets'] / 100) * 100 : 1800 }}">{{ $stats['treated_pets'] >= 50 ? round($stats['treated_pets'] / 100) * 100 : '1800' }}</h3>
                        <p class="text-muted mb-0 fw-semibold">Вылеченных питомцев</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6 mb-4">
                <div class="card h-100 border-0 shadow-sm stat-card">
                    <div class="card-body text-center p-4">
                        <h3 class="text-primary mb-2 stat-number" data-target="{{ $stats['services_count'] >= 10 ? $stats['services_count'] : 25 }}">{{ $stats['services_count'] >= 10 ? $stats['services_count'] : '25' }}</h3>
                        <p class="text-muted mb-0 fw-semibold">Медицинских услуг</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Почему выбирают нас?</h2>
                <p class="lead text-muted">
                    Мы предоставляем комплексный подход к здоровью ваших питомцев
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-shield-check text-primary display-4 mb-3"></i>
                        <h5 class="card-title">Профессиональные врачи</h5>
                        <p class="card-text text-muted">
                            Опытные ветеринары с многолетним стажем работы и постоянным повышением квалификации.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-gear text-primary display-4 mb-3"></i>
                        <h5 class="card-title">Современное оборудование</h5>
                        <p class="card-text text-muted">
                            Новейшее диагностическое и лечебное оборудование для точной диагностики и эффективного лечения.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm feature-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-clock text-primary display-4 mb-3"></i>
                        <h5 class="card-title">Гибкий график работы</h5>
                        <p class="card-text text-muted">
                            Удобное время приема с учетом ваших потребностей. Работаем в удобном для вас режиме.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Наши услуги</h2>
                <p class="lead text-muted">
                    Полный спектр ветеринарных услуг для поддержания здоровья ваших питомцев
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-clipboard-pulse text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Диагностика</h6>
                        <p class="card-text text-muted small">Комплексное обследование и постановка диагноза</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-droplet text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Вакцинация</h6>
                        <p class="card-text text-muted small">Профилактические прививки по графику</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-scissors text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Хирургия</h6>
                        <p class="card-text text-muted small">Плановые и экстренные операции</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-heart text-primary fs-1 mb-3"></i>
                        <h6 class="card-title">Стоматология</h6>
                        <p class="card-text text-muted small">Лечение и профилактика заболеваний зубов</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('client.services') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-right me-2"></i>Все услуги
            </a>
        </div>
    </div>
</section>

<!-- Technology Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Современные технологии</h2>
                <p class="lead text-muted">
                    Мы используем новейшее оборудование для точной диагностики и эффективного лечения
                </p>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- УЗИ диагностика - на всю ширину на md, 1/3 на lg+ -->
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-0">
                        <div class="tech-image mb-3">
                            <img src="{{ asset('images/client/technology/ultrasound.png') }}" alt="УЗИ оборудование" class="img-fluid" style="height: 200px; object-fit: cover; width: 100%; border-radius: 0.375rem 0.375rem 0 0;">
                        </div>
                        <div class="px-4 pb-4">
                            <h5 class="card-title">УЗИ диагностика</h5>
                            <p class="card-text text-muted">
                                Безопасное исследование внутренних органов
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Цифровой рентген - половинка на md, 1/3 на lg+ -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-0">
                        <div class="tech-image mb-3">
                            <img src="{{ asset('images/client/technology/x_ray.png') }}" alt="Рентген оборудование" class="img-fluid" style="height: 200px; object-fit: cover; width: 100%; border-radius: 0.375rem 0.375rem 0 0;">
                        </div>
                        <div class="px-4 pb-4">
                            <h5 class="card-title">Цифровой рентген</h5>
                            <p class="card-text text-muted">
                                Высокоточная диагностика с минимальным облучением
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Лаборатория - половинка на md, 1/3 на lg+ -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-0">
                        <div class="tech-image mb-3">
                            <img src="{{ asset('images/client/technology/laborant.png') }}" alt="Лаборатория" class="img-fluid" style="height: 200px; object-fit: cover; width: 100%; border-radius: 0.375rem 0.375rem 0 0;">
                        </div>
                        <div class="px-4 pb-4">
                            <h5 class="card-title">Собственная лаборатория</h5>
                            <p class="card-text text-muted">
                                Быстрые и точные анализы в день обращения
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold mb-3">Наша клиника</h2>
                <p class="lead text-muted">
                    Современные кабинеты и комфортные условия для ваших питомцев
                </p>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <img src="{{ asset('images/client/features/reception.png') }}" alt="Ресепшн клиники" class="img-fluid rounded shadow-sm" style="height: 250px; object-fit: cover; width: 100%;">
            </div>
            <div class="col-md-4">
                <img src="{{ asset('images/client/features/operating_room.png') }}" alt="Операционная" class="img-fluid rounded shadow-sm" style="height: 250px; object-fit: cover; width: 100%;">
            </div>
            <div class="col-md-4">
                <img src="{{ asset('images/client/features/cabinet.png') }}" alt="Кабинет приема" class="img-fluid rounded shadow-sm" style="height: 250px; object-fit: cover; width: 100%;">
            </div>
            <div class="col-md-6">
                <img src="{{ asset('images/client/features/laboratory.png') }}" alt="Лаборатория" class="img-fluid rounded shadow-sm" style="height: 200px; object-fit: cover; width: 100%;">
            </div>
            <div class="col-md-6">
                <img src="{{ asset('images/client/features/waiting-area.png') }}" alt="Зона ожидания" class="img-fluid rounded shadow-sm" style="height: 200px; object-fit: cover; width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-3">Готовы записаться на прием?</h2>
                <p class="lead text-muted mb-4">
                    Выберите удобное время и запишитесь на прием к нашим специалистам
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="{{ route('client.appointment') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-plus me-2"></i>Записаться онлайн
                    </a>
                    <a href="{{ route('client.contacts') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-telephone me-2"></i>Позвонить нам
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero секция - поэтапная анимация
    function initHeroAnimation() {
        const heroSection = document.getElementById('hero-section');
        const titlePart1 = document.querySelector('.hero-title-part-1');
        const titlePart2 = document.querySelector('.hero-title-part-2');
        const typewriterText = document.querySelector('.typewriter-text');
        const description = document.querySelector('.hero-description');
        const buttons = document.querySelector('.hero-buttons');
        const heroImage = document.querySelector('.hero-image');
        
        // 1. Сначала появляется фон (уже есть)
        
        // 2. Через 100мс появляется первая часть заголовка
        setTimeout(() => {
            titlePart1.style.transition = 'opacity 0.8s ease-in-out';
            titlePart1.style.opacity = '1';
        }, 100);
        
        // 3. Через 400мс начинается анимация печатания
        setTimeout(() => {
            titlePart2.style.transition = 'opacity 0.5s ease-in-out';
            titlePart2.style.opacity = '1';
            
            // Анимация печатания для "наша профессия" без курсора
            const text = typewriterText.textContent;
            typewriterText.textContent = '';
            
            let i = 0;
            const typeInterval = setInterval(() => {
                typewriterText.textContent += text.charAt(i);
                i++;
                if (i >= text.length) {
                    clearInterval(typeInterval);
                }
            }, 100);
        }, 400);
        
        // 4. Через 600мс появляется картинка
        setTimeout(() => {
            heroImage.style.transition = 'opacity 0.8s ease-in-out';
            heroImage.style.opacity = '1';
        }, 600);
        
        // 5. Через 700мс появляется blur блок (через 100мс после картинки)
        setTimeout(() => {
            const blurBlock = document.querySelector('.hero-blur-block');
            if (blurBlock) {
                blurBlock.style.opacity = '1';
            }
        }, 700);
        
        // 6. Через 900мс появляется описание
        setTimeout(() => {
            description.style.transition = 'opacity 0.8s ease-in-out';
            description.style.opacity = '1';
        }, 900);
        
        // 7. Через 1200мс появляются кнопки
        setTimeout(() => {
            buttons.style.transition = 'opacity 0.8s ease-in-out';
            buttons.style.opacity = '1';
        }, 1200);
    }
    
    // Запускаем анимацию hero секции
    initHeroAnimation();
    
    // Анимация счетчиков
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 800; // 1 секунда (в 2 раза быстрее)
        const start = 0;
        const increment = target / (duration / 16); // 60 FPS
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (element.textContent.includes('%')) {
                element.textContent = Math.floor(current) + '%';
            } else if (element.textContent.includes('+')) {
                element.textContent = Math.floor(current) + '+';
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    };
    
    // Intersection Observer для запуска анимации при появлении в viewport
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    });
    
    statNumbers.forEach(stat => {
        observer.observe(stat);
    });
});
</script>
@endpush
