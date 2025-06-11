@extends('layouts.admin')

@section('title', 'Просмотр приёма')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Просмотр приёма</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.visits.edit', $item) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Редактировать
            </a>
            <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> К списку приёмов
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-check"></i> Основная информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Дата и время</h6>
                        <p class="fs-5">{{ \Carbon\Carbon::parse($item->starts_at)->format('d.m.Y H:i') }}</p>
                        
                        <h6 class="text-muted mt-3">Статус</h6>
                        @if($item->status)
                            <span class="badge fs-6" style="background-color: {{ $item->status->color ?? '#6c757d' }}">
                                {{ $item->status->name }}
                            </span>
                        @else
                            <span class="text-muted">Не указан</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Клиент</h6>
                        @if($item->client)
                            <p class="fs-5">{{ $item->client->name }}</p>
                            <p class="text-muted">
                                <i class="bi bi-envelope"></i> {{ $item->client->email }}<br>
                                @if($item->client->phone)
                                    <i class="bi bi-telephone"></i> {{ $item->client->phone }}<br>
                                @endif
                                @if($item->client->address)
                                    <i class="bi bi-geo-alt"></i> {{ $item->client->address }}
                                @endif
                            </p>
                        @else
                            <span class="text-muted">Не указан</span>
                        @endif
                        
                        <h6 class="text-muted mt-3">Питомец</h6>
                        @if($item->pet)
                            <p class="fs-5">{{ $item->pet->name }}</p>
                            @if($item->pet->breed)
                                <p class="text-muted">
                                    {{ $item->pet->breed->species->name ?? '' }} - {{ $item->pet->breed->name ?? '' }}
                                </p>
                            @endif
                        @else
                            <span class="text-muted">Не указан</span>
                        @endif
                    </div>
                </div>
                
                @if($item->schedule && $item->schedule->veterinarian)
                    <hr>
                    <h6 class="text-muted">Ветеринар</h6>
                    <p class="fs-5">{{ $item->schedule->veterinarian->name }}</p>
                    @if($item->schedule->veterinarian->specialization)
                        <p class="text-muted">{{ $item->schedule->veterinarian->specialization }}</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Жалобы и заметки -->
        @if($item->complaints || $item->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-left-text"></i> Детали приёма
                    </h5>
                </div>
                <div class="card-body">
                    @if($item->complaints)
                        <h6 class="text-muted">Жалобы</h6>
                        <div class="alert alert-warning">
                            {{ $item->complaints }}
                        </div>
                    @endif
                    
                    @if($item->notes)
                        <h6 class="text-muted">Заметки</h6>
                        <div class="alert alert-info">
                            {{ $item->notes }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Симптомы -->
        @if($item->symptoms && $item->symptoms->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Симптомы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($item->symptoms as $symptom)
                            <span class="badge bg-warning text-dark">{{ $symptom->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Диагнозы -->
        @if($item->diagnoses && $item->diagnoses->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard2-pulse"></i> Диагнозы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($item->diagnoses as $diagnosis)
                            <span class="badge bg-success">{{ $diagnosis->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Услуги -->
        @if($item->services && $item->services->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> Оказанные услуги
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Услуга</th>
                                    <th>Цена</th>
                                    <th>Описание</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPrice = 0; @endphp
                                @foreach($item->services as $visitService)
                                    @if($visitService->service)
                                        <tr>
                                            <td>{{ $visitService->service->name }}</td>
                                            <td>
                                                @if($visitService->service->price)
                                                    {{ number_format($visitService->service->price, 0, ',', ' ') }} ₽
                                                    @php $totalPrice += $visitService->service->price; @endphp
                                                @else
                                                    <span class="text-muted">Не указана</span>
                                                @endif
                                            </td>
                                            <td>{{ $visitService->service->description ?? '-' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            @if($totalPrice > 0)
                                <tfoot>
                                    <tr class="table-info">
                                        <th>Итого:</th>
                                        <th>{{ number_format($totalPrice, 0, ',', ' ') }} ₽</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Лабораторные анализы -->
        @if($item->labTests && $item->labTests->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-data"></i> Лабораторные анализы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Анализ</th>
                                    <th>Дата проведения</th>
                                    <th>Результат</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->labTests as $labTest)
                                    <tr>
                                        <td>{{ $labTest->name ?? 'Не указан' }}</td>
                                        <td>{{ $labTest->conducted_at ? \Carbon\Carbon::parse($labTest->conducted_at)->format('d.m.Y H:i') : '-' }}</td>
                                        <td>{{ $labTest->result ?? 'Ожидается' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Вакцинации -->
        @if($item->vaccinations && $item->vaccinations->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check"></i> Вакцинации
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Вакцина</th>
                                    <th>Дата введения</th>
                                    <th>Следующая вакцинация</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->vaccinations as $vaccination)
                                    <tr>
                                        <td>{{ $vaccination->vaccine_name ?? 'Не указана' }}</td>
                                        <td>{{ $vaccination->administered_at ? \Carbon\Carbon::parse($vaccination->administered_at)->format('d.m.Y') : '-' }}</td>
                                        <td>{{ $vaccination->next_vaccination_date ? \Carbon\Carbon::parse($vaccination->next_vaccination_date)->format('d.m.Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Боковая панель -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Действия</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.visits.edit', $item) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Редактировать приём
                    </a>
                    
                    @if($item->client)
                        <a href="{{ route('admin.users.show', $item->client) }}" class="btn btn-outline-info">
                            <i class="bi bi-person"></i> Профиль клиента
                        </a>
                    @endif
                    
                    @if($item->pet)
                        <a href="{{ route('admin.pets.show', $item->pet) }}" class="btn btn-outline-success">
                            <i class="bi bi-heart"></i> Карточка питомца
                        </a>
                    @endif
                    
                    <form action="{{ route('admin.visits.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Удалить приём {{ \Carbon\Carbon::parse($item->starts_at)->format('d.m.Y H:i') }}? Это действие нельзя отменить.');">
                            <i class="bi bi-trash"></i> Удалить приём
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Статистика</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-primary mb-0">{{ $item->services->count() }}</h4>
                            <small class="text-muted">Услуг</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-success mb-0">{{ $item->diagnoses->count() }}</h4>
                            <small class="text-muted">Диагнозов</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-warning mb-0">{{ $item->symptoms->count() }}</h4>
                            <small class="text-muted">Симптомов</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-info mb-0">{{ $item->labTests->count() }}</h4>
                            <small class="text-muted">Анализов</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 