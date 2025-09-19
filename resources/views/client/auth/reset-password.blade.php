@extends('layouts.client')

@section('title', 'Сброс пароля - PurrfectCare')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="card-title">Сброс пароля</h3>
                        <p class="text-muted">Введите новый пароль для вашего аккаунта</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        @php
                            $fieldErrors = ['email', 'password', 'password_confirmation', 'token'];
                            $generalErrors = [];
                            foreach ($errors->keys() as $key) {
                                if (!in_array($key, $fieldErrors)) {
                                    $generalErrors = array_merge($generalErrors, $errors->get($key));
                                }
                            }
                        @endphp
                        @if (!empty($generalErrors))
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($generalErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('client.password.update') }}">
                        @csrf
                        
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   value="{{ $email }}" 
                                   disabled>
                            <div class="form-text">Email адрес для сброса пароля</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Новый пароль</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   autofocus
                                   placeholder="Введите новый пароль">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   placeholder="Подтвердите новый пароль">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-key me-2"></i>Сбросить пароль
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Вспомнили пароль? 
                            <a href="{{ route('client.login') }}" class="text-primary fw-bold text-decoration-none">Войти</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
