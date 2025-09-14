@extends('layouts.client')

@section('title', 'Вход - PurrfectCare')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="card-title">Вход в систему</h3>
                        <p class="text-muted">Войдите, чтобы записаться на прием</p>
                    </div>

                    @if (session('message'))
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>{{ session('message') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        @php
                            $fieldErrors = ['email', 'password'];
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

                    <form method="POST" action="{{ route('client.login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Нет аккаунта? 
                            <a href="{{ route('client.register') }}" class="text-primary fw-bold text-decoration-none">Зарегистрироваться</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
