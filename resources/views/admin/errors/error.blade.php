@extends('layouts.admin')

@section('title', 'Ошибка ' . $exception->getStatusCode())

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-12">
            <div class="card border-0 shadow-lg error-card">
                <div class="card-body text-center p-5">
                    <!-- Иконка ошибки -->
                    <div class="error-icon mb-4">
                        @switch($exception->getStatusCode())
                            @case(404)
                                <i class="bi bi-search display-1 text-muted"></i>
                                @break
                            @case(403)
                                <i class="bi bi-shield-exclamation display-1 text-warning"></i>
                                @break
                            @case(419)
                                <i class="bi bi-clock-history display-1 text-info"></i>
                                @break
                            @case(500)
                                <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                                @break
                            @case(401)
                                <i class="bi bi-lock display-1 text-warning"></i>
                                @break
                            @case(422)
                                <i class="bi bi-file-earmark-excel display-1 text-warning"></i>
                                @break
                            @case(429)
                                <i class="bi bi-hourglass-split display-1 text-info"></i>
                                @break
                            @default
                                <i class="bi bi-exclamation-circle display-1 text-muted"></i>
                        @endswitch
                    </div>

                    <!-- Заголовок ошибки -->
                    <h1 class="h2 mb-3">
                        @switch($exception->getStatusCode())
                            @case(404)
                                Страница не найдена
                                @break
                            @case(403)
                                Доступ запрещен
                                @break
                            @case(419)
                                Страница устарела
                                @break
                            @case(500)
                                Внутренняя ошибка сервера
                                @break
                            @case(401)
                                Требуется авторизация
                                @break
                            @case(422)
                                Ошибка валидации
                                @break
                            @case(429)
                                Слишком много запросов
                                @break
                            @default
                                Произошла ошибка
                        @endswitch
                    </h1>

                    <!-- Код ошибки -->
                    <div class="error-code mb-3">
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            Ошибка {{ $exception->getStatusCode() }}
                        </span>
                    </div>

                    <!-- Описание ошибки -->
                    <p class="text-muted mb-4">
                        @switch($exception->getStatusCode())
                            @case(404)
                                Запрашиваемая страница не существует или была перемещена.
                                @break
                            @case(403)
                                У вас нет прав для доступа к этому ресурсу.
                                @break
                            @case(419)
                                Срок действия страницы истек. Обновите страницу и попробуйте снова.
                                @break
                            @case(500)
                                Произошла внутренняя ошибка сервера. Попробуйте позже.
                                @break
                            @case(401)
                                Для доступа к этому ресурсу необходимо авторизоваться.
                                @break
                            @case(422)
                                Данные не прошли валидацию. Проверьте введенную информацию.
                                @break
                            @case(429)
                                Превышен лимит запросов. Подождите немного и попробуйте снова.
                                @break
                            @default
                                Произошла непредвиденная ошибка. Попробуйте позже.
                        @endswitch
                    </p>

                    <!-- Дополнительная информация для разработчиков -->
                    @if(config('app.debug') && $exception->getMessage())
                        <div class="alert alert-info text-start small mb-4">
                            <strong>Отладочная информация:</strong><br>
                            <code>{{ $exception->getMessage() }}</code>
                        </div>
                    @endif

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button onclick="history.back()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                        
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary error-btn">
                            <i class="bi bi-house-door me-2"></i>На главную
                        </a>
                        
                        @if($exception->getStatusCode() === 419)
                            <button onclick="window.location.reload()" class="btn btn-info error-btn">
                                <i class="bi bi-arrow-clockwise me-2"></i>Обновить
                            </button>
                        @endif
                    </div>

                    <!-- Ссылка на поддержку -->
                    <div class="mt-4 pt-3 border-top">
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
