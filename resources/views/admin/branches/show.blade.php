@extends('layouts.admin')

@section('title', 'Просмотр филиала')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Филиал: {{ $branch->name }}</h3>
                        <div>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                            <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад к списку
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Название:</th>
                                    <td>{{ $branch->name }}</td>
                                </tr>
                                <tr>
                                    <th>Адрес:</th>
                                    <td>{{ $branch->address }}</td>
                                </tr>
                                <tr>
                                    <th>Телефон:</th>
                                    <td>
                                        <a href="tel:{{ $branch->phone }}">{{ $branch->phone }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>
                                        <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Часы работы:</th>
                                    <td>{{ $branch->working_hours ?? 'Не указано' }}</td>
                                </tr>
                                <tr>
                                    <th>Описание:</th>
                                    <td>{{ $branch->description ?? 'Описание отсутствует' }}</td>
                                </tr>
                                <tr>
                                    <th>Дата создания:</th>
                                    <td>{{ $branch->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Последнее обновление:</th>
                                    <td>{{ $branch->updated_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Статистика</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-users"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Сотрудники</span>
                                                    <span class="info-box-number">{{ $branch->employees()->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-stethoscope"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Услуги</span>
                                                    <span class="info-box-number">{{ $branch->services()->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Связанные данные -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Сотрудники филиала</h5>
                                </div>
                                <div class="card-body">
                                    @if($branch->employees()->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Имя</th>
                                                        <th>Специальность</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($branch->employees()->with('specialties')->get() as $employee)
                                                        <tr>
                                                            <td>{{ $employee->name }}</td>
                                                            <td>
                                                                @foreach($employee->specialties as $specialty)
                                                                    <span class="badge badge-primary">{{ $specialty->name }}</span>
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">Сотрудники не назначены</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Услуги филиала</h5>
                                </div>
                                <div class="card-body">
                                    @if($branch->services()->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Название</th>
                                                        <th>Цена</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($branch->services as $service)
                                                        <tr>
                                                            <td>{{ $service->name }}</td>
                                                            <td>{{ number_format($service->price, 0, ',', ' ') }} ₽</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted">Услуги не назначены</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 