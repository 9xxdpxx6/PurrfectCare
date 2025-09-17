@extends('layouts.admin')

@section('title', 'Редактировать профиль')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать профиль</h1>
    <a href="{{ route('admin.employees.profile') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline">Назад в личный кабинет</span>
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i> Персональные данные
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.profile.update') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">ФИО</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $employee->name) }}" 
                                   maxlength="255">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $employee->phone) }}" 
                                   maxlength="20">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Смена пароля -->
                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-key"></i> Смена пароля
                            </h6>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label for="current_password" class="form-label">Текущий пароль <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password">
                            <div class="form-text text-muted">
                                Введите текущий пароль для подтверждения изменений
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input type="password" 
                                   class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" 
                                   name="new_password">
                            <div class="form-text text-muted">
                                Оставьте пустым, если не хотите менять пароль. Минимум 6 символов
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email (только для чтения) -->
                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   value="{{ $employee->email }}" 
                                   readonly
                                   disabled>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Email нельзя изменить самостоятельно. Обратитесь к администратору.
                            </div>
                        </div>
                    </div>

                    <!-- Информация о специальностях и филиалах (только для чтения) -->
                    <div class="row g-3 mt-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Специальности</label>
                            <div class="form-control-plaintext">
                                @if($employee->specialties->count())
                                    @foreach($employee->specialties as $specialty)
                                        <span class="badge bg-primary me-1">{{ $specialty->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Не указаны</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label class="form-label">Филиалы</label>
                            <div class="form-control-plaintext">
                                @if($employee->branches->count())
                                    @foreach($employee->branches as $branch)
                                        <span class="badge bg-secondary me-1">{{ $branch->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Не указаны</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('admin.employees.profile') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Дополнительная информация
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <small class="text-muted">Дата регистрации:</small>
                        <div class="fw-bold">{{ $employee->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <small class="text-muted">Последнее обновление:</small>
                        <div class="fw-bold">{{ $employee->updated_at->format('d.m.Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
