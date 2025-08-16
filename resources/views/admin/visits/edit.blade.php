@extends('layouts.admin')

@section('title', 'Редактировать приём')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать приём</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
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
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" data-url="{{ route('admin.visits.client-options') }}">
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
                            <label for="pet_id" class="form-label">Питомец <small class="text-muted">(необязательно)</small></label>
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" data-url="{{ route('admin.visits.pet-options') }}" @if(!old('client_id', $item->client_id)) disabled @endif>
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
                            <select name="schedule_id" id="schedule_id" class="form-select @error('schedule_id') is-invalid @enderror" data-url="{{ route('admin.visits.schedule-options') }}">
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
                            <label for="visit_time" class="form-label">
                                Время приёма <span id="time-interval" class="text-muted"></span>
                            </label>
                            @php
                                $visitTime = old('visit_time');
                                if (!$visitTime && $item->starts_at) {
                                    $visitTime = \Carbon\Carbon::parse($item->starts_at)->format('H:i');
                                } elseif (!$visitTime) {
                                    $visitTime = '10:00';
                                }
                            @endphp
                            <input type="text" name="visit_time" id="visit_time" 
                                class="form-control @error('visit_time') is-invalid @enderror" 
                                value="{{ $visitTime }}" placeholder="чч:мм">
                            @error('visit_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Скрытое поле для отправки полной даты и времени -->
                    <input type="hidden" name="starts_at" id="starts_at">

                    <!-- Информация о расписании -->
                    <div id="schedule-info" class="mb-3"></div>

                    <div class="mb-3">
                        <label for="status_id" class="form-label">Статус</label>
                        <select name="status_id" id="status_id" class="form-select @error('status_id') is-invalid @enderror" data-url="{{ route('admin.visits.status-options') }}">
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
                            @foreach($selectedSymptoms as $symptom)
                                <option value="{{ $symptom['id'] }}" selected>{{ $symptom['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Диагнозы -->
                    <div class="mb-3">
                        <label for="diagnoses" class="form-label">Диагнозы</label>
                        <select name="diagnoses[]" id="diagnoses" class="form-select" multiple data-url="{{ route('admin.visits.diagnosis-options') }}">
                            @foreach($selectedDiagnoses as $diagnosis)
                                <option value="{{ $diagnosis['id'] }}" selected>{{ $diagnosis['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline"></span>Отмена</span>
                        </a>
                        <button type="submit" class="btn btn-success">
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
@php
    $oldSymptoms = old('symptoms');
    $oldDiagnoses = old('diagnoses');
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        
        // Переменные для старых значений
        const oldSymptoms = @json($oldSymptoms ?? []);
        const oldDiagnoses = @json($oldDiagnoses ?? []);
        
        // Инициализация TomSelect
        const clientTomSelect = new createTomSelect('#client_id', {
            placeholder: 'Выберите клиента...',
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        const petTomSelect = new createTomSelect('#pet_id', {
            placeholder: 'Выберите питомца...',
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        new createTomSelect('#schedule_id', {
            placeholder: 'Выберите расписание...',
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        new createTomSelect('#status_id', {
            placeholder: 'Выберите статус...',
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // Инициализация TomSelect для симптомов с предварительно выбранными значениями
        const symptomsSelect = new createTomSelect('#symptoms', {
            placeholder: 'Выберите симптомы...',
            create: true,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Добавляем уже выбранные значения к запросу
                const selectedValues = this.getValue();
                if (selectedValues.length > 0) {
                    url += '&selected=' + encodeURIComponent(selectedValues.join(','));
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // Восстанавливаем старые значения симптомов при ошибке валидации
        if (oldSymptoms && oldSymptoms.length > 0) {
            // Сначала очищаем все существующие опции
            symptomsSelect.clearOptions();
            
            // Создаем опции для уже выбранных симптомов
            oldSymptoms.forEach(symptomId => {
                if (typeof symptomId === 'string' && symptomId.trim() !== '') {
                    if (isNaN(symptomId)) {
                        // Кастомный симптом
                        symptomsSelect.addOption({
                            value: symptomId,
                            text: symptomId
                        });
                    } else {
                        // Симптом из словаря - нужно загрузить его данные
                        fetch(`{{ route('admin.visits.symptom-options') }}?q=&selected=${symptomId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    const symptom = data.find(s => s.value == symptomId);
                                    if (symptom) {
                                        symptomsSelect.addOption(symptom);
                                    }
                                }
                            });
                    }
                }
            });
            
            // Устанавливаем значения после загрузки опций
            setTimeout(() => {
                symptomsSelect.setValue(oldSymptoms);
            }, 500);
        } else {
            // Если нет old значений, восстанавливаем изначально выбранные
            const selectedSymptoms = @json($selectedSymptoms ?? []);
            if (selectedSymptoms && selectedSymptoms.length > 0) {
                selectedSymptoms.forEach(symptom => {
                    symptomsSelect.addOption({
                        value: symptom.id,
                        text: symptom.name
                    });
                });
                symptomsSelect.setValue(selectedSymptoms.map(s => s.id));
            }
        }
        
        // Инициализация TomSelect для диагнозов с предварительно выбранными значениями
        const diagnosesSelect = new createTomSelect('#diagnoses', {
            placeholder: 'Выберите диагнозы...',
            create: true,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Добавляем уже выбранные значения к запросу
                const selectedValues = this.getValue();
                if (selectedValues.length > 0) {
                    url += '&selected=' + encodeURIComponent(selectedValues.join(','));
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // Восстанавливаем старые значения диагнозов при ошибке валидации
        if (oldDiagnoses && oldDiagnoses.length > 0) {
            // Сначала очищаем все существующие опции
            diagnosesSelect.clearOptions();
            
            // Создаем опции для уже выбранных диагнозов
            oldDiagnoses.forEach(diagnosisId => {
                if (typeof diagnosisId === 'string' && diagnosisId.trim() !== '') {
                    if (isNaN(diagnosisId)) {
                        // Кастомный диагноз
                        diagnosesSelect.addOption({
                            value: diagnosisId,
                            text: diagnosisId
                        });
                    } else {
                        // Диагноз из словаря - нужно загрузить его данные
                        fetch(`{{ route('admin.visits.diagnosis-options') }}?q=&selected=${diagnosisId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    const diagnosis = data.find(d => d.value == diagnosisId);
                                    if (diagnosis) {
                                        diagnosesSelect.addOption(diagnosis);
                                    }
                                }
                            });
                    }
                }
            });
            
            // Устанавливаем значения после загрузки опций
            setTimeout(() => {
                diagnosesSelect.setValue(oldDiagnoses);
            }, 500);
        } else {
            // Если нет old значений, восстанавливаем изначально выбранные
            const selectedDiagnoses = @json($selectedDiagnoses ?? []);
            if (selectedDiagnoses && selectedDiagnoses.length > 0) {
                selectedDiagnoses.forEach(diagnosis => {
                    diagnosesSelect.addOption({
                        value: diagnosis.id,
                        text: diagnosis.name
                    });
                });
                diagnosesSelect.setValue(selectedDiagnoses.map(d => d.id));
            }
        }
        
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

        const visitTimeInput = document.getElementById('visit_time');
        const timeIntervalSpan = document.getElementById('time-interval');
        const scheduleSelect = document.getElementById('schedule_id');
        let selectedSchedule = null;
        let originalVisitTime = visitTimeInput.value; // Сохраняем оригинальное время

        // Инициализация таймпикера
        createDatepicker('#visit_time', {
            timepicker: true,
            onlyTimepicker: true,
            startDate: new Date(new Date().setHours(10, 0, 0, 0)),
            timeFormat: 'HH:mm',
            onSelect: function() {
                updateVisitTimeInterval();
            }
        });

        // Слушатель изменения времени
        visitTimeInput.addEventListener('change', function() {
            updateVisitTimeInterval();
        });
        
        // Слушатель ввода времени
        visitTimeInput.addEventListener('input', function() {
            updateVisitTimeInterval();
        });

        function updateVisitTimeInterval() {
            const visitTimeValue = visitTimeInput.value;
            if (!visitTimeValue || !selectedSchedule) {
                timeIntervalSpan.textContent = '';
                return;
            }

            // Парсим время
            const timeParts = visitTimeValue.split(':');
            if (timeParts.length < 2) {
                timeIntervalSpan.textContent = '';
                return;
            }

            let hour = parseInt(timeParts[0], 10);
            let minute = parseInt(timeParts[1], 10);

            if (isNaN(hour) || isNaN(minute)) {
                timeIntervalSpan.textContent = '';
                return;
            }

            // Округляем до начала получасового интервала
            let startMinute, endMinute, endHour;
            if (minute >= 30) {
                startMinute = 30;
                endMinute = 0;
                endHour = hour + 1;
            } else {
                startMinute = 0;
                endMinute = 30;
                endHour = hour;
            }
            
            const startTime = `${String(hour).padStart(2, '0')}:${String(startMinute).padStart(2, '0')}`;
            const endTime = `${String(endHour).padStart(2, '0')}:${String(endMinute).padStart(2, '0')}`;
            
            // Форматируем дату из расписания
            const scheduleDate = new Date(selectedSchedule.shift_starts_at);
            const formattedDate = scheduleDate.toLocaleDateString('ru-RU');
            
            timeIntervalSpan.textContent = `(${formattedDate} с ${startTime} до ${endTime})`;
        }

        // Функция для получения доступного времени
        function getAvailableTime(scheduleId) {
            if (!scheduleId) return;
            
            // Очищаем предыдущее расписание
            selectedSchedule = null;
            const scheduleInfo = document.getElementById('schedule-info');
            if (scheduleInfo) {
                scheduleInfo.innerHTML = '';
            }
            
            updateVisitTimeInterval();
            
            fetch(`{{ route('admin.visits.available-time') }}?schedule_id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    // Сохраняем информацию о расписании
                    selectedSchedule = data.schedule;
                    
                    // Показываем информацию о расписании
                    if (scheduleInfo) {
                        scheduleInfo.innerHTML = `
                            <div class="alert alert-info">
                                <strong>Врач:</strong> ${data.schedule.veterinarian}<br>
                                <strong>Смена:</strong> ${data.schedule.shift_start} - ${data.schedule.shift_end}<br>
                                <strong>Доступное время:</strong> ${data.available_times.length > 0 ? 
                                    data.available_times.map(t => t.time).join(', ') : 'Нет свободного времени'}
                            </div>
                        `;
                    }

                    // Обновляем интервал
                    updateVisitTimeInterval();
                })
                .catch(error => {
                    console.error('Ошибка при получении доступного времени:', error);
                });
        }
        
        // Слушатель изменения расписания
        scheduleSelect.addEventListener('change', function() {
            if (this.value) {
                // При смене расписания сохраняем текущее время
                const currentTime = visitTimeInput.value;
                getAvailableTime(this.value);
                // Восстанавливаем время после загрузки расписания
                setTimeout(() => {
                    if (currentTime) {
                        visitTimeInput.value = currentTime;
                        updateVisitTimeInterval();
                    }
                }, 100);
            } else {
                // Очищаем информацию о расписании
                selectedSchedule = null;
                const scheduleInfo = document.getElementById('schedule-info');
                if (scheduleInfo) {
                    scheduleInfo.innerHTML = '';
                }
                updateVisitTimeInterval();
            }
        });
        
        // Обновляем интервал при загрузке
        updateVisitTimeInterval();
        
        // Инициализируем расписание при загрузке, если оно уже выбрано
        const initialScheduleId = scheduleSelect.value;
        if (initialScheduleId) {
            // Загружаем информацию о расписании, но не меняем время
            fetch(`{{ route('admin.visits.available-time') }}?schedule_id=${initialScheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }
                    
                    // Сохраняем информацию о расписании
                    selectedSchedule = data.schedule;
                    
                    // Показываем информацию о расписании
                    const scheduleInfo = document.getElementById('schedule-info');
                    if (scheduleInfo) {
                        scheduleInfo.innerHTML = `
                            <div class="alert alert-info">
                                <strong>Врач:</strong> ${data.schedule.veterinarian}<br>
                                <strong>Смена:</strong> ${data.schedule.shift_start} - ${data.schedule.shift_end}<br>
                                <strong>Доступное время:</strong> ${data.available_times.length > 0 ? 
                                    data.available_times.map(t => t.time).join(', ') : 'Нет свободного времени'}
                            </div>
                        `;
                    }
                    
                    // Обновляем интервал с текущим временем
                    updateVisitTimeInterval();
                })
                .catch(error => {
                    console.error('Ошибка при получении доступного времени:', error);
                });
        } else {
            // Если расписание не выбрано, очищаем информацию
            selectedSchedule = null;
            const scheduleInfo = document.getElementById('schedule-info');
            if (scheduleInfo) {
                scheduleInfo.innerHTML = '';
            }
        }
        
        // Устанавливаем время по умолчанию, если оно не выбрано
        if (!visitTimeInput.value) {
            visitTimeInput.value = '10:00';
        }
        
        // Добавляем обработчик отправки формы
        document.querySelector('form').addEventListener('submit', function(e) {
            // Объединяем дату из расписания с временем приёма
            if (selectedSchedule && visitTimeInput.value) {
                const scheduleDate = new Date(selectedSchedule.shift_starts_at);
                const visitTime = visitTimeInput.value;
                
                // Парсим время и округляем до начала получасового интервала
                const timeParts = visitTime.split(':');
                if (timeParts.length === 2) {
                    const hour = parseInt(timeParts[0], 10);
                    const minute = parseInt(timeParts[1], 10);
                    
                    // Округляем до начала получасового интервала
                    let roundedMinute;
                    if (minute >= 30) {
                        roundedMinute = 30;
                    } else {
                        roundedMinute = 0;
                    }
                    
                    // Форматируем округленное время
                    const roundedTime = `${String(hour).padStart(2, '0')}:${String(roundedMinute).padStart(2, '0')}`;
                    
                    // Форматируем дату в Y-m-d формат
                    const year = scheduleDate.getFullYear();
                    const month = String(scheduleDate.getMonth() + 1).padStart(2, '0');
                    const day = String(scheduleDate.getDate()).padStart(2, '0');
                    
                    // Создаем полную дату и время с округленным временем
                    const fullDateTime = `${year}-${month}-${day} ${roundedTime}`;
                    document.getElementById('starts_at').value = fullDateTime;
                }
            }
            
            // Добавляем значения TomSelect в форму перед отправкой
            const symptomsValues = symptomsSelect.getValue();
            const diagnosesValues = diagnosesSelect.getValue();
            
            // Очищаем оригинальные select поля
            const symptomsSelectElement = document.getElementById('symptoms');
            const diagnosesSelectElement = document.getElementById('diagnoses');
            
            // Удаляем все option из select полей
            while (symptomsSelectElement.firstChild) {
                symptomsSelectElement.removeChild(symptomsSelectElement.firstChild);
            }
            while (diagnosesSelectElement.firstChild) {
                diagnosesSelectElement.removeChild(diagnosesSelectElement.firstChild);
            }
            
            // Добавляем новые значения как option в select поля
            symptomsValues.forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.selected = true;
                symptomsSelectElement.appendChild(option);
            });
            
            diagnosesValues.forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.selected = true;
                diagnosesSelectElement.appendChild(option);
            });
        });
    });
</script>
@endpush 