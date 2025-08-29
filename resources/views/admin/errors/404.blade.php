@extends('layouts.admin')

@section('title', 'Страница не найдена')

@section('content')
<div class="container-fluid">
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
                        <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex justify-content-center">
                            <div class="input-group" style="max-width: 400px;">
                                <input type="text" name="search" class="form-control" placeholder="Поиск по админке...">
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
                            <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-outline-primary error-btn">
                                <i class="bi bi-calendar-check me-1"></i>Приёмы
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-success error-btn">
                                <i class="bi bi-bag-check me-1"></i>Заказы
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-info error-btn">
                                <i class="bi bi-people me-1"></i>Клиенты
                            </a>
                            <a href="{{ route('admin.pets.index') }}" class="btn btn-sm btn-outline-warning error-btn">
                                <i class="bi bi-heart me-1"></i>Питомцы
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
                    </div>

                    <!-- Ссылка на поддержку -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted">
                            Если вы уверены, что страница должна существовать, свяжитесь с администратором системы
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
