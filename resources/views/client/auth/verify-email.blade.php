@extends('layouts.client')

@section('title', 'Подтверждение email - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <!-- Заголовок -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-envelope-check text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="h3 fw-bold text-primary mb-2">
                            Подтвердите email
                        </h2>
                        <p class="text-muted">Мы отправили письмо на адрес <strong>{{ $user->email }}</strong></p>
                    </div>

                    <!-- Уведомления -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Информация -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Что дальше?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Проверьте папку "Входящие" в вашем email</li>
                            <li>Если письма нет, проверьте папку "Спам"</li>
                            <li>Нажмите на ссылку в письме для подтверждения</li>
                            <li>После подтверждения вы сможете записываться на прием</li>
                        </ul>
                    </div>

                    <!-- Действия -->
                    <div class="d-grid gap-2 mb-3">
                        <form method="POST" action="{{ route('client.verify-email.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-clockwise me-2"></i>Отправить повторно
                            </button>
                        </form>
                        
                        <a href="{{ route('client.index') }}" class="btn btn-secondary">
                            <i class="bi bi-house me-2"></i>На главную
                        </a>
                    </div>

                    <!-- Дополнительная информация -->
                    <div class="text-center">
                        <p class="text-muted small mb-0">
                            Не получили письмо? Проверьте правильность email адреса в 
                            <a href="{{ route('client.profile') }}" class="text-primary">профиле</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Дополнительная информация -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Ссылка действительна в течение 24 часов
                </p>
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
    transform: translateY(-1px);
}

.btn-outline-primary:hover {
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}

.text-primary {
    color: #0d6efd !important;
}

.text-primary:hover {
    color: #0b5ed7 !important;
}
</style>
@endpush
