@extends('layouts.client')

@section('title', 'Страница не найдена - PurrfectCare')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-12">
            <div class="card border-0 shadow-lg error-card">
                <div class="card-body text-center p-5">
                    <!-- Иконка 404 -->
                    <div class="error-icon mb-4">
                        <i class="bi bi-search display-1 text-muted"></i>
                    </div>

                    <!-- Заголовок -->
                    <h1 class="h2 mb-3">Страница не найдена</h1>
                    
                    <!-- Код ошибки -->
                    <div class="error-code mb-3">
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            Ошибка 404
                        </span>
                    </div>

                    <!-- Описание -->
                    <p class="text-muted mb-4">
                        Запрашиваемая страница не существует или была перемещена. 
                        Возможно, вы перешли по устаревшей ссылке или допустили опечатку в адресе.
                    </p>

                    <!-- Поиск по сайту -->
                    <div class="search-suggestion mb-4">
                        <p class="text-muted small mb-2">Попробуйте найти нужную информацию:</p>
                        <form action="{{ route('client.index') }}" method="GET" class="d-flex justify-content-center">
                            <div class="input-group" style="max-width: 400px;">
                                <input type="text" name="search" class="form-control" placeholder="Поиск по сайту...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Быстрые ссылки -->
                    <div class="quick-links mb-4">
                        <p class="text-muted small mb-2">Популярные разделы:</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            @auth
                                <a href="{{ route('client.appointment.branches') }}" class="btn btn-sm btn-outline-primary error-btn">
                                    <i class="bi bi-calendar-plus me-1"></i>Записаться
                                </a>
                                <a href="{{ route('client.profile.pets') }}" class="btn btn-sm btn-outline-success error-btn">
                                    <i class="bi bi-heart me-1"></i>Мои питомцы
                                </a>
                                <a href="{{ route('client.profile.visits') }}" class="btn btn-sm btn-outline-info error-btn">
                                    <i class="bi bi-clock-history me-1"></i>История
                                </a>
                            @else
                                <a href="{{ route('client.login') }}" class="btn btn-sm btn-outline-primary error-btn">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Войти
                                </a>
                                <a href="{{ route('client.register') }}" class="btn btn-sm btn-outline-success error-btn">
                                    <i class="bi bi-person-plus me-1"></i>Регистрация
                                </a>
                            @endauth
                            <a href="{{ route('client.services') }}" class="btn btn-sm btn-outline-warning error-btn">
                                <i class="bi bi-list-ul me-1"></i>Услуги
                            </a>
                            <a href="{{ route('client.contacts') }}" class="btn btn-sm btn-outline-secondary error-btn">
                                <i class="bi bi-telephone me-1"></i>Контакты
                            </a>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <button onclick="history.back()" class="btn btn-outline-secondary error-btn">
                            <i class="bi bi-arrow-left me-2"></i>Назад
                        </button>
                        
                        <a href="{{ route('client.index') }}" class="btn btn-primary error-btn">
                            <i class="bi bi-house-door me-2"></i>На главную
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
