@extends('layouts.client')

@section('title', 'Доступ запрещен - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-12">
            <div class="card border-0 shadow-lg error-card">
                <div class="card-body text-center p-5">
                    <!-- Иконка 403 -->
                    <div class="error-icon mb-4">
                        <i class="bi bi-shield-exclamation display-1 text-warning"></i>
                    </div>

                    <!-- Заголовок -->
                    <h1 class="h2 mb-3">Доступ запрещен</h1>
                    
                    <!-- Код ошибки -->
                    <div class="error-code mb-3">
                        <span class="badge bg-warning fs-6 px-3 py-2">
                            Ошибка 403
                        </span>
                    </div>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        У вас нет прав для доступа к этому ресурсу. 
                        Возможно, требуется авторизация или у вас недостаточно прав.
                    </p>

                    <!-- Информация о доступе -->
                    <div class="error-details mb-4">
                        <div class="alert alert-warning text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-shield-exclamation me-2"></i>Что это означает?
                            </h6>
                            <p class="mb-0 small">
                                Эта страница или функция доступна только авторизованным пользователям 
                                или требует специальных прав доступа.
                            </p>
                        </div>
                    </div>

                    <!-- Рекомендации -->
                    <div class="recommendations mb-4">
                        <div class="alert alert-info text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb me-2"></i>Что можно сделать:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Войдите в систему, если вы не авторизованы</li>
                                <li>Обратитесь к администратору для получения доступа</li>
                                <li>Проверьте, правильно ли вы перешли по ссылке</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        @guest
                            <a href="{{ route('client.login') }}" class="btn btn-primary error-btn">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </a>
                            <a href="{{ route('client.register') }}" class="btn btn-outline-primary error-btn">
                                <i class="bi bi-person-plus me-2"></i>Регистрация
                            </a>
                        @else
                            <a href="{{ route('client.index') }}" class="btn btn-primary error-btn">
                                <i class="bi bi-house-door me-2"></i>На главную
                            </a>
                        @endguest
                        
                        <button onclick="history.back()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
