@extends('layouts.admin')

@section('title', 'Редактировать питомца')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать питомца</h1>
    <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

<form method="POST" action="{{ route('admin.pets.update', $item->id) }}" class="needs-validation" novalidate>
    @csrf
    @method('PATCH')
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="breed_id" class="form-label">Порода</label>
            <select class="form-select @error('breed_id') is-invalid @enderror" id="breed_id" name="breed_id" data-url="{{ route('admin.pets.breed-options') }}">
                <option value="">Выберите породу</option>
                @php
                    $breedId = old('breed_id', $item->breed_id);
                @endphp
                @if($breedId)
                    @php
                        $selectedBreed = \App\Models\Breed::find($breedId);
                    @endphp
                    @if($selectedBreed)
                        <option value="{{ $selectedBreed->id }}" selected>{{ $selectedBreed->name }}</option>
                    @endif
                @endif
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
            <input type="text" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ $birthdate }}" autocomplete="off">
            @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="gender" class="form-label">Пол</label>
            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                <option value="">Выберите пол</option>
                <option value="male" @if(old('gender', $item->gender) == 'male') selected @endif>Самец</option>
                <option value="female" @if(old('gender', $item->gender) == 'female') selected @endif>Самка</option>
                <option value="unknown" @if(old('gender', $item->gender) == 'unknown') selected @endif>Неизвестно</option>
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="client_id" class="form-label">Владелец</label>
            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" data-url="{{ route('admin.pets.client-options') }}">
                <option value="">Выберите владельца</option>
                @php
                    $clientId = old('client_id', $item->client_id);
                @endphp
                @if($clientId)
                    @php
                        $selectedClient = \App\Models\User::find($clientId);
                    @endphp
                    @if($selectedClient)
                        <option value="{{ $selectedClient->id }}" selected>{{ $selectedClient->name }}</option>
                    @endif
                @endif
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
        <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
        </a>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        createDatepicker('#birthdate', {});
        
        // TomSelect для клиентов с поиском
        createTomSelect('#client_id', {
            placeholder: 'Выберите владельца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // TomSelect для пород с поиском
        createTomSelect('#breed_id', {
            placeholder: 'Выберите породу...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=true';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
    });
</script>
@endsection
