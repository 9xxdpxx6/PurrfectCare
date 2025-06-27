@extends('layouts.admin')

@section('title', 'Создать расписание')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать расписание</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.schedules.create-week') }}" class="btn btn-success">
                <i class="bi bi-calendar-week"></i> <span class="d-none d-lg-inline">Расписание на неделю</span>
            </a>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> К списку расписаний
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.schedules.store') }}" method="POST">
                    @csrf

                    @if($errors->has('schedule_conflicts'))
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Обнаружены конфликты в расписании:</h6>
                            <ul class="mb-0">
                                @foreach($errors->get('schedule_conflicts') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="veterinarian_id" class="form-label">Ветеринар <span class="text-danger">*</span></label>
                            <select name="veterinarian_id" id="veterinarian_id" class="form-select @error('veterinarian_id') is-invalid @enderror" required>
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

                        <div class="col-md-6 mb-3">
                            <label for="branch_id" class="form-label">Филиал <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
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

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="shift_date" class="form-label">Дата смены <span class="text-danger">*</span></label>
                            @php
                                $shiftDate = old('shift_date');
                                if (!$shiftDate && old('shift_starts_at')) {
                                    try {
                                        $shiftDate = \Carbon\Carbon::parse(old('shift_starts_at'))->format('d.m.Y');
                                    } catch (\Exception $e) {
                                        $shiftDate = '';
                                    }
                                }
                            @endphp
                            <input type="text" name="shift_date" id="shift_date" 
                                class="form-control @error('shift_starts_at') is-invalid @enderror" 
                                value="{{ $shiftDate }}" readonly required placeholder="дд.мм.гггг">
                            @error('shift_starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Время начала <span class="text-danger">*</span></label>
                            @php
                                $startTime = old('start_time');
                                if (!$startTime && old('shift_starts_at')) {
                                    try {
                                        $startTime = \Carbon\Carbon::parse(old('shift_starts_at'))->format('H:i');
                                    } catch (\Exception $e) {
                                        $startTime = '09:00';
                                    }
                                } elseif (!$startTime) {
                                    $startTime = '09:00';
                                }
                            @endphp
                            <input type="text" name="start_time" id="start_time" 
                                class="form-control @error('shift_starts_at') is-invalid @enderror" 
                                value="{{ $startTime }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">Время окончания <span class="text-danger">*</span></label>
                            @php
                                $endTime = old('end_time');
                                if (!$endTime && old('shift_ends_at')) {
                                    try {
                                        $endTime = \Carbon\Carbon::parse(old('shift_ends_at'))->format('H:i');
                                    } catch (\Exception $e) {
                                        $endTime = '18:00';
                                    }
                                } elseif (!$endTime) {
                                    $endTime = '18:00';
                                }
                            @endphp
                            <input type="text" name="end_time" id="end_time" 
                                class="form-control @error('shift_ends_at') is-invalid @enderror" 
                                value="{{ $endTime }}" required>
                            @error('shift_ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Скрытые поля для отправки в нужном формате -->
                        <input type="hidden" name="shift_starts_at" id="shift_starts_at">
                        <input type="hidden" name="shift_ends_at" id="shift_ends_at">
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
                <h6 class="card-title mb-0">Информация</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Обязательные поля</h6>
                    <ul class="mb-0">
                        <li>Ветеринар</li>
                        <li>Филиал</li>
                        <li>Начало смены</li>
                        <li>Окончание смены</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Быстрое создание</h6>
                    <p class="mb-0">Для создания расписания сразу на несколько дней недели используйте кнопку "Расписание на неделю".</p>
                    <hr>
                    <a href="{{ route('admin.schedules.create-week') }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-calendar-week"></i> Расписание на неделю
                    </a>
                </div>

                <div class="alert alert-secondary">
                    <h6 class="alert-heading">Справка</h6>
                    <p class="mb-0">Время окончания смены должно быть позже времени начала. Расписание создается для одного дня.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new createTomSelect('#veterinarian_id', {
            placeholder: 'Выберите ветеринара...',
        });
        
        new createTomSelect('#branch_id', {
            placeholder: 'Выберите филиал...',
        });

        // Air Datepicker
        createDatepicker('#shift_date');
        createDatepicker('#start_time', {
            timepicker: true,
            onlyTimepicker: true,
            startDate: new Date(new Date().setHours(9, 0, 0, 0)), 
            timeFormat: 'HH:mm'
        });
        createDatepicker('#end_time', {
            timepicker: true,
            onlyTimepicker: true,
            startDate: new Date(new Date().setHours(18, 0, 0, 0)), 
            timeFormat: 'HH:mm'
        });

        // Автоматическое обновление времени окончания смены
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');

        startTimeInput.addEventListener('change', function() {
            if (this.value && !endTimeInput.value) {
                // Парсим время из air datepicker формата
                const timeValue = this.value.trim();
                if (timeValue.includes(':')) {
                    const [hours, minutes] = timeValue.split(':');
                    const endHours = parseInt(hours) + 8;
                    const endTime = `${String(endHours).padStart(2, '0')}:${minutes}`;
                    endTimeInput.value = endTime;
                    
                    // Обновляем скрытые поля
                    updateHiddenFields();
                }
            }
        });

        // Обновление скрытых полей перед отправкой формы
        function updateHiddenFields() {
            const date = document.getElementById('shift_date').value;
            const startTime = document.getElementById('start_time').value.trim();
            const endTime = document.getElementById('end_time').value.trim();

            if (date && startTime && startTime.includes(':')) {
                const [day, month, year] = date.split('.');
                const startDateTime = `${year}-${month}-${day}T${startTime}`;
                document.getElementById('shift_starts_at').value = startDateTime;
            }

            if (date && endTime && endTime.includes(':')) {
                const [day, month, year] = date.split('.');
                const endDateTime = `${year}-${month}-${day}T${endTime}`;
                document.getElementById('shift_ends_at').value = endDateTime;
            }
        }

        // Обновляем скрытые поля при изменении любого из полей
        ['shift_date', 'start_time', 'end_time'].forEach(id => {
            document.getElementById(id).addEventListener('change', updateHiddenFields);
        });

        // Обновляем скрытые поля перед отправкой формы
        document.querySelector('form').addEventListener('submit', function(e) {
            updateHiddenFields();
        });

        // Инициализация скрытых полей
        updateHiddenFields();
    });
</script>
@endpush 