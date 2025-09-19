@extends('layouts.client')

@section('title', 'Забыли пароль - PurrfectCare')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h3 class="card-title">Забыли пароль?</h3>
                        <p class="text-muted">Введите ваш email для получения ссылки сброса пароля</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        @php
                            $fieldErrors = ['email'];
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

                    <form method="POST" action="{{ route('client.password.email') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="Введите ваш email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope me-2"></i>Отправить ссылку
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
