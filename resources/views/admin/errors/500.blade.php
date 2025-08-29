@extends('layouts.admin')

@section('title', 'Внутренняя ошибка сервера')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-12">
            <div class="card border-0 shadow-lg error-card">
                <div class="card-body text-center p-5">
                    <!-- Иконка 500 -->
                    <div class="error-icon mb-4">
                        <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                    </div>

                    <!-- Заголовок -->
                    <h1 class="h2 mb-3">Внутренняя ошибка сервера</h1>
                    
                    <!-- Код ошибки -->
                    <div class="error-code mb-3">
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            Ошибка 500
                        </span>
                    </div>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        Произошла внутренняя ошибка сервера. 
                        Наша команда уже уведомлена о проблеме и работает над её решением.
                    </p>

                    <!-- Что произошло -->
                    <div class="error-details mb-4">
                        <div class="alert alert-danger text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>Что произошло?
                            </h6>
                            <p class="mb-0 small">
                                Сервер временно не может обработать ваш запрос. 
                                Это может быть связано с техническими работами или временными сбоями.
                            </p>
                        </div>
                    </div>

                    <!-- Рекомендации -->
                    <div class="recommendations mb-4">
                        <div class="alert alert-info text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb me-2"></i>Рекомендации:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Подождите несколько минут и попробуйте снова</li>
                                <li>Обновите страницу (F5 или Ctrl+R)</li>
                                <li>Очистите кэш браузера</li>
                                <li>Попробуйте использовать другой браузер</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Альтернативные действия -->
                    <div class="alternative-actions mb-4">
                        <p class="text-muted small mb-2">Попробуйте перейти к другим разделам:</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-house-door me-1"></i>Главная
                            </a>
                            <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-calendar-check me-1"></i>Приёмы
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-bag-check me-1"></i>Заказы
                            </a>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button onclick="window.location.reload()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-clockwise me-2"></i>Обновить
                        </button>
                        
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary error-btn">
                            <i class="bi bi-house-door me-2"></i>На главную
                        </a>
                        
                        <button onclick="history.back()" class="btn btn-outline-info error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                    </div>

                    <!-- Статус системы -->
                    <div class="system-status mt-4 pt-3 border-top">
                        <p class="text-muted small mb-2">
                            <i class="bi bi-circle-fill text-success me-1"></i>
                            Система работает в штатном режиме
                        </p>
                        <small class="text-muted">
                            Последнее обновление: {{ now()->format('d.m.Y H:i') }}
                        </small>
                    </div>

                    <!-- Ссылка на поддержку -->
                    <div class="mt-3">
                        <small class="text-muted">
                            Если проблема повторяется, свяжитесь с администратором системы
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
