@extends('layouts.admin')

@section('title', 'Настройки')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">
        <i class="bi bi-gear-fill me-2"></i>
        Настройки
    </h1>
</div>

<div class="row g-4">
    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm settings-card">
            <div class="card-header bg-gradient-primary text-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-pulse me-2"></i>Анализы
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.lab-tests.types.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-clipboard-data me-3 text-primary"></i>Типы анализов
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.lab-tests.params.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-list-check me-3 text-primary"></i>Параметры анализов
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm settings-card">
            <div class="card-header bg-gradient-success text-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>Система
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.system.statuses.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-tags me-3 text-success"></i>Статусы
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.system.units.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-rulers me-3 text-success"></i>Единицы измерений
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.system.branches.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-building me-3 text-success"></i>Филиалы
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.system.specialties.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-person-badge me-3 text-success"></i>Специальности
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm settings-card">
            <div class="card-header bg-gradient-warning text-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-paw me-2"></i>Животные
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.animals.species.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-collection me-3 text-warning"></i>Виды животных
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.animals.breeds.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-tags me-3 text-warning"></i>Породы
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm settings-card">
            <div class="card-header bg-gradient-info text-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-truck me-2"></i>Поставки
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.suppliers.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-truck me-3 text-info"></i>Поставщики
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card h-100 border-0 shadow-sm settings-card">
            <div class="card-header bg-gradient-danger text-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-journal-medical me-2"></i>Медицина
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.settings.dictionary.diagnoses.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-journal-text me-3 text-danger"></i>Диагнозы (словарь)
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.dictionary.symptoms.index') }}" class="list-group-item list-group-item-action border-0 py-3 px-4 settings-link">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium">
                                <i class="bi bi-exclamation-triangle me-3 text-danger"></i>Симптомы (словарь)
                            </span>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.settings-card {
    border-radius: 15px !important;
    overflow: hidden;
}

.settings-card .card-header {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark, #0056b3) 100%) !important;
    padding: 1.25rem 1.5rem;
    border-radius: 15px 15px 0 0 !important;
}

.settings-card .card-header.bg-gradient-success {
    background: linear-gradient(135deg, var(--bs-success) 0%, #157347 100%) !important;
}

.settings-card .card-header.bg-gradient-warning {
    background: linear-gradient(135deg, var(--bs-warning) 0%, #e0a800 100%) !important;
}

.settings-card .card-header.bg-gradient-info {
    background: linear-gradient(135deg, var(--bs-info) 0%, #087990 100%) !important;
}

.settings-card .card-header.bg-gradient-danger {
    background: linear-gradient(135deg, var(--bs-danger) 0%, #b02a37 100%) !important;
}

.settings-link {
    transition: all 0.2s ease;
    text-decoration: none !important;
}

.settings-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    padding-left: 1.75rem !important;
}

.settings-link:hover .bi-chevron-right {
    transform: translateX(5px);
    transition: transform 0.2s ease;
}

.settings-link .bi-chevron-right {
    transition: transform 0.2s ease;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--bs-primary) 0%, #0056b3 100%) !important;
}
</style>
@endpush