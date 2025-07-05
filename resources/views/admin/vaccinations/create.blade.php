@extends('layouts.admin')

@section('title', 'Создать вакцинацию')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать вакцинацию</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Назад к списку</span>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="administered_at" class="form-label">Дата проведения</label>
                                <input type="text" name="administered_at" id="administered_at" 
                                    class="form-control @error('administered_at') is-invalid @enderror" 
                                    value="{{ old('administered_at') }}" placeholder="дд.мм.гггг">
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
                                    value="{{ old('next_due') }}" placeholder="дд.мм.гггг">
                                @error('next_due')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label">Препараты</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-drug">
                                <i class="bi bi-plus"></i> Добавить препарат
                            </button>
                        </div>
                        
                        <div id="drugs-container">
                            @if(old('drugs'))
                                @foreach(old('drugs') as $index => $drug)
                                    <div class="drug-item border p-3 mb-3 rounded">
                                        <div class="row gy-2 flex-row align-items-end">
                                            <div class="col-12 col-lg d-flex flex-column">
                                                <label class="form-label">Препарат</label>
                                                <select name="drugs[{{ $index }}][drug_id]" class="form-select drug-select w-100" data-url="{{ route('admin.vaccinations.drug-options') }}">
                                                    @if($drug['drug_id'])
                                                        @php
                                                            $selectedDrug = \App\Models\Drug::with('unit')->find($drug['drug_id']);
                                                        @endphp
                                                        @if($selectedDrug)
                                                            <option value="{{ $selectedDrug->id }}" selected>
                                                                {{ $selectedDrug->name }}@if($selectedDrug->unit) ({{ $selectedDrug->unit->symbol }})@endif
                                                            </option>
                                                        @endif
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-8 col-md-4 col-lg-3 d-flex flex-column">
                                                <label class="form-label">Дозировка</label>
                                                <input type="number" step="0.01" min="0.01" max="999.99" name="drugs[{{ $index }}][dosage]" class="form-control" value="{{ $drug['dosage'] }}">
                                                <input type="hidden" name="drugs[{{ $index }}][batch_number]" value="BATCH{{ $drug['drug_id'] ?? '' }}">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger remove-drug">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="drug-item border p-3 mb-3 rounded">
                                    <div class="row gy-2 flex-row align-items-end">
                                        <div class="col-12 col-lg d-flex flex-column">
                                            <label class="form-label">Препарат</label>
                                            <select name="drugs[0][drug_id]" class="form-select drug-select w-100" data-url="{{ route('admin.vaccinations.drug-options') }}">
                                            </select>
                                        </div>
                                        <div class="col-8 col-md-4 col-lg-3 d-flex flex-column">
                                            <label class="form-label">Дозировка</label>
                                            <input type="number" step="0.01" min="0.01" max="999.99" name="drugs[0][dosage]" class="form-control">
                                            <input type="hidden" name="drugs[0][batch_number]" value="">
                                        </div>
                                        <div class="col-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                                            <button type="button" class="btn btn-outline-danger remove-drug ms-md-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Создать вакцинацию
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
        const selectedPetId = '{{ old('pet_id') }}';
        new createTomSelect('#pet_id', {
            placeholder: 'Выберите питомца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                if (selectedPetId && !query) {
                    url += '&selected=' + encodeURIComponent(selectedPetId);
                }
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            }
        });

        const selectedVetId = '{{ old('veterinarian_id') }}';
        new createTomSelect('#veterinarian_id', {
            placeholder: 'Выберите ветеринара...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                if (selectedVetId && !query) {
                    url += '&selected=' + encodeURIComponent(selectedVetId);
                }
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions();
            }
        });

        let drugIndex = {{ old('drugs') ? count(old('drugs')) : 1 }};
        
        function initDrugSelect(select, selectedDrugId = null) {
            new createTomSelect(select, {
                placeholder: 'Выберите препарат...',
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                load: function(query, callback) {
                    let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                    if (selectedDrugId && !query) {
                        url += '&selected=' + encodeURIComponent(selectedDrugId);
                    }
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                },
                onItemAdd: function() {
                    this.setTextboxValue('');
                    this.refreshOptions();
                }
            });
        }

        document.getElementById('add-drug').addEventListener('click', function() {
            const container = document.getElementById('drugs-container');
            const drugItem = document.createElement('div');
            drugItem.className = 'drug-item border p-3 mb-3 rounded';
            drugItem.innerHTML = `
                <div class="row gy-2 flex-row align-items-end">
                    <div class="col-12 col-lg d-flex flex-column">
                        <label class="form-label">Препарат</label>
                        <select name="drugs[${drugIndex}][drug_id]" class="form-select drug-select w-100" 
                                data-url="{{ route('admin.vaccinations.drug-options') }}"></select>
                    </div>
                    <div class="col-8 col-md-4 col-lg-3 d-flex flex-column">
                        <label class="form-label">Дозировка</label>
                        <input type="number" step="0.01" min="0.01" max="999.99" name="drugs[${drugIndex}][dosage]" class="form-control">
                        <input type="hidden" name="drugs[${drugIndex}][batch_number]" value="">
                    </div>
                    <div class="col-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                        <button type="button" class="btn btn-outline-danger remove-drug ms-md-2">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(drugItem);
            initDrugSelect(drugItem.querySelector('.drug-select'));
            drugIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-drug')) {
                const drugItem = e.target.closest('.drug-item');
                const container = document.getElementById('drugs-container');
                if (container.children.length > 1) {
                    drugItem.remove();
                } else {
                    alert('Должен быть выбран хотя бы один препарат');
                }
            }
        });

        document.querySelectorAll('.drug-select').forEach(function(select) {
            const selected = select.querySelector('option[selected]');
            initDrugSelect(select, selected ? selected.value : null);
        });

        let prevAdministeredAt = '';
        createDatepicker('#administered_at', {
            onShow: function() {
                prevAdministeredAt = document.getElementById('administered_at').value;
            },
            onSelect: function(formattedDate, date, inst) {
                const nextDueInput = document.getElementById('next_due');
                const currentNextDue = nextDueInput.value;
                
                // Дата находится в formattedDate.date, а не в параметре date
                const selectedDate = formattedDate.date;
                
                if (selectedDate) {
                    const nextYear = new Date(selectedDate.getTime());
                    nextYear.setFullYear(nextYear.getFullYear() + 1);
                    const pad = n => n < 10 ? '0' + n : n;
                    const nextDueStr = pad(nextYear.getDate()) + '.' + pad(nextYear.getMonth() + 1) + '.' + nextYear.getFullYear();
                    
                    // Если next_due пустое или совпадает с предыдущим administered_at
                    if (!currentNextDue || currentNextDue.trim() === '' || currentNextDue === prevAdministeredAt) {
                        nextDueInput.value = nextDueStr;
                        // Обновляем datepicker для next_due, если он уже инициализирован
                        if (nextDueInput.datepicker) {
                            nextDueInput.datepicker.selectDate(nextYear);
                        }
                    }
                }
            }
        });

        createDatepicker('#next_due');
    });
</script>
@endpush 