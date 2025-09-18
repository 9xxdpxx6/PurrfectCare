@extends('layouts.client')

@section('title', 'Страница устарела - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-12">
            <div class="card border-0 shadow-lg error-card">
                <div class="card-body text-center p-5">
                    <!-- Иконка 419 -->
                    <div class="error-icon mb-4">
                        <i class="bi bi-clock-history display-1 text-info"></i>
                    </div>

                    <!-- Заголовок -->
                    <h1 class="h2 mb-3">Страница устарела</h1>
                    
                    <!-- Код ошибки -->
                    <div class="error-code mb-3">
                        <span class="badge bg-info fs-6 px-3 py-2">
                            Ошибка 419
                        </span>
                    </div>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        Срок действия страницы истек. Обновите страницу и попробуйте снова.
                        Это происходит из-за истечения времени сессии или токена безопасности.
                    </p>

                    <!-- Информация о проблеме -->
                    <div class="error-details mb-4">
                        <div class="alert alert-info text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-clock-history me-2"></i>Почему это произошло?
                            </h6>
                            <p class="mb-0 small">
                                Страница была открыта слишком долго без активности, 
                                или истек срок действия токена безопасности. 
                                Это защитная мера для обеспечения безопасности.
                            </p>
                        </div>
                    </div>

                    <!-- Рекомендации -->
                    <div class="recommendations mb-4">
                        <div class="alert alert-warning text-start error-alert">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb me-2"></i>Как исправить:
                            </h6>
                            <ul class="mb-0 small">
                                <li>Обновите страницу (F5 или Ctrl+R)</li>
                                <li>Попробуйте выполнить действие заново</li>
                                <li>Убедитесь, что вы не оставляли страницу открытой слишком долго</li>
                                <li>Проверьте, что у вас стабильное интернет-соединение</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button onclick="window.location.reload()" class="btn btn-primary error-btn">
                            <i class="bi bi-arrow-clockwise me-2"></i>Обновить страницу
                        </button>
                        
                        <a href="{{ route('client.index') }}" class="btn btn-outline-primary error-btn">
                            <i class="bi bi-house-door me-2"></i>На главную
                        </a>
                        
                        <button onclick="history.back()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                    </div>

                    <!-- Дополнительная информация -->
                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted small mb-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Если проблема повторяется, попробуйте очистить кэш браузера
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
