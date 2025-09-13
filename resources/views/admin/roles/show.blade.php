@extends('layouts.admin')

@section('title', 'Просмотр роли')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Роль: {{ $role->name }}</h3>
                    <div class="d-flex gap-2">
                        @can('roles.update')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-warning">
                                <i class="bi bi-pencil"></i> <span class="d-none d-lg-inline">Редактировать</span>
                            </a>
                        @endcan
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                            <h5>Информация о роли</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-nowrap"><strong>Название:</strong></td>
                                        <td><span class="badge bg-secondary">{{ $role->name }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap"><strong>Guard:</strong></td>
                                        <td><span class="badge bg-info">{{ $role->guard_name }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap"><strong>Добавлено:</strong></td>
                                        <td>{{ $role->created_at->format('d.m.Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <h5>Статистика</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-nowrap"><strong>Количество прав:</strong></td>
                                        <td><span class="badge bg-success">{{ $role->permissions->count() }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap"><strong>Пользователи с этой ролью:</strong></td>
                                        <td><span class="badge bg-primary">{{ $role->users->count() }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <h5>Назначенные права</h5>
                    <p class="text-muted">Права, которые назначены этой роли</p>
                    
                    @if($role->permissions->count() > 0)
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
                                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mb-2 mb-md-1">
                                            <div class="text-muted me-md-3 mb-1 mb-md-0" style="min-width: 200px;">
                                                {{ $moduleTranslations[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}:
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @php
                                                    $operations = $modulePermissions->map(function ($permission) {
                                                        $parts = explode('.', $permission->name);
                                                        return $parts[1] ?? '';
                                                    });
                                                @endphp
                                                @foreach(['read', 'create', 'update', 'delete', 'export'] as $operation)
                                                    @if($operations->contains($operation))
                                                        <div class="d-flex align-items-center me-2 me-md-3">
                                                            @if($operation === 'read')
                                                                <i class="bi bi-eye text-info me-1"></i>
                                                                <small class="text-muted d-none d-sm-inline">Просмотр</small>
                                                            @elseif($operation === 'create')
                                                                <i class="bi bi-patch-plus text-success me-1"></i>
                                                                <small class="text-muted d-none d-sm-inline">Создание</small>
                                                            @elseif($operation === 'update')
                                                                <i class="bi bi-pencil text-warning me-1"></i>
                                                                <small class="text-muted d-none d-sm-inline">Редактирование</small>
                                                            @elseif($operation === 'delete')
                                                                <i class="bi bi-trash text-danger me-1"></i>
                                                                <small class="text-muted d-none d-sm-inline">Удаление</small>
                                                            @elseif($operation === 'export')
                                                                <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i>
                                                                <small class="text-muted d-none d-sm-inline">Экспорт</small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            У этой роли нет назначенных прав
                        </div>
                    @endif

                    <h5 class="mt-4">Сотрудники сотрудников с этой ролью</h5>
                    <p class="text-muted">Список сотрудников, которым назначена эта роль</p>
                    
                    @if($role->users->count() > 0)
                        <div class="row">
                            @foreach($role->users as $user)
                                <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                    <div class="card border-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="bi bi-person-vcard fs-3"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1 text-truncate">{{ $user->name ?? 'Без имени' }}</h6>
                                                    <small class="text-muted text-truncate d-block">{{ $user->email ?? 'Email не указан' }}</small>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <a href="{{ route('admin.employees.show', $user->id) }}" class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Этой роли не назначена ни одному пользователю
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
