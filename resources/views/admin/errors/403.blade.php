@extends('layouts.admin')

@section('title', 'Доступ запрещен')

@section('content')
<div class="container-fluid">
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
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            Ошибка 403
                        </span>
                    </div>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        У вас нет прав для доступа к этому ресурсу. 
                        Возможно, требуется дополнительная авторизация или у вас недостаточно привилегий.
                    </p>

                    <!-- Информация о правах -->
                    <div class="permissions-info mb-4">
                        <div class="alert alert-warning text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>Что делать?
                            </h6>
                            <ul class="mb-0 small">
                                <li>Убедитесь, что вы авторизованы в системе</li>
                                <li>Проверьте, что у вас есть необходимые права доступа</li>
                                <li>Обратитесь к администратору для получения дополнительных привилегий</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Альтернативные действия -->
                    <div class="alternative-actions mb-4">
                        <p class="text-muted small mb-2">Попробуйте перейти к доступным разделам:</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary error-btn">
                                <i class="bi bi-house-door me-1"></i>Главная
                            </a>
                            <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-outline-success error-btn">
                                <i class="bi bi-calendar-check me-1"></i>Приёмы
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-info error-btn">
                                <i class="bi bi-bag-check me-1"></i>Заказы
                            </a>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button onclick="history.back()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                        
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary error-btn">
                            <i class="bi bi-house-door me-2"></i>На главную
                        </a>
                        
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-warning error-btn">
                            <i class="bi bi-gear me-2"></i>Настройки
                        </a>
                    </div>

                    <!-- Ссылка на поддержку -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            Если вы считаете, что это ошибка, свяжитесь с администратором системы
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
