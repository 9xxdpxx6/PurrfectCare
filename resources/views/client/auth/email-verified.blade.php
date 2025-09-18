@extends('layouts.client')

@section('title', 'Email подтвержден - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <!-- Иконка успеха -->
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Заголовок -->
                    <h2 class="h3 fw-bold text-success mb-3">
                        Email успешно подтвержден!
                    </h2>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        Теперь вы можете пользоваться всеми функциями системы PurrfectCare, 
                        включая запись на прием к ветеринару.
                    </p>

                    <!-- Действия -->
                    <div class="d-grid gap-2 mb-4">
                        <a href="{{ route('client.appointment.branches') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                        </a>
                        
                        <a href="{{ route('client.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-house me-2"></i>На главную
                        </a>
                    </div>

                    <!-- Дополнительная информация -->
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Добро пожаловать!</strong> Теперь вы можете:
                        <ul class="mb-0 mt-2 text-start">
                            <li>Записываться на прием к ветеринару</li>
                            <li>Добавлять информацию о своих питомцах</li>
                            <li>Просматривать историю визитов</li>
                            <li>Получать уведомления о записях</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 1rem;
}

.alert {
    border-radius: 0.75rem;
}

.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

.btn-primary:hover {
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
}

.btn-outline-primary:hover {
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}

.text-success {
    color: #198754 !important;
}

.text-primary {
    color: #0d6efd !important;
}

.text-primary:hover {
    color: #0b5ed7 !important;
}
</style>
@endpush
