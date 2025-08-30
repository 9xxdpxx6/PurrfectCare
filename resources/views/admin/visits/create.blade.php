@extends('layouts.admin')

@section('title', 'Добавить приём')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить приём</h1>
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
                <form action="{{ route('admin.visits.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_id" class="form-label">Клиент</label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" data-url="{{ route('admin.visits.client-options') }}">
                                @if(old('client_id') || $selectedClientId)
                                    @php
                                        $clientId = old('client_id', $selectedClientId);
                                        $selectedClient = \App\Models\User::find($clientId);
                                    @endphp
                                    @if($selectedClient)
                                        <option value="{{ $selectedClient->id }}" selected>{{ $selectedClient->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pet_id" class="form-label">Питомец <small class="text-muted">(необязательно)</small></label>
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" data-url="{{ route('admin.visits.pet-options') }}">
                                @if(old('pet_id') || $selectedPetId)
                                    @php
                                        $petId = old('pet_id', $selectedPetId);
                                        $selectedPet = \App\Models\Pet::with('client')->find($petId);
                                    @endphp
                                    @if($selectedPet)
                                        <option value="{{ $selectedPet->id }}" selected data-client="{{ $selectedPet->client_id }}">{{ $selectedPet->name }} ({{ $selectedPet->client->name ?? 'Без владельца' }})</option>
                                    @endif
                                @endif
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
                                @if(old('schedule_id') || $selectedScheduleId)
                                    @php
                                        $scheduleId = old('schedule_id', $selectedScheduleId);
                                        $selectedSchedule = \App\Models\Schedule::with('veterinarian')->find($scheduleId);
                                    @endphp
                                    @if($selectedSchedule)
                                        <option value="{{ $selectedSchedule->id }}" selected>
                                            @if($selectedSchedule->veterinarian)
                                                {{ $selectedSchedule->veterinarian->name }} - 
                                            @endif
                                            {{ \Carbon\Carbon::parse($selectedSchedule->shift_starts_at)->format('d.m.Y H:i') }} - 
                                            {{ \Carbon\Carbon::parse($selectedSchedule->shift_ends_at)->format('H:i') }}
                                        </option>
                                    @endif
                                @endif
                            </select>
                            @error('schedule_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="visit_time" class="form-label">
                                Время приёма <span id="time-interval" class="text-muted"></span>
                            </label>
                            <input type="text" name="visit_time" id="visit_time" 
                                class="form-control @error('visit_time') is-invalid @enderror @error('starts_at') is-invalid @enderror" 
                                value="{{ old('visit_time', '10:00') }}" placeholder="чч:мм">
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
                            @if(old('status_id') || $default_status_id)
                                @php
                                    $statusId = old('status_id', $default_status_id);
                                    $selectedStatus = \App\Models\Status::find($statusId);
                                @endphp
                                @if($selectedStatus)
                                    <option value="{{ $selectedStatus->id }}" selected>{{ $selectedStatus->name }}</option>
                                @endif
                            @endif
                        </select>
                        @error('status_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="complaints" class="form-label">Жалобы</label>
                        <textarea name="complaints" id="complaints" rows="3" 
                            class="form-control @error('complaints') is-invalid @enderror" 
                            placeholder="Опишите жалобы клиента...">{{ old('complaints') }}</textarea>
                        @error('complaints')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Заметки</label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="form-control @error('notes') is-invalid @enderror" 
                            placeholder="Дополнительные заметки...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Симптомы -->
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">Симптомы</label>
                        <select name="symptoms[]" id="symptoms" class="form-select @error('symptoms') is-invalid @enderror @error('symptoms.*') is-invalid @enderror" multiple data-url="{{ route('admin.visits.symptom-options') }}">
                        </select>
                        <div class="form-text">Удерживайте Ctrl для выбора нескольких симптомов</div>
                        @error('symptoms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('symptoms.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Диагнозы -->
                    <div class="mb-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-8 col-xl-9">
                                <h6 class="mb-0">Диагнозы</h6>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-3 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="addDiagnosisItem()">
                                    <i class="bi bi-plus-lg"></i> Добавить диагноз
                                </button>
                            </div>
                        </div>
                        <div id="diagnosisItems">
                            <!-- Карточки диагнозов будут добавляться сюда -->
                        </div>
                        @error('diagnoses')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('diagnoses.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.visits.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Шаблон для карточки диагноза -->
<template id="diagnosisItemTemplate">
    <div class="diagnosis-item border rounded p-3 mb-3" data-diagnosis-index="">
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <label class="form-label">Диагноз</label>
                <select name="diagnoses[INDEX][diagnosis_id]" class="form-select diagnosis-select" data-url="{{ route('admin.visits.diagnosis-options') }}">
                </select>
                <input type="hidden" name="diagnoses[INDEX][id]" value="">
            </div>
            
            <div class="col-lg-4">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeDiagnosisItem(this)">
                    <i class="bi bi-trash"></i> Удалить
                </button>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <label class="form-label">План лечения</label>
                <textarea name="diagnoses[INDEX][treatment_plan]" class="form-control" rows="3" placeholder="Опишите план лечения для данного диагноза..."></textarea>
            </div>
        </div>
    </div>
</template>

@push('scripts')
@php
    $oldSymptoms = old('symptoms');
    $oldDiagnoses = old('diagnoses');
    $oldPetId = old('pet_id');
    $oldClientId = old('client_id');
@endphp
<script>
    // Глобальные переменные
    let diagnosisIndex = 0;
    
    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        
        // Получаем предустановленные значения
        const selectedClientId = '{{ $oldClientId ?? $selectedClientId ?? "" }}';
        const selectedPetId = '{{ $oldPetId ?? $selectedPetId ?? "" }}';
        const selectedScheduleId = '{{ old("schedule_id") ?? $selectedScheduleId ?? "" }}';
        
        // Переменные для старых значений
        const oldSymptoms = {!! json_encode($oldSymptoms ?? []) !!};
        const oldDiagnoses = {!! json_encode($oldDiagnoses ?? []) !!};
        
        // Инициализация TomSelect
        const clientTomSelect = new createTomSelect('#client_id', {
            placeholder: 'Выберите клиента...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
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
        
        const petTomSelect = new createTomSelect('#pet_id', {
            placeholder: 'Выберите питомца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: false,
            load: function(query, callback) {
                const clientId = clientTomSelect.getValue();
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                
                // Если выбран клиент, фильтруем по нему
                if (clientId) {
                    url += '&client_id=' + clientId;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback([]));
            },
            onItemAdd: function(value) {
                // При выборе питомца автоматически заполняем клиента
                const selectedOption = this.options[value];
                if (selectedOption && selectedOption.dataset && selectedOption.dataset.client) {
                    const clientId = selectedOption.dataset.client;
                    clientTomSelect.setValue(clientId);
                }
                
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        // Флаги инициализации для корректной первичной установки значений
        clientTomSelect.isInitializing = true;
        petTomSelect.isInitializing = true;
        
        // Устанавливаем предустановленные значения
        setTimeout(() => {
            if (selectedClientId) {
                clientTomSelect.setValue(selectedClientId);
                // Загружаем питомцев для выбранного клиента
                fetch(`{{ route('admin.visits.pet-options') }}?client_id=${selectedClientId}&filter=false`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(option => {
                            petTomSelect.addOption(option);
                        });
                        // Устанавливаем питомца после загрузки опций
                        if (selectedPetId) {
                            petTomSelect.setValue(selectedPetId);
                        }
                    })
                    .catch(() => {
                        console.error('Ошибка загрузки питомцев');
                    });
            } else if (selectedPetId) {
                petTomSelect.setValue(selectedPetId);
            }
            if (selectedScheduleId) {
                scheduleTomSelect.setValue(selectedScheduleId);
                getAvailableTime(selectedScheduleId);
            }

            // Завершаем фазу инициализации
            clientTomSelect.isInitializing = false;
            petTomSelect.isInitializing = false;
        }, 100);
        
        // Обработчик изменения клиента
        clientTomSelect.on('change', function(value) {
            // Пропускаем очистку во время первоначальной инициализации
            if (this.isInitializing) return;
            // Очищаем выбранного питомца при смене клиента
            petTomSelect.clear();
            petTomSelect.clearOptions();
            
            if (value) {
                // Загружаем питомцев для выбранного клиента
                fetch(`{{ route('admin.visits.pet-options') }}?client_id=${value}&filter=false`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(option => {
                            petTomSelect.addOption(option);
                        });
                    })
                    .catch(() => {
                        console.error('Ошибка загрузки питомцев');
                    });
            }
        });
        
        const scheduleTomSelect = new createTomSelect('#schedule_id', {
            placeholder: 'Выберите расписание...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
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
        
        new createTomSelect('#status_id', {
            placeholder: 'Выберите статус...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
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
            setTimeout(() => {
                // Загружаем выбранные симптомы
                const symptomsUrl = document.getElementById('symptoms').dataset.url + '?selected=' + oldSymptoms.join(',');
                fetch(symptomsUrl)
                    .then(response => response.json())
                    .then(options => {
                        options.forEach(option => {
                            symptomsSelect.addOption(option);
                        });
                        symptomsSelect.setValue(oldSymptoms);
                    })
                    .catch(error => {
                        console.error('Error loading selected symptoms:', error);
                    });
            }, 200);
        }
        
        // Инициализация диагнозов уже выполнена глобально
        
        // Восстанавливаем старые значения диагнозов при ошибке валидации
        if (oldDiagnoses && Array.isArray(oldDiagnoses) && oldDiagnoses.length > 0) {
            oldDiagnoses.forEach((diagnosis, index) => {
                addDiagnosisItem();
                const diagnosisItems = document.getElementById('diagnosisItems');
                const lastDiagnosisItem = diagnosisItems.lastElementChild;
                
                if (lastDiagnosisItem) {
                    const treatmentPlanTextarea = lastDiagnosisItem.querySelector('textarea[name*="treatment_plan"]');
                    const idInput = lastDiagnosisItem.querySelector('input[name*="[id]"]');
                    
                    if (diagnosis.id) {
                        idInput.value = diagnosis.id;
                    }
                    
                    if (diagnosis.treatment_plan && treatmentPlanTextarea) {
                        treatmentPlanTextarea.value = diagnosis.treatment_plan;
                    }
                    
                    // Загружаем и устанавливаем выбранный диагноз
                    const diagnosisId = diagnosis.diagnosis_id || diagnosis.id;
                    if (diagnosisId) {
                        const diagnosisSelect = lastDiagnosisItem.querySelector('.diagnosis-select');
                        setTimeout(() => {
                            if (diagnosisSelect.tomselect) {
                                const diagnosisUrl = diagnosisSelect.dataset.url + '?selected=' + diagnosisId;
                                fetch(diagnosisUrl)
                                    .then(response => response.json())
                                    .then(options => {
                                        options.forEach(option => {
                                            diagnosisSelect.tomselect.addOption(option);
                                        });
                                        diagnosisSelect.tomselect.setValue(diagnosisId);
                                    });
                            }
                        }, 300);
                    }
                }
            });
        }

        const visitTimeInput = document.getElementById('visit_time');
        const timeIntervalSpan = document.getElementById('time-interval');
        const scheduleSelect = document.getElementById('schedule_id');
        let selectedSchedule = null;

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
                    const scheduleInfo = document.getElementById('schedule-info');
                    if (scheduleInfo) {
                        const hasAvailableTime = data.available_times.length > 0;
                        const alertClass = hasAvailableTime ? 'alert-info' : 'alert-danger';
                        const timeText = hasAvailableTime ? 
                            data.available_times.map(t => t.time).join(', ') : 
                            'Нет свободного времени';
                        
                        scheduleInfo.innerHTML = `
                            <div class="alert ${alertClass}">
                                <strong>Врач:</strong> ${data.schedule.veterinarian}<br>
                                <strong>Смена:</strong> ${data.schedule.shift_start} - ${data.schedule.shift_end}<br>
                                <strong>Доступное время:</strong> ${timeText}
                                ${!hasAvailableTime ? '<br><br><i class="bi bi-exclamation-triangle"></i> <strong>Все слоты заняты! Выберите другое расписание.</strong>' : ''}
                            </div>
                        `;
                    }

                    // Устанавливаем ближайшее доступное время
                    if (data.next_available_time) {
                        const timeOnly = data.next_available_time.split(' ')[1]; // Берем только время
                        visitTimeInput.value = timeOnly;
                        updateVisitTimeInterval(); // Обновляем отображение интервала
                    } else {
                        // Если нет доступного времени, очищаем поле времени
                        visitTimeInput.value = '';
                        updateVisitTimeInterval();
                    }
                    
                    // Показываем предупреждение если нет доступного времени
                    if (data.available_times.length === 0) {
                        const scheduleInfo = document.getElementById('schedule-info');
                        if (scheduleInfo) {
                            scheduleInfo.innerHTML = `
                                <div class="alert alert-warning">
                                    <strong>Врач:</strong> ${data.schedule.veterinarian}<br>
                                    <strong>Смена:</strong> ${data.schedule.shift_start} - ${data.schedule.shift_end}<br>
                                    <strong>Доступное время:</strong> Нет свободного времени<br>
                                    <br><i class="bi bi-exclamation-triangle"></i> <strong>Все слоты заняты! Выберите другое расписание.</strong>
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка при получении доступного времени:', error);
                });
        }
        
        // Слушатель изменения расписания
        scheduleSelect.addEventListener('change', function() {
            if (this.value) {
                getAvailableTime(this.value);
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
            getAvailableTime(initialScheduleId);
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
                    
                    // Форматируем дату в d.m.Y формат (как ожидает сервер)
                    const year = scheduleDate.getFullYear();
                    const month = String(scheduleDate.getMonth() + 1).padStart(2, '0');
                    const day = String(scheduleDate.getDate()).padStart(2, '0');
                    
                    // Создаем полную дату и время с округленным временем в формате d.m.Y H:i
                    const fullDateTime = `${day}.${month}.${year} ${roundedTime}`;
                    document.getElementById('starts_at').value = fullDateTime;
                }
            } else {
                // Показываем ошибку пользователю
                e.preventDefault();
                alert('Ошибка: не удалось определить дату и время приёма. Проверьте, что выбрано расписание и время.');
                return;
            }
            
            // Проверяем, что поле starts_at заполнено
            const startsAtValue = document.getElementById('starts_at').value;
            
            if (!startsAtValue) {
                e.preventDefault();
                alert('Ошибка: не удалось определить дату и время приёма. Проверьте, что выбрано расписание и время.');
                return;
            }
            
            // Добавляем значения TomSelect в форму перед отправкой
            const symptomsValues = symptomsSelect.getValue();
            
            // Очищаем оригинальные select поля
            const symptomsSelectElement = document.getElementById('symptoms');
            
            // Удаляем все option из select полей
            while (symptomsSelectElement.firstChild) {
                symptomsSelectElement.removeChild(symptomsSelectElement.firstChild);
            }
            
            // Добавляем новые значения как option в select поля
            symptomsValues.forEach(value => {
                const option = document.createElement('option');
                option.value = value;
                option.selected = true;
                symptomsSelectElement.appendChild(option);
            });
        });

    });
    
    // Функции для работы с диагнозами
    function addDiagnosisItem() {
        const template = document.getElementById('diagnosisItemTemplate');
        const container = document.getElementById('diagnosisItems');
        const clone = template.content.cloneNode(true);
        
        // Обновляем индексы
        const diagnosisDiv = clone.querySelector('.diagnosis-item');
        diagnosisDiv.setAttribute('data-diagnosis-index', diagnosisIndex);
        
        const selects = clone.querySelectorAll('select');
        const inputs = clone.querySelectorAll('input');
        const textareas = clone.querySelectorAll('textarea');
        
        selects.forEach(select => {
            select.name = select.name.replace('INDEX', diagnosisIndex);
        });
        
        inputs.forEach(input => {
            input.name = input.name.replace('INDEX', diagnosisIndex);
        });
        
        textareas.forEach(textarea => {
            textarea.name = textarea.name.replace('INDEX', diagnosisIndex);
        });
        
        container.appendChild(clone);
        
        // Инициализируем TomSelect для нового диагноза
        const diagnosisSelect = container.querySelector(`[data-diagnosis-index="${diagnosisIndex}"] .diagnosis-select`);
        initDiagnosisTomSelect(diagnosisSelect);
        
        diagnosisIndex++;
    }
    
    function removeDiagnosisItem(button) {
        const diagnosisDiv = button.closest('.diagnosis-item');
        diagnosisDiv.remove();
    }
    
    function initDiagnosisTomSelect(select) {
        new createTomSelect(select, {
            placeholder: 'Выберите диагноз...',
            create: true,
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                
                // Добавляем уже выбранные значения к запросу
                const selectedValues = getAllSelectedDiagnosisValues();
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
    }
    
    function getAllSelectedDiagnosisValues() {
        const diagnosisSelects = document.querySelectorAll('.diagnosis-select');
        const values = [];
        diagnosisSelects.forEach(select => {
            if (select.tomselect && select.tomselect.getValue()) {
                values.push(select.tomselect.getValue());
            }
        });
        return values;
    }
</script>
@endpush 