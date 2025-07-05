@extends('layouts.admin')

@section('title', 'Редактировать приём')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать приём</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> К списку приёмов
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.visits.update', $item) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_id" class="form-label">Клиент</label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required data-url="{{ route('admin.visits.client-options') }}">
                                <option value="">Выберите клиента</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @if(old('client_id', $item->client_id) == $client->id) selected @endif>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pet_id" class="form-label">Питомец</label>
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" required data-url="{{ route('admin.visits.pet-options') }}" @if(!old('client_id', $item->client_id)) disabled @endif>
                                <option value="">Выберите питомца</option>
                                @foreach($pets as $pet)
                                    <option value="{{ $pet->id }}" data-client="{{ $pet->client_id }}" @if(old('pet_id', $item->pet_id) == $pet->id) selected @endif>
                                        {{ $pet->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="schedule_id" class="form-label">Расписание</label>
                            <select name="schedule_id" id="schedule_id" class="form-select @error('schedule_id') is-invalid @enderror" required data-url="{{ route('admin.visits.schedule-options') }}">
                                <option value="">Выберите расписание</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" @if(old('schedule_id', $item->schedule_id) == $schedule->id) selected @endif>
                                        @if($schedule->veterinarian)
                                            {{ $schedule->veterinarian->name }} - 
                                        @endif
                                        {{ \Carbon\Carbon::parse($schedule->shift_starts_at)->format('d.m.Y H:i') }} - 
                                        {{ \Carbon\Carbon::parse($schedule->shift_ends_at)->format('H:i') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="starts_at" class="form-label">Время начала</label>
                            <input type="text" name="starts_at" id="starts_at" 
                                class="form-control @error('starts_at') is-invalid @enderror" 
                                value="{{ old('starts_at', $edit_starts_at ?? '') }}" readonly placeholder="дд.мм.гггг чч:мм" required>
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status_id" class="form-label">Статус</label>
                        <select name="status_id" id="status_id" class="form-select @error('status_id') is-invalid @enderror" required data-url="{{ route('admin.visits.status-options') }}">
                            <option value="">Выберите статус</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" @if(old('status_id', $item->status_id) == $status->id) selected @endif>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="complaints" class="form-label">Жалобы</label>
                        <textarea name="complaints" id="complaints" rows="3" 
                            class="form-control @error('complaints') is-invalid @enderror" 
                            placeholder="Опишите жалобы клиента...">{{ old('complaints', $item->complaints) }}</textarea>
                        @error('complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Заметки</label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="form-control @error('notes') is-invalid @enderror" 
                            placeholder="Дополнительные заметки...">{{ old('notes', $item->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Симптомы -->
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">Симптомы</label>
                        <select name="symptoms[]" id="symptoms" class="form-select" multiple data-url="{{ route('admin.visits.symptom-options') }}">
                        </select>
                    </div>

                    <!-- Диагнозы -->
                    <div class="mb-3">
                        <label for="diagnoses" class="form-label">Диагнозы</label>
                        <select name="diagnoses[]" id="diagnoses" class="form-select" multiple data-url="{{ route('admin.visits.diagnosis-options') }}">
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        
        // Инициализация TomSelect
        const clientTomSelect = new createTomSelect('#client_id', {
            placeholder: 'Выберите клиента...',
        });
        
        const petTomSelect = new createTomSelect('#pet_id', {
            placeholder: 'Выберите питомца...',
        });
        
        new createTomSelect('#schedule_id', {
            placeholder: 'Выберите расписание...',
        });
        
        new createTomSelect('#status_id', {
            placeholder: 'Выберите статус...',
        });
        
        new createTomSelect('#symptoms', {
            placeholder: 'Выберите симптомы...',
            create: true,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });
        
        new createTomSelect('#diagnoses', {
            placeholder: 'Выберите диагнозы...',
            create: true,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            }
        });
        
        // Фильтрация питомцев по клиенту
        function filterPetsByClient(clientId) {
            const petOptions = petSelect.querySelectorAll('option');
            petTomSelect.clear();
            petTomSelect.clearOptions();
            if (!clientId) {
                petSelect.setAttribute('disabled', 'disabled');
                return;
            } else {
                petSelect.removeAttribute('disabled');
            }
            petOptions.forEach(option => {
                if (option.value === '' || option.dataset.client === clientId) {
                    petTomSelect.addOption({
                        value: option.value,
                        text: option.textContent
                    });
                }
            });
            // Восстанавливаем выбранное значение питомца
            const currentPetId = '{{ old("pet_id", $item->pet_id) }}';
            if (currentPetId) {
                petTomSelect.setValue(currentPetId);
            }
        }
        
        // Слушатель изменения клиента
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
                filterPetsByClient(clientId);
        });
        
        // Инициализация при загрузке страницы
        const initialClientId = clientSelect.value;
            filterPetsByClient(initialClientId);

        createDatepicker('#starts_at', {
            timepicker: true
        });
    });
</script>
@endpush 