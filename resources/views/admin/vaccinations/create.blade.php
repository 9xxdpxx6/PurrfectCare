@extends('layouts.admin')

@section('title', 'Добавить вакцинацию')



@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить вакцинацию</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> Информация о вакцинации
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.vaccinations.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pet_id" class="form-label">Питомец</label>
                                <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.vaccinations.pet-options') }}">
                                    @if(old('pet_id'))
                                        @php
                                            $selectedPet = \App\Models\Pet::with('client')->find(old('pet_id'));
                                        @endphp
                                        @if($selectedPet)
                                            <option value="{{ $selectedPet->id }}" selected>
                                                {{ $selectedPet->name }} ({{ $selectedPet->client->name ?? 'Без владельца' }})
                                            </option>
                                        @endif
                                    @endif
                                </select>
                                @error('pet_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="veterinarian_id" class="form-label">Ветеринар</label>
                                <select name="veterinarian_id" id="veterinarian_id" class="form-select @error('veterinarian_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.vaccinations.veterinarian-options') }}">
                                    @if(old('veterinarian_id'))
                                        @php
                                            $selectedVeterinarian = \App\Models\Employee::find(old('veterinarian_id'));
                                        @endphp
                                        @if($selectedVeterinarian)
                                            <option value="{{ $selectedVeterinarian->id }}" selected>
                                                {{ $selectedVeterinarian->name }}
                                            </option>
                                        @endif
                                    @endif
                                </select>
                                @error('veterinarian_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="vaccination_type_id" class="form-label">Тип вакцинации</label>
                                <select name="vaccination_type_id" id="vaccination_type_id" class="form-select @error('vaccination_type_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.vaccinations.vaccination-type-options') }}">
                                    @if(old('vaccination_type_id'))
                                        @php
                                            $selectedType = \App\Models\VaccinationType::find(old('vaccination_type_id'));
                                        @endphp
                                        @if($selectedType)
                                            <option value="{{ $selectedType->id }}" selected>
                                                {{ $selectedType->name }} (₽{{ number_format($selectedType->price, 0, ',', ' ') }})
                                            </option>
                                        @endif
                                    @endif
                                </select>
                                @error('vaccination_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Выберите тип вакцинации. В него уже включены необходимые препараты и их дозировки.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="administered_at" class="form-label">Дата проведения</label>
                                <input type="text" name="administered_at" id="administered_at" 
                                    class="form-control @error('administered_at') is-invalid @enderror" 
                                    value="{{ old('administered_at', $default_administered_at ?? '') }}" placeholder="дд.мм.гггг" autocomplete="off">
                                @error('administered_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="next_due" class="form-label">Дата следующей вакцинации</label>
                                <input type="text" name="next_due" id="next_due" 
                                    class="form-control @error('next_due') is-invalid @enderror" 
                                    value="{{ old('next_due') }}" placeholder="дд.мм.гггг" autocomplete="off">
                                @error('next_due')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Информация о препаратах в выбранном типе вакцинации -->
                    <div class="mb-3" id="vaccination-type-info" style="display: none;">
                        <h6 class="text-muted">Препараты в составе вакцинации:</h6>
                        <div id="selected-drugs-info" class="p-3 rounded">
                            <!-- Здесь будет отображаться информация о препаратах -->
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline"></span>Отмена</span>    
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Простая инициализация TomSelect как в schedules
    new createTomSelect('#pet_id', {
        placeholder: 'Введите имя питомца или владельца...',
        preload: true,
        load: function(query, callback) {
            fetch('{{ route("admin.vaccinations.pet-options") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => callback(data))
                .catch(() => callback());
        }
    });
    
    new createTomSelect('#veterinarian_id', {
        placeholder: 'Выберите ветеринара...',
        preload: true,
        load: function(query, callback) {
            fetch('{{ route("admin.vaccinations.veterinarian-options") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => callback(data))
                .catch(() => callback());
        }
    });
    
    new createTomSelect('#vaccination_type_id', {
        placeholder: 'Выберите тип вакцинации...',
        preload: true,
        load: function(query, callback) {
            fetch('{{ route("admin.vaccinations.vaccination-type-options") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => callback(data))
                .catch(() => callback());
        }
    });
    
    // Загружаем уже выбранный тип вакцинации если есть
    const vaccinationTypeSelect = document.getElementById('vaccination_type_id');
    if (vaccinationTypeSelect && vaccinationTypeSelect.value) {
        fetch('{{ route("admin.vaccinations.vaccination-type-options") }}?selected=' + vaccinationTypeSelect.value)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const tomSelect = vaccinationTypeSelect.tomselect;
                    if (tomSelect) {
                        tomSelect.addOption(data[0]);
                        tomSelect.setValue(data[0].value);
                    }
                }
            });
    }

    // Обработчик изменения типа вакцинации
    document.getElementById('vaccination_type_id').addEventListener('change', function() {
        const typeId = this.value;
        const infoBlock = document.getElementById('vaccination-type-info');
        const drugsInfo = document.getElementById('selected-drugs-info');
        
        if (typeId) {
            // Получаем информацию о препаратах в типе вакцинации
            fetch(`{{ route('admin.settings.vaccination-types.show', '') }}/${typeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.drugs && data.drugs.length > 0) {
                        let drugsHtml = '<ul class="mb-0">';
                        data.drugs.forEach(drug => {
                            drugsHtml += `<li>${drug.name} - ${drug.pivot.dosage} ${drug.unit ? drug.unit.symbol : 'мл'}</li>`;
                        });
                        drugsHtml += '</ul>';
                        drugsInfo.innerHTML = drugsHtml;
                        infoBlock.style.display = 'block';
                    } else {
                        infoBlock.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Ошибка при получении информации о типе вакцинации:', error);
                    infoBlock.style.display = 'none';
                });
        } else {
            infoBlock.style.display = 'none';
        }
    });

    // Функция для установки даты следующей вакцинации
    function setNextDueDate(administeredDate) {
        const nextDueInput = document.getElementById('next_due');
        const nextYear = new Date(administeredDate.getTime());
        nextYear.setFullYear(nextYear.getFullYear() + 1);
        const pad = n => n < 10 ? '0' + n : n;
        const nextDueStr = pad(nextYear.getDate()) + '.' + pad(nextYear.getMonth() + 1) + '.' + nextYear.getFullYear();
        nextDueInput.value = nextDueStr;
    }

    // Datepicker для даты проведения
    createDatepicker('#administered_at', {
        onSelect: function(formattedDate, date, inst) {
            // Автоматически заполняем дату следующей вакцинации (через год)
            if (date) {
                setNextDueDate(date);
            }
        }
    });

    // Обработчик изменения даты проведения (для ручного ввода и datepicker)
    document.getElementById('administered_at').addEventListener('input', function() {
        updateNextDueDate(this.value);
    });

    document.getElementById('administered_at').addEventListener('change', function() {
        updateNextDueDate(this.value);
    });

    // Функция для обновления даты следующей вакцинации
    function updateNextDueDate(dateStr) {
        if (dateStr && dateStr.match(/^\d{2}\.\d{2}\.\d{4}$/)) {
            const dateParts = dateStr.split('.');
            const date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
            if (!isNaN(date.getTime())) {
                setNextDueDate(date);
            }
        }
    }

    // Datepicker для даты следующей вакцинации
    createDatepicker('#next_due');

    // Автоматически устанавливаем дату следующей вакцинации при загрузке страницы
    const administeredAtInput = document.getElementById('administered_at');
    if (administeredAtInput && administeredAtInput.value) {
        // Если дата проведения уже заполнена, устанавливаем следующую
        const dateParts = administeredAtInput.value.split('.');
        if (dateParts.length === 3) {
            const administeredDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
            setNextDueDate(administeredDate);
        }
    } else {
        // Если дата проведения не заполнена, устанавливаем сегодня + год
        const today = new Date();
        const nextYear = new Date(today.getTime());
        nextYear.setFullYear(nextYear.getFullYear() + 1);
        const pad = n => n < 10 ? '0' + n : n;
        const nextDueStr = pad(nextYear.getDate()) + '.' + pad(nextYear.getMonth() + 1) + '.' + nextYear.getFullYear();
        
        const nextDueInput = document.getElementById('next_due');
        nextDueInput.value = nextDueStr;
    }
});
</script>
@endpush
@endsection