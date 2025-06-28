@extends('layouts.admin')

@section('title', 'Создать расписание на неделю')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать расписание на неделю</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-calendar-day"></i> Расписание на день
            </a>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> К списку расписаний
            </a>
    </div>
</div>

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $conflicts = session('conflicts', []);
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.schedules.store-week') }}" method="POST" id="weekScheduleForm">
                    @csrf

                    @if($errors->has('schedule_conflicts'))
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i>
                                Обнаружены конфликты в расписании:
                            </h6>
                            <ul class="mb-0">
                                @foreach($errors->get('schedule_conflicts') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($errors->has('general'))
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ $errors->first('general') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="veterinarian_id" class="form-label">Ветеринар <span class="text-danger">*</span></label>
                            <select name="veterinarian_id" id="veterinarian_id" class="form-select @error('veterinarian_id') is-invalid @enderror">
                                <option value="">Выберите ветеринара</option>
                                @foreach($veterinarians as $veterinarian)
                                    <option value="{{ $veterinarian->id }}" @if(old('veterinarian_id') == $veterinarian->id) selected @endif>
                                        {{ $veterinarian->name }}
                                        @if($veterinarian->specialization)
                                            <small>({{ $veterinarian->specialization }})</small>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('veterinarian_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Филиал <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">Выберите филиал</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @if(old('branch_id') == $branch->id) selected @endif>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="week_start" class="form-label">Начало недели <span class="text-danger">*</span></label>
                            <input type="text" name="week_start" id="week_start" 
                                class="form-control @error('week_start') is-invalid @enderror" 
                                value="{{ old('week_start') }}" readonly>
                            <div class="form-text" id="weekRange"></div>
                            @error('week_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-calendar-week"></i>
                                Дни недели
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php
                                    $days = [
                                        'monday' => 'Понедельник',
                                        'tuesday' => 'Вторник',
                                        'wednesday' => 'Среда',
                                        'thursday' => 'Четверг',
                                        'friday' => 'Пятница',
                                        'saturday' => 'Суббота',
                                        'sunday' => 'Воскресенье'
                                    ];
                                    $defaultDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                    $oldDays = old('days', []);
                                @endphp

                                @foreach($days as $value => $label)
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input day-checkbox" 
                                                name="days[]" value="{{ $value }}" 
                                                id="day_{{ $value }}" 
                                                @if(in_array($value, $oldDays) || (empty($oldDays) && in_array($value, $defaultDays))) checked @endif>
                                            <label class="form-check-label" for="day_{{ $value }}">{{ $label }}</label>
                                        </div>
                                        <div class="day-time-inputs mt-2" id="time_{{ $value }}" style="display: none;">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="text" class="form-control time-input @error("start_time_{$value}") is-invalid @enderror" 
                                                        name="start_time_{{ $value }}" 
                                                        placeholder="Начало" 
                                                        data-day="{{ $value }}"
                                                        value="{{ old("start_time_{$value}", '09:00') }}">
                                                    @error("start_time_{$value}")
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6">
                                                    <input type="text" class="form-control time-input @error("end_time_{$value}") is-invalid @enderror" 
                                                        name="end_time_{{ $value }}" 
                                                        placeholder="Окончание" 
                                                        data-day="{{ $value }}"
                                                        value="{{ old("end_time_{$value}", '18:00') }}">
                                                    @error("end_time_{$value}")
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @if(isset($conflicts[$value]))
                                                <div class="text-warning small mt-1">
                                                    @foreach($conflicts[$value]['errors'] as $err)
                                                        {{ $err }}<br>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-eye"></i>
                                Предпросмотр расписания
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="schedulePreview" class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>День</th>
                                            <th>Время</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Здесь будет предпросмотр -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Создать расписание
                        </button>
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i>
                    Информация
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Обязательные поля</h6>
                    <ul class="mb-0">
                        <li>Ветерина</li>
                        <li>Филиал</li>
                        <li>Начало недели</li>
                        <li>Минимум один день недели</li>
                        <li>Время начала и окончания для каждого выбранного дня</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6 class="alert-heading">Важно</h6>
                    <p class="mb-0">Если для выбранного дня уже существует расписание, оно не будет перезаписано.</p>
                </div>

                <div class="alert alert-secondary">
                    <h6 class="alert-heading">Справка</h6>
                    <p class="mb-0">Выберите начало недели, отметьте нужные дни и укажите время работы для каждого дня. Время окончания должно быть позже времени начала.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Инициализация TomSelect
    new createTomSelect('#veterinarian_id', {
        placeholder: 'Выберите ветеринара...',
    });
    
    new createTomSelect('#branch_id', {
        placeholder: 'Выберите филиал...',
    });

    // Инициализация Air Datepicker для начала недели
    createDatepicker('#week_start', {
        onSelect: function({date}) {
            if (date) {
                // Устанавливаем дату на начало недели (понедельник)
                const startOfWeek = new Date(date);
                const dayOfWeek = startOfWeek.getDay();
                const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Если воскресенье, отступаем на 6 дней назад
                startOfWeek.setDate(startOfWeek.getDate() + diff);
                
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(endOfWeek.getDate() + 6);
                
                const formatDate = (d) => {
                    return d.toLocaleDateString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                };
                
                document.getElementById('weekRange').textContent = 
                    `Период: ${formatDate(startOfWeek)} - ${formatDate(endOfWeek)}`;
                
                updateSchedulePreview();
            }
        }
    });

    // Инициализация Air Datepicker для полей времени
    const timePickers = new Map();

    function initTimePicker(input) {
        const day = input.dataset.day;
        const isStart = input.name.includes('start');
        const pickerKey = `${day}_${isStart ? 'start' : 'end'}`;

        if (timePickers.has(pickerKey)) {
            timePickers.get(pickerKey).destroy();
        }

        const picker = createDatepicker(input, {
            timepicker: true,
            onlyTimepicker: true,
            timeFormat: 'HH:mm',
            startDate: new Date(new Date().setHours(isStart ? 9 : 18, 0, 0, 0)),
            onSelect: function({date, datepicker}) {
                // Обновляем предпросмотр при изменении времени
                updateSchedulePreview();
            }
        });

        timePickers.set(pickerKey, picker);
    }

    // Инициализация всех полей времени
    document.querySelectorAll('.time-input').forEach(input => {
        initTimePicker(input);
    });

    // Обработка чекбоксов дней
    document.querySelectorAll('.day-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const timeInputs = document.getElementById(`time_${this.value}`);
            timeInputs.style.display = this.checked ? 'block' : 'none';
            
            // Если день выбран, устанавливаем время по умолчанию
            if (this.checked) {
                const startInput = timeInputs.querySelector(`input[name="start_time_${this.value}"]`);
                const endInput = timeInputs.querySelector(`input[name="end_time_${this.value}"]`);
                
                if (!startInput.value) {
                    startInput.value = '09:00';
                    const picker = timePickers.get(`${this.value}_start`);
                    if (picker) {
                        picker.selectDate(new Date('2000-01-01T09:00'));
                    }
                }
                
                if (!endInput.value) {
                    endInput.value = '18:00';
                    const picker = timePickers.get(`${this.value}_end`);
                    if (picker) {
                        picker.selectDate(new Date('2000-01-01T18:00'));
                    }
                }
            }
            
            updateSchedulePreview();
        });
    });

    // Функция обновления предпросмотра расписания
    function updateSchedulePreview() {
        const preview = document.getElementById('schedulePreview').querySelector('tbody');
        preview.innerHTML = '';
        
        const weekStart = document.getElementById('week_start').value;
        if (!weekStart) return;

        const days = {
            'monday': 'Понедельник',
            'tuesday': 'Вторник',
            'wednesday': 'Среда',
            'thursday': 'Четверг',
            'friday': 'Пятница',
            'saturday': 'Суббота',
            'sunday': 'Воскресенье'
        };

        const startDate = new Date(weekStart.split('.').reverse().join('-'));
        
        document.querySelectorAll('.day-checkbox:checked').forEach(checkbox => {
            const day = checkbox.value;
            const dayIndex = Object.keys(days).indexOf(day);
            const date = new Date(startDate);
            date.setDate(date.getDate() + dayIndex);
            
            const startTime = document.querySelector(`input[name="start_time_${day}"]`).value;
            const endTime = document.querySelector(`input[name="end_time_${day}"]`).value;
            
            if (startTime && endTime) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <strong>${days[day]}</strong><br>
                        <small class="text-muted">${date.toLocaleDateString('ru-RU', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        })}</small>
                    </td>
                    <td>
                        <span class="badge bg-primary">${startTime}</span>
                        <span class="text-muted">-</span>
                        <span class="badge bg-secondary">${endTime}</span>
                    </td>
                `;
                preview.appendChild(row);
            }
        });
    }

    // Обработка отправки формы
    document.getElementById('weekScheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Проверяем, что выбраны дни
        const selectedDays = document.querySelectorAll('.day-checkbox:checked');
        if (selectedDays.length === 0) {
            alert('Выберите хотя бы один день недели');
            return;
        }

        // Проверяем время для каждого выбранного дня
        let isValid = true;

        selectedDays.forEach(checkbox => {
            const day = checkbox.value;
            const startTime = document.querySelector(`input[name="start_time_${day}"]`).value;
            const endTime = document.querySelector(`input[name="end_time_${day}"]`).value;
            
            if (!startTime || !endTime) {
                alert(`Укажите время начала и окончания для ${days[day].toLowerCase()}`);
                isValid = false;
                return;
            }
            
            if (startTime >= endTime) {
                alert(`Время окончания должно быть позже времени начала для ${days[day].toLowerCase()}`);
                isValid = false;
                return;
            }
        });

        if (!isValid) return;

        // Отправляем форму
        this.submit();
    });

    // Инициализация отображения полей времени для выбранных дней
    document.querySelectorAll('.day-checkbox:checked').forEach(checkbox => {
        const timeInputs = document.getElementById(`time_${checkbox.value}`);
        timeInputs.style.display = 'block';
        
        // Устанавливаем время по умолчанию, если оно не задано
        const startInput = timeInputs.querySelector(`input[name="start_time_${checkbox.value}"]`);
        const endInput = timeInputs.querySelector(`input[name="end_time_${checkbox.value}"]`);
        
        if (!startInput.value) {
            startInput.value = '09:00';
            const picker = timePickers.get(`${checkbox.value}_start`);
            if (picker) {
                picker.selectDate(new Date('2000-01-01T09:00'));
            }
        }
        
        if (!endInput.value) {
            endInput.value = '18:00';
            const picker = timePickers.get(`${checkbox.value}_end`);
            if (picker) {
                picker.selectDate(new Date('2000-01-01T18:00'));
            }
        }
    });

    // Первоначальное обновление предпросмотра
    updateSchedulePreview();
});
</script>
@endpush 