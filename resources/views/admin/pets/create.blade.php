@extends('layouts.admin')

@section('title', 'Добавить питомца')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить питомца</h1>
    @if(request('client_id') || $selectedClientId)
        @php
            $clientId = request('client_id', $selectedClientId);
        @endphp
        <a href="{{ route('admin.users.show', $clientId) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    @else
    <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
    @endif
</div>

@if(request('client_id') || $selectedClientId)
    @php
        $clientId = request('client_id', $selectedClientId);
        $selectedClient = $clients->firstWhere('id', $clientId);
    @endphp
    @if($selectedClient)
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i> 
            <strong>Добавление питомца для клиента:</strong> {{ $selectedClient->name }}
        </div>
    @endif
@endif

<form method="POST" action="{{ route('admin.pets.store') }}" class="needs-validation" novalidate>
    @csrf
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="breed_id" class="form-label">Порода</label>
            <select class="form-select @error('breed_id') is-invalid @enderror" id="breed_id" name="breed_id" data-url="{{ route('admin.pets.breed-options') }}">
                <option value="">Выберите породу</option>
                @if(old('breed_id'))
                    @php
                        $selectedBreed = \App\Models\Breed::find(old('breed_id'));
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
                $birthdate = old('birthdate');
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
                <option value="male" @if(old('gender') == 'male') selected @endif>Самец</option>
                <option value="female" @if(old('gender') == 'female') selected @endif>Самка</option>
                <option value="unknown" @if(old('gender') == 'unknown') selected @endif>Неизвестно</option>
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="client_id" class="form-label">Владелец</label>
            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" data-url="{{ route('admin.pets.client-options') }}">
                <option value="">Выберите владельца</option>
                @php
                    $clientId = old('client_id', $selectedClientId ?? request('client_id'));
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
            <input type="number" step="0.01" class="form-control @error('temperature') is-invalid @enderror" id="temperature" name="temperature" value="{{ old('temperature') }}">
            @error('temperature')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 col-lg-4">
            <label for="weight" class="form-label">Вес (кг)</label>
            <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight') }}">
            @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        @if(request('client_id') || $selectedClientId)
            @php
                $clientId = request('client_id', $selectedClientId);
            @endphp
            <a href="{{ route('admin.users.show', $clientId) }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
            </a>
        @else
            <a href="{{ route('admin.pets.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
            </a>
        @endif
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