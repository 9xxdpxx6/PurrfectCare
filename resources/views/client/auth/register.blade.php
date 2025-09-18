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
                        <h2 class="h3 fw-bold text-primary mb-2 d-flex align-items-center justify-content-center">
                            <img src="{{ asset('logo.png') }}" alt="PurrfectCare" class="me-2" style="height: 28px;">
                            Регистрация
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
                            <label for="name" class="form-label">ФИО</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон *</label>
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
                            <label for="password" class="form-label">Пароль</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password">
                                <button class="btn btn-outline-primary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation">
                                <button class="btn btn-outline-primary" type="button" onclick="togglePassword('password_confirmation')">
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
    
    // Убираем только JavaScript-созданные ошибки, не трогаем серверные
    this.classList.remove('is-invalid');
    let jsErrorDiv = this.parentNode.querySelector('.invalid-feedback.js-error');
    if (jsErrorDiv) {
        jsErrorDiv.remove();
    }
    
    if (password.length < 8) {
        this.classList.add('is-invalid');
        // Добавляем сообщение об ошибке с классом js-error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback js-error';
        errorDiv.textContent = 'Пароль должен содержать минимум 8 символов';
        this.parentNode.appendChild(errorDiv);
    }
    
    // Проверяем совпадение паролей
    if (confirmation.value && password !== confirmation.value) {
        // Подсвечиваем оба поля при несовпадении
        this.classList.add('is-invalid');
        confirmation.classList.add('is-invalid');
        
        // Добавляем сообщение об ошибке для поля password
        let passwordErrorDiv = this.parentNode.querySelector('.invalid-feedback.js-error');
        if (!passwordErrorDiv) {
            passwordErrorDiv = document.createElement('div');
            passwordErrorDiv.className = 'invalid-feedback js-error';
            this.parentNode.appendChild(passwordErrorDiv);
        }
        passwordErrorDiv.textContent = 'Пароли не совпадают';
        
        // Добавляем сообщение об ошибке для поля confirmation
        let confErrorDiv = confirmation.parentNode.querySelector('.invalid-feedback.js-error');
        if (!confErrorDiv) {
            confErrorDiv = document.createElement('div');
            confErrorDiv.className = 'invalid-feedback js-error';
            confirmation.parentNode.appendChild(confErrorDiv);
        }
        confErrorDiv.textContent = 'Пароли не совпадают';
    } else {
        // Убираем подсветку с обоих полей при совпадении
        this.classList.remove('is-invalid');
        confirmation.classList.remove('is-invalid');
        
        // Удаляем сообщения об ошибках
        let passwordErrorDiv = this.parentNode.querySelector('.invalid-feedback.js-error');
        if (passwordErrorDiv) {
            passwordErrorDiv.remove();
        }
        
        let confErrorDiv = confirmation.parentNode.querySelector('.invalid-feedback.js-error');
        if (confErrorDiv) {
            confErrorDiv.remove();
        }
    }
});

document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    // Убираем только JavaScript-созданные ошибки, не трогаем серверные
    this.classList.remove('is-invalid');
    let jsErrorDiv = this.parentNode.querySelector('.invalid-feedback.js-error');
    if (jsErrorDiv) {
        jsErrorDiv.remove();
    }
    
    if (password !== confirmation) {
        // Подсвечиваем оба поля при несовпадении
        this.classList.add('is-invalid');
        document.getElementById('password').classList.add('is-invalid');
        
        // Добавляем сообщение об ошибке для текущего поля
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback js-error';
        errorDiv.textContent = 'Пароли не совпадают';
        this.parentNode.appendChild(errorDiv);
        
        // Добавляем сообщение об ошибке для поля password
        let passwordErrorDiv = document.getElementById('password').parentNode.querySelector('.invalid-feedback.js-error');
        if (!passwordErrorDiv) {
            passwordErrorDiv = document.createElement('div');
            passwordErrorDiv.className = 'invalid-feedback js-error';
            document.getElementById('password').parentNode.appendChild(passwordErrorDiv);
        }
        passwordErrorDiv.textContent = 'Пароли не совпадают';
    } else {
        // Убираем подсветку с обоих полей при совпадении
        this.classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
        
        // Удаляем сообщения об ошибках
        let errorDiv = this.parentNode.querySelector('.invalid-feedback.js-error');
        if (errorDiv) {
            errorDiv.remove();
        }
        
        let passwordErrorDiv = document.getElementById('password').parentNode.querySelector('.invalid-feedback.js-error');
        if (passwordErrorDiv) {
            passwordErrorDiv.remove();
        }
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
    border-color: #dee2e6;
    color: #6c757d;
}

.input-group .form-control:focus + .btn {
    border-color: #0d6efd;
    color: #0d6efd;
}

.input-group .btn:hover {
    border-color: #0d6efd;
    color: #0d6efd;
    background-color: transparent;
}

.input-group .btn:focus {
    border-color: #0d6efd;
    color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.text-primary {
    color: #0d6efd !important;
}

.text-primary:hover {
    color: #0b5ed7 !important;
}
</style>
@endpush
