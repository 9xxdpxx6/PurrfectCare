@extends('layouts.admin')

@section('title', 'Просмотр приёма')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Приём от {{ $item->starts_at->format('d.m.Y H:i') }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('admin.visits.edit', $item) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <!-- Основная информация -->
    <div class="col-12 col-lg-8 order-1 order-lg-1">
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
                        
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <span class="text-muted">Статус:</span>
                            @if($item->status)
                                <span class="badge fs-6" style='background-color: {{ $item->status->color ?? '#6c757d' }};'>
                                    {{ $item->status->name }}
                                </span>
                            @else
                                <span class="text-muted">Не указан</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if($item->client)
                            <p class="mb-1 text-wrap"><strong>Клиент:</strong> {{ $item->client->name }}</p>
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
                        
                        @if($item->pet)
                            <p class="mb-1 text-wrap"><strong>Питомец:</strong> {{ $item->pet->name }}
                                @if($item->pet->breed)
                                    ({{ $item->pet->breed->species->name ?? '' }} - {{ $item->pet->breed->name ?? '' }})
                                @endif
                            </p>
                        @else
                            <span class="text-muted">Не указан</span>
                        @endif
                    </div>
                </div>
                
                @if($item->schedule && $item->schedule->veterinarian)
                    <hr>
                    <h6 class="text-muted">Ветеринар</h6>
                    <div class="d-flex align-items-center gap-2">
                        <p class="fs-5 mb-0">{{ $item->schedule->veterinarian->name }}</p>
                        <a href="{{ route('admin.employees.show', $item->schedule->veterinarian) }}" class="btn btn-outline-primary btn-sm" title="Профиль ветеринара">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
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
                    <div class="accordion" id="symptomsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="symptomsHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#symptomsCollapse" aria-expanded="false" aria-controls="symptomsCollapse">
                                    Все симптомы ({{ $item->symptoms->count() }})
                                </button>
                            </h2>
                            <div id="symptomsCollapse" class="accordion-collapse collapse" aria-labelledby="symptomsHeading">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach($item->symptoms as $symptom)
                                            <li class="list-group-item">
                                                <strong>
                                                    @if($symptom->dictionarySymptom)
                                                        {{ $symptom->dictionarySymptom->name }}
                                                    @elseif($symptom->custom_symptom)
                                                        {{ $symptom->custom_symptom }}
                                                    @else
                                                        <span class="text-muted">Без названия</span>
                                                    @endif
                                                </strong>
                                                <br>
                                                @if($symptom->dictionarySymptom && $symptom->dictionarySymptom->description)
                                                    <span class="text-muted">{{ $symptom->dictionarySymptom->description }}</span>
                                                @elseif($symptom->notes)
                                                    <span class="text-muted">{{ $symptom->notes }}</span>
                                                @else
                                                    <span class="text-muted">Нет описания</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
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
                    <div class="accordion" id="diagnosesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="diagnosesHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#diagnosesCollapse" aria-expanded="false" aria-controls="diagnosesCollapse">
                                    Все диагнозы ({{ $item->diagnoses->count() }})
                                </button>
                            </h2>
                            <div id="diagnosesCollapse" class="accordion-collapse collapse" aria-labelledby="diagnosesHeading">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach($item->diagnoses as $diagnosis)
                                            <li class="list-group-item">
                                                <strong>
                                                    @if($diagnosis->dictionaryDiagnosis)
                                                        {{ $diagnosis->dictionaryDiagnosis->name }}
                                                    @elseif($diagnosis->custom_diagnosis)
                                                        {{ $diagnosis->custom_diagnosis }}
                                                    @else
                                                        <span class="text-muted">Без названия</span>
                                                    @endif
                                                </strong>
                                                <br>
                                                @if($diagnosis->dictionaryDiagnosis && $diagnosis->dictionaryDiagnosis->description)
                                                    <span class="text-muted">{{ $diagnosis->dictionaryDiagnosis->description }}</span>
                                                @elseif($diagnosis->treatment_plan)
                                                    <span class="text-muted">{{ $diagnosis->treatment_plan }}</span>
                                                @else
                                                    <span class="text-muted">Нет описания</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Блок действий -->
    <div class="col-12 col-lg-4 order-2 order-lg-2">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">Действия</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.visits.edit', $item) }}" class="btn btn-outline-warning">
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
                    <hr>
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
    </div>
</div>
@endsection 