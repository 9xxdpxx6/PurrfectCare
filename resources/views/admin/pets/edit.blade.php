@extends('layouts.admin')

@section('title', 'Редактировать питомца')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать питомца</h1>
    <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад к списку
    </a>
</div>

<form method="POST" action="{{ route('admin.pets.update', $item->id) }}" class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="breed_id" class="form-label">Порода</label>
            <select class="form-select @error('breed_id') is-invalid @enderror" id="breed_id" name="breed_id" required>
                <option value="">Выберите породу</option>
                @foreach($breeds as $breed)
                    <option value="{{ $breed->id }}" @if(old('breed_id', $item->breed_id) == $breed->id) selected @endif>{{ $breed->name }}</option>
                @endforeach
            </select>
            @error('breed_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="birthdate" class="form-label">Дата рождения</label>
            @php
                $birthdate = old('birthdate', $item->birthdate);
                if (!$birthdate) {
                    $birthdate = \Carbon\Carbon::now()->format('d.m.Y');
                } else {
                    try {
                        $birthdate = \Carbon\Carbon::parse($birthdate)->format('d.m.Y');
                    } catch (\Exception $e) {
                        $birthdate = $birthdate;
                    }
                }
            @endphp
            <input type="text" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ $birthdate }}" readonly>
            @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="gender" class="form-label">Пол</label>
            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                <option value="">Выберите пол</option>
                <option value="male" @if(old('gender', $item->gender) == 'male') selected @endif>Самец</option>
                <option value="female" @if(old('gender', $item->gender) == 'female') selected @endif>Самка</option>
                <option value="unknown" @if(old('gender', $item->gender) == 'unknown') selected @endif>Неизвестно</option>
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="client_id" class="form-label">Владелец</label>
            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                <option value="">Выберите владельца</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @if(old('client_id', $item->client_id) == $client->id) selected @endif>{{ $client->name }}</option>
                @endforeach
            </select>
            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="temperature" class="form-label">Температура (&deg;C)</label>
            <input type="number" step="0.01" class="form-control @error('temperature') is-invalid @enderror" id="temperature" name="temperature" value="{{ old('temperature', $item->temperature) }}">
            @error('temperature')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="weight" class="form-label">Вес (кг)</label>
            <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight', $item->weight) }}">
            @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">Отмена</a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        createDatepicker('#birthdate', {
            
        });
        new createTomSelect('#client_id', {
            placeholder: 'Выберите владельца...',
        });
        new createTomSelect('#breed_id', {
            placeholder: 'Выберите породу...',
        });
    });
</script>
@endsection 