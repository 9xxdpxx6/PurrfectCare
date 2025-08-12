@extends('layouts.admin')

@section('title', 'Редактировать расписание')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать расписание</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.show', $item) }}" class="btn btn-outline-info me-2">
                <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Просмотр</span>
            </a>
            <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад
            </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.schedules.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                            <label for="veterinarian_id" class="form-label">Ветеринар</label>
                            <select name="veterinarian_id" id="veterinarian_id" class="form-select @error('veterinarian_id') is-invalid @enderror" required>
                                <option value="">Выберите ветеринара</option>
                                @foreach($veterinarians as $veterinarian)
                                    <option value="{{ $veterinarian->id }}" 
                                        @if(old('veterinarian_id', $item->veterinarian_id) == $veterinarian->id) selected @endif>
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
                            <label for="branch_id" class="form-label">Филиал</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">Выберите филиал</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        @if(old('branch_id', $item->branch_id) == $branch->id) selected @endif>
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
                            <label for="shift_date" class="form-label">Дата смены</label>
                            @php
                                $shiftDate = old('shift_date');
                                if (!$shiftDate) {
                                    $shiftDate = $item->shift_starts_at->format('d.m.Y');
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
                            <label for="start_time" class="form-label">Время начала</label>
                            @php
                                $startTime = old('start_time');
                                if (!$startTime) {
                                    $startTime = $item->shift_starts_at->format('H:i');
                                }
                            @endphp
                            <input type="text" name="start_time" id="start_time" 
                                class="form-control @error('shift_starts_at') is-invalid @enderror" 
                                value="{{ $startTime }}" readonly required placeholder="чч:мм">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">Время окончания</label>
                            @php
                                $endTime = old('end_time');
                                if (!$endTime) {
                                    $endTime = $item->shift_ends_at->format('H:i');
                                }
                            @endphp
                            <input type="text" name="end_time" id="end_time" 
                                class="form-control @error('shift_ends_at') is-invalid @enderror" 
                                value="{{ $endTime }}" readonly required placeholder="чч:мм">
                            @error('shift_ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Скрытые поля для отправки в нужном формате -->
                        <input type="hidden" name="shift_starts_at" id="shift_starts_at">
                        <input type="hidden" name="shift_ends_at" id="shift_ends_at">
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Текущее расписание</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <div>
                        <strong>Дата:</strong><br>
                        {{ $item->shift_starts_at->format('d.m.Y') }}
                        <small class="text-muted">({{ $item->shift_starts_at->locale('ru')->translatedFormat('l') }})</small>
                    </div>
                    
                    <div>
                        <strong>Время:</strong><br>
                        {{ $item->shift_starts_at->format('H:i') }} - {{ $item->shift_ends_at->format('H:i') }}
                    
                        @php
                            // Получаем разницу между началом и концом
                            $start = $item->shift_starts_at;
                            $end = $item->shift_ends_at;
                        
                            $diffInHours = $start->diff($end)->h; 
                            $diffInMinutes = $start->diff($end)->i;
                        @endphp
                    
                        <small class="text-muted">
                            ({{ $diffInHours }} ч. {{ $diffInMinutes }} мин.)
                        </small>
                    </div>
                    
                    <div>
                        <strong>Ветеринар:</strong><br>
                        {{ $item->veterinarian->name ?? 'Не указан' }}
                        @if($item->veterinarian && $item->veterinarian->specialization)
                            <br><small class="text-muted">{{ $item->veterinarian->specialization }}</small>
                        @endif
                    </div>
                    
                    <div>
                        <strong>Филиал:</strong><br>
                        {{ $item->branch->name ?? 'Не указан' }}
                    </div>
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

        const startTimeVal = document.getElementById('start_time').value;
        const endTimeVal = document.getElementById('end_time').value;

        function parseTimeToStartDate(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            const date = new Date();
            date.setHours(parseInt(hours), parseInt(minutes), 0, 0);
            return date;
        }

        // Air Datepicker
        createDatepicker('#shift_date');
        createDatepicker('#start_time', {
            timepicker: true,
            onlyTimepicker: true,
            startDate: parseTimeToStartDate(startTimeVal),
            timeFormat: 'HH:mm'
        });
        createDatepicker('#end_time', {
            timepicker: true,
            onlyTimepicker: true,
            startDate: parseTimeToStartDate(endTimeVal),
            timeFormat: 'HH:mm'
        });

        // Автоматическое обновление времени окончания смены
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');

        startTimeInput.addEventListener('change', function() {
            if (this.value) {
                // Парсим время из air datepicker формата
                const timeValue = this.value.trim();
                if (timeValue.includes(':')) {
                    updateHiddenFields();
                }
            }
        });

        // Обновление скрытых полей
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