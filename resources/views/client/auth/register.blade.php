@extends('layouts.client')

@section('title', 'Регистрация - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <!-- Заголовок -->
                    <div class="text-center mb-4">
                        <h2 class="h3 fw-bold text-primary mb-2">
                            <i class="bi bi-heart-pulse me-2"></i>Регистрация
                        </h2>
                        <p class="text-muted">Создайте аккаунт для записи на прием</p>
                    </div>

                    <!-- Уведомления -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        @php
                            $fieldErrors = ['name', 'email', 'phone', 'password', 'password_confirmation'];
                            $generalErrors = [];
                            foreach ($errors->keys() as $key) {
                                if (!in_array($key, $fieldErrors)) {
                                    $generalErrors = array_merge($generalErrors, $errors->get($key));
                                }
                            }
                        @endphp
                        @if (!empty($generalErrors))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <ul class="mb-0">
                                    @foreach ($generalErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    @endif

                    <!-- Форма регистрации -->
                    <form method="POST" action="{{ route('client.register') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя и фамилия *</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="+7 (999) 123-45-67">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Минимум 8 символов</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Зарегистрироваться
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-muted mb-0">
                                Уже есть аккаунт? 
                                <a href="{{ route('client.login') }}" class="text-primary fw-bold text-decoration-none">
                                    Войти
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Дополнительная информация -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Регистрируясь, вы соглашаетесь с 
                    <a href="#" class="text-primary">условиями использования</a> 
                    и 
                    <a href="#" class="text-primary">политикой конфиденциальности</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Валидация пароля в реальном времени
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const confirmation = document.getElementById('password_confirmation');
    
    if (password.length < 8) {
        this.setCustomValidity('Пароль должен содержать минимум 8 символов');
    } else {
        this.setCustomValidity('');
    }
    
    // Проверяем совпадение паролей
    if (confirmation.value && password !== confirmation.value) {
        confirmation.setCustomValidity('Пароли не совпадают');
    } else {
        confirmation.setCustomValidity('');
    }
});

document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password !== confirmation) {
        this.setCustomValidity('Пароли не совпадают');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endpush

@push('styles')
<style>
.card {
    border-radius: 1rem;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
}

.input-group .btn {
    border-left: none;
}

.input-group .form-control:focus + .btn {
    border-color: #0d6efd;
}

.text-primary {
    color: #0d6efd !important;
}

.text-primary:hover {
    color: #0b5ed7 !important;
}
</style>
@endpush
