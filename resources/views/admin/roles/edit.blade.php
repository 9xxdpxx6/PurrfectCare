@extends('layouts.admin')

@section('title', 'Редактирование роли')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактирование роли: {{ $role->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lock"></i> Информация о роли
                </h5>
            </div>
                <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название роли</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $role->name) }}" 
                                           placeholder="Введите название роли"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Используйте только латинские буквы, цифры и дефисы
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h5>Права доступа</h5>
                                <p class="text-muted">Выберите права, которые будут назначены этой роли</p>
                                
                                @foreach($permissions as $module => $modulePermissions)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                {{ $moduleTranslations[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}
                                                <span class="badge bg-secondary ms-2">{{ $modulePermissions->count() }}</span>
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($modulePermissions as $permission)
                                                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->id }}" 
                                                                   id="permission_{{ $permission->id }}"
                                                                   {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                @php
                                                                    $parts = explode('.', $permission->name);
                                                                    $operation = $parts[1] ?? '';
                                                                    $operationName = $operationTranslations[$operation] ?? ucfirst($operation);
                                                                @endphp
                                                                {{ $operationName }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between gap-2">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Отмена
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Сохранить
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция для выбора/снятия всех чекбоксов в модуле
    function setupModuleSelectAll() {
        document.querySelectorAll('.card-header h6').forEach(header => {
            const card = header.closest('.card');
            const checkboxes = card.querySelectorAll('input[type="checkbox"]');
            
            if (checkboxes.length > 0) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            }
        });
    }
    
    setupModuleSelectAll();
});
</script>
@endpush
@endsection
