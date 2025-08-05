@extends('layouts.admin')

@section('title', 'Филиалы')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Филиалы</h3>
                        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Добавить филиал
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Поиск -->
                    <form method="GET" action="{{ route('admin.branches.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Поиск..." 
                                           value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}">
                                            Название
                                            @if(request('sort') == 'name')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Адрес</th>
                                    <th>Телефон</th>
                                    <th>Email</th>
                                    <th>Часы работы</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branches as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->address }}</td>
                                        <td>{{ $branch->phone }}</td>
                                        <td>{{ $branch->email }}</td>
                                        <td>{{ $branch->working_hours ?? 'Не указано' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.branches.show', $branch) }}" 
                                                   class="btn btn-sm btn-info" title="Просмотр">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.branches.edit', $branch) }}" 
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.branches.destroy', $branch) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этот филиал?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Филиалы не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="d-flex justify-content-center">
                        {{ $branches->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 