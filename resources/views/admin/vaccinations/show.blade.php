@extends('layouts.admin')

@section('title', 'Просмотр вакцинации')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Вакцинация от {{ $item->administered_at->format('d.m.Y') }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('admin.vaccinations.edit', $item) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-shield-check"></i> Основная информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mt-md-0">
                        <h6 class="text-muted">Дата проведения</h6>
                        <p class="fs-5">{{ $item->administered_at->format('d.m.Y') }}</p>
                        
                        @if($item->next_due)
                            <h6 class="text-muted mt-3">Следующая вакцинация: {{ $item->next_due->format('d.m.Y') }}</h6>
                            @php
                                $daysUntilDue = now()->diffInDays($item->next_due, false);
                            @endphp
                            <p class="fs-5 mb-0">
                                @if($daysUntilDue < 0)
                                    <span class="badge bg-danger">просрочена на {{ abs($daysUntilDue) }} дн.</span>
                                @elseif($daysUntilDue <= 30)
                                    <span class="badge bg-warning text-dark">через {{ $daysUntilDue }} дн.</span>
                                @else
                                    <span class="badge bg-success">через {{ $daysUntilDue }} дн.</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($item->pet)
                            <p class="mb-1 text-wrap"><strong>Питомец:</strong> {{ $item->pet->name }}
                                @if($item->pet->breed)
                                    ({{ $item->pet->breed->species->name ?? '' }} - {{ $item->pet->breed->name ?? '' }})
                                @endif
                            </p>
                            @if($item->pet->client)
                                <p class="text-muted">
                                    <strong>Владелец:</strong> {{ $item->pet->client->name }}<br>
                                    <i class="bi bi-envelope"></i> {{ $item->pet->client->email }}<br>
                                    @if($item->pet->client->phone)
                                        <i class="bi bi-telephone"></i> {{ $item->pet->client->phone }}<br>
                                    @endif
                                </p>
                            @endif
                        @else
                            <span class="text-muted">Питомец не указан</span>
                        @endif
                    </div>
                </div>
                
                @if($item->veterinarian)
                    <hr>
                    <h6 class="text-muted">Ветеринар</h6>
                    <div class="d-flex align-items-center gap-2">
                        <p class="fs-5 mb-0">{{ $item->veterinarian->name }}</p>
                        <a href="{{ route('admin.employees.show', $item->veterinarian) }}" class="btn btn-outline-primary btn-sm" title="Профиль ветеринара">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                    @if($item->veterinarian->specialization)
                        <p class="text-muted">{{ $item->veterinarian->specialization }}</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Препараты -->
        @if($item->drugs && $item->drugs->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-capsule"></i> Препараты ({{ $item->drugs->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @foreach($item->drugs as $drug)
                            <div class="border rounded p-3 bg-body-tertiary position-relative">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 pe-5 pe-md-2 flex-grow-1">
                                                <a href="{{ route('admin.drugs.show', $drug) }}" class="text-decoration-none">
                                                    {{ $drug->name }}
                                                </a>
                                            </h6>
                                        </div>
                                        
                                        <a href="{{ route('admin.drugs.show', $drug) }}" class="btn btn-outline-primary btn-sm d-md-none position-absolute top-0 end-0 m-3" title="Подробнее о препарате">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($drug->description)
                                            <p class="text-muted small mb-2">{{ $drug->description }}</p>
                                        @endif
                                        
                                        <div class="d-flex flex-column flex-md-row gap-2 gap-md-4">
                                            {{-- <div>
                                                <small class="text-muted">Номер партии:</small>
                                                <div><code class="fs-6">{{ $drug->pivot->batch_number }}</code></div>
                                            </div> --}}
                                            <div>
                                                <small class="text-muted">Дозировка:</small>
                                                <div class="fw-bold">{{ $drug->pivot->dosage }} {{ $drug->unit->name ?? '' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-none d-md-flex align-self-center">
                                        <a href="{{ route('admin.drugs.show', $drug) }}" class="btn btn-outline-primary btn-sm" title="Подробнее о препарате">
                                            <i class="bi bi-eye"></i> <span class="d-lg-inline d-none">Подробнее</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Боковая панель -->
    <div class="col-12 col-lg-4 order-2 order-lg-2">
        @if($item->pet)
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-heart"></i> Информация о питомце
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Кличка:</strong> {{ $item->pet->name }}</p>
                    @if($item->pet->breed)
                        <p class="mb-1"><strong>Вид:</strong> {{ $item->pet->breed->species->name ?? 'Не указан' }}</p>
                        <p class="mb-1"><strong>Порода:</strong> {{ $item->pet->breed->name ?? 'Не указана' }}</p>
                    @endif
                    @if($item->pet->birth_date)
                        <p class="mb-1"><strong>Дата рождения:</strong> {{ $item->pet->birth_date->format('d.m.Y') }}</p>
                        <p class="mb-1"><strong>Возраст:</strong> {{ $item->pet->birth_date->age }} лет</p>
                    @endif
                    @if($item->pet->gender)
                        <p class="mb-1"><strong>Пол:</strong> {{ $item->pet->gender == 'male' ? 'Самец' : 'Самка' }}</p>
                    @endif
                    @if($item->pet->weight)
                        <p class="mb-1"><strong>Вес:</strong> {{ $item->pet->weight }} кг</p>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.pets.show', $item->pet) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Подробнее о питомце
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Действия
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.vaccinations.edit', $item) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать вакцинацию
                    </a>
                    
                    @if($item->pet)
                        <a href="{{ route('admin.pets.show', $item->pet) }}" class="btn btn-outline-success">
                            <i class="bi bi-heart"></i> Карточка питомца
                        </a>
                    @endif
                    
                    @if($item->veterinarian)
                        <a href="{{ route('admin.employees.show', $item->veterinarian) }}" class="btn btn-outline-info">
                            <i class="bi bi-person-badge"></i> Профиль ветеринара
                        </a>
                    @endif
                    <hr>
                    <form action="{{ route('admin.vaccinations.destroy', $item) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" 
                                onclick="return confirm('Удалить вакцинацию от {{ $item->administered_at->format('d.m.Y') }}?');">
                            <i class="bi bi-trash"></i> Удалить вакцинацию
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 