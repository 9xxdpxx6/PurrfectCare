@extends('layouts.admin')

@section('title', 'Сотрудник: ' . $employee->name)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 col-12 col-md-7 col-xl-8">Сотрудник: {{ $employee->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
        </a>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
        </a>
    </div>
</div>

<div class="row">
    <!-- Информация о сотруднике -->
    <div class="col-12 col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person"></i> Информация о сотруднике</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Имя:</div><div class="col-sm-8">{{ $employee->name }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Email:</div><div class="col-sm-8">{{ $employee->email }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Телефон:</div><div class="col-sm-8">{{ $employee->phone }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Дата создания:</div><div class="col-sm-8">{{ $employee->created_at->format('d.m.Y H:i') }}</div></div>
                <div class="row mb-3"><div class="col-sm-4 fw-bold">Дата обновления:</div><div class="col-sm-8">{{ $employee->updated_at->format('d.m.Y H:i') }}</div></div>
            </div>
        </div>
    </div>

    <!-- Должности и филиалы -->
    <div class="col-12 col-xl-3 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-briefcase"></i> Должности и филиалы</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Специальности:</strong><br>
                    @if($employee->specialties->count())
                        {{ $employee->specialties->pluck('name')->join(', ') }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div>
                    <strong>Филиалы:</strong><br>
                    @if($employee->branches->count())
                        {{ $employee->branches->pluck('name')->join(', ') }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Действия -->
    <div class="col-12 col-xl-3 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Редактировать
                    </a>
                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Удалить сотрудника?');" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </form>
                    <a href="{{ route('admin.employees.resetPassword', $employee) }}" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Сбросить пароль
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 