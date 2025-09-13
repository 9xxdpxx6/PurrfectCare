@extends('layouts.admin')

@section('title', 'Создание роли')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создание новой роли</h1>
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
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="card-body">


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название роли</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
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
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($modulePermissions as $permission)
                                                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="{{ $permission->id }}" 
                                                                   id="permission_{{ $permission->id }}"
                                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                @php
                                                                    $parts = explode('.', $permission->name);
                                                                    $operation = $parts[1] ?? '';
                                                                    $operationName = $operationTranslations[$operation] ?? ucfirst($operation);
                                                                @endphp
                                                                @if($operation === 'read')
                                                                    <i class="bi bi-eye text-info me-1"></i>
                                                                @elseif($operation === 'create')
                                                                    <i class="bi bi-patch-plus text-success me-1"></i>
                                                                @elseif($operation === 'update')
                                                                    <i class="bi bi-pencil text-warning me-1"></i>
                                                                @elseif($operation === 'delete')
                                                                    <i class="bi bi-trash text-danger me-1"></i>
                                                                @elseif($operation === 'export')
                                                                    <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i>
                                                                @endif
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
                                <i class="bi bi-check-lg"></i> Создать
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
