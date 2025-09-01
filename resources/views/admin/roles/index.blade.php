@extends('layouts.admin')

@section('title', 'Управление ролями')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Роли - {{ $roles->count() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @can('roles.create')
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> <span class="d-none d-lg-inline">Создать роль</span>
        </a>
        @endcan
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:300px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" 
                   name="search" 
                   id="search" 
                   value="{{ request('search') }}" 
                   class="form-control" 
                   placeholder="Поиск по названию роли или модулям...">
        </div>
        <div class="flex-grow-1" style="min-width:170px;">
            <label for="sort" class="form-label mb-1">Сортировка</label>
            <select name="sort" id="sort" class="form-control" data-tomselect>
                <option value="id_desc" @selected(request('sort', 'id_desc')=='id_desc')>Сначала новые</option>
                <option value="id_asc" @selected(request('sort')=='id_asc')>Сначала старые</option>
                <option value="name_asc" @selected(request('sort')=='name_asc')>Название А-Я</option>
                <option value="name_desc" @selected(request('sort')=='name_desc')>Название Я-А</option>
            </select>
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>



<div class="row g-3">
    @foreach($roles as $role)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $role->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-1">
                                <span>ID:</span> {{ $role->id }}
                            </div>
                            <div class="text-muted mb-1">
                                <span>Пользователи:</span> {{ $role->users->count() }}
                            </div>
                            <div class="text-muted">
                                <span>Права:</span>
                                @php
                                    $permissionsByModule = $role->permissions->groupBy(function ($permission) {
                                        $parts = explode('.', $permission->name);
                                        return $parts[0] ?? 'other';
                                    });
                                @endphp
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex flex-column">
                                            @foreach($permissionsByModule->sortKeys() as $module => $modulePermissions)
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="text-muted me-3" style="min-width: 200px;">
                                                        {{ $moduleTranslations[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}:
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        @php
                                                            $operations = $modulePermissions->map(function ($permission) {
                                                                $parts = explode('.', $permission->name);
                                                                return $parts[1] ?? '';
                                                            });
                                                        @endphp
                                                        @foreach(['read', 'create', 'update', 'delete'] as $operation)
                                                            <div style="width: 20px; text-align: center;">
                                                                @if($operations->contains($operation))
                                                                    @if($operation === 'read')
                                                                        <i class="bi bi-eye text-info" title="Просмотр"></i>
                                                                    @elseif($operation === 'create')
                                                                        <i class="bi bi-patch-plus text-success" title="Создание"></i>
                                                                    @elseif($operation === 'update')
                                                                        <i class="bi bi-pencil text-warning" title="Редактирование"></i>
                                                                    @elseif($operation === 'delete')
                                                                        <i class="bi bi-trash text-danger" title="Удаление"></i>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-info">
                            <span class="d-none d-lg-inline-block">Просмотр</span>
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('roles.update')
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-warning">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('roles.delete')
                            @if(!in_array($role->name, ['super-admin', 'admin']))
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Вы уверены, что хотите удалить эту роль?')">
                                    <span class="d-none d-lg-inline-block">Удалить</span>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($roles->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-shield-exclamation display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Роли не найдены</h3>
        <p class="text-muted">Создайте новую роль для управления правами доступа.</p>
        @can('roles.create')
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Создать роль
        </a>
        @endcan
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // TomSelect для поля сортировки
        new createTomSelect('#sort', {
            placeholder: 'Выберите сортировку...',
            plugins: ['remove_button'],
            allowEmptyOption: true,
            maxOptions: 10,
            persist: false
        });
    });
</script>
@endpush