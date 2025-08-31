@extends('layouts.admin')

@section('title', 'Просмотр анализа')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Анализ от {{ $item->received_at->format('d.m.Y') ?? '...' }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('lab_tests.update')
        <a href="{{ route('admin.lab-tests.edit', $item) }}" class="btn btn-outline-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        @endcan
        <a href="{{ route('admin.lab-tests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-12 col-lg-8 order-1 order-lg-1">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data"></i> Информация об анализе
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <span class="text-muted me-2">Тип анализа:</span>
                                    <strong>{{ $item->labTestType->name }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <span class="text-muted me-2">Питомец:</span>
                                    <div>
                                        <strong>{{ $item->pet->name }}</strong>
                                        @if($item->pet->client)
                                            <br><small class="text-muted">{{ $item->pet->client->name }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <span class="text-muted me-2">Ветеринар:</span>
                                    <strong>{{ $item->veterinarian->name }}</strong>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <span class="text-muted me-2">Получен:</span>
                                    <strong>{{ $item->received_at->format('d.m.Y') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-md-end">
                            @if($item->completed_at)
                                <span class="badge bg-success me-2">
                                    <i class="bi bi-check-circle"></i> Завершен
                                </span>
                                <small class="text-muted">
                                    {{ $item->completed_at->diffForHumans() }}
                                </small>
                            @else
                                <span class="badge bg-warning me-2">
                                    <i class="bi bi-clock"></i> В процессе
                                </span>
                                <small class="text-muted">
                                    {{ $item->received_at->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                        @if($item->completed_at)
                            <div class="text-end mt-2">
                                <small class="text-muted">Завершен: {{ $item->completed_at->format('d.m.Y') }}</small>
                            </div>
                        @endif
                    </div>
                </div>

                @if($item->results->count() > 0)
                    <hr>
                    <h6>Результаты анализов</h6>
                    <div class="results-container">
                        <!-- Заголовки для десктопа -->
                        <div class="d-none d-md-flex results-header border-bottom pb-2 mb-3">
                            <div class="flex-fill">
                                <strong>Параметр</strong>
                            </div>
                            <div class="flex-fill">
                                <strong>Значение</strong>
                            </div>
                            <div class="flex-fill">
                                <strong>Заметки</strong>
                            </div>
                        </div>
                        
                        <!-- Результаты -->
                        @foreach($item->results as $result)
                            <div class="result-item border-bottom py-3">
                                <div class="row">
                                    <div class="col-md-4 mb-2 mb-md-0">
                                        <div class="d-md-none text-muted small mb-1">Параметр:</div>
                                        <div>
                                            <strong>{{ $result->labTestParam->name }}</strong>
                                                                    @if($result->labTestParam->unit_id)
                            <br><small class="text-muted">ID единицы: {{ $result->labTestParam->unit_id }}</small>
                        @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2 mb-md-0">
                                        <div class="d-md-none text-muted small mb-1">Значение:</div>
                                        <div>
                                            <span class="">{{ $result->value }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-md-none text-muted small mb-1">Заметки:</div>
                                        <div>
                                            @if($result->notes)
                                                <small class="text-muted">{{ $result->notes }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <hr>
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-4 text-muted"></i>
                        <h6 class="mt-3 text-muted">Результаты анализов отсутствуют</h6>
                        <p class="text-muted">Результаты еще не добавлены к этому анализу.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Блок действий -->
    <div class="col-12 col-lg-4 order-2 order-lg-2">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Действия</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('lab_tests.update')
                    <a href="{{ route('admin.lab-tests.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать анализ
                    </a>
                    @endcan
                    @if($item->pet)
                        <a href="{{ route('admin.pets.show', $item->pet) }}" class="btn btn-outline-success">
                            <i class="bi bi-heart"></i> Карточка питомца
                        </a>
                    @endif
                    @if($item->pet && $item->pet->client)
                        <a href="{{ route('admin.users.show', $item->pet->client) }}" class="btn btn-outline-info">
                            <i class="bi bi-person"></i> Профиль владельца
                        </a>
                    @endif
                    @if($item->veterinarian)
                        <a href="{{ route('admin.employees.show', $item->veterinarian) }}" class="btn btn-outline-primary">
                            <i class="bi bi-person-badge"></i> Профиль ветеринара
                        </a>
                    @endif
                    <hr>
                    @can('lab_tests.delete')
                    <form action="{{ route('admin.lab-tests.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Удалить анализ от {{ $item->received_at->format('d.m.Y') }}? Это действие нельзя отменить.');">
                            <i class="bi bi-trash"></i> Удалить анализ
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 