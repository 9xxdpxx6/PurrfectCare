@extends('layouts.admin')

@section('title', 'Редактировать клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать клиента</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12">
        <form action="{{ route('admin.users.update', $item) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="row">
                <div class="col-12 col-lg-6 mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-lg-6 mb-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $item->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $item->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="address" class="form-label">Адрес</label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $item->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                <!-- Левая группа - Отмена -->
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary order-sm-first">
                    <span class="d-inline">Отмена</span>
                </a>
                
                <!-- Правая группа - Сброс пароля и Сохранить -->
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <a href="{{ route('admin.users.resetPassword', $item) }}" class="btn btn-outline-warning"
                        onclick="return confirm('Сбросить пароль для клиента {{ $item->name }}?');">
                        <i class="bi bi-key"></i> <span class="d-inline">Сбросить пароль</span>
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection 