@extends('layouts.client')

@section('title', 'Страница не найдена - PurrfectCare')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="error-template">
                <h1 class="display-1 text-primary">404</h1>
                <h2 class="mb-4">Страница не найдена</h2>
                <div class="error-details mb-4">
                    <p class="lead">Извините, запрашиваемая страница не существует.</p>
                </div>
                <div class="error-actions">
                    <a href="{{ route('client.dashboard') }}" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-house me-2"></i>На главную
                    </a>
                    <a href="{{ route('client.appointment.branches') }}" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-calendar-plus me-2"></i>Записаться на прием
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
