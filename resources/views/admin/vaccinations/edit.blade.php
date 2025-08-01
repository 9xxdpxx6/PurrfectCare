@extends('layouts.admin')

@section('title', 'Редактировать вакцинацию')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать вакцинацию</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.vaccinations.show', $item) }}" class="btn btn-outline-info me-2">
            <i class="bi bi-eye"></i> <span class="d-none d-lg-inline">Просмотр</span>
        </a>
        <a href="{{ route('admin.vaccinations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-lg-inline">Назад к списку</span>
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
                <form action="{{ route('admin.vaccinations.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pet_id" class="form-label">Питомец</label>
                                <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.vaccinations.pet-options') }}">
                                    @php
                                        $selectedPetId = old('pet_id', $item->pet_id);
                                        $selectedPet = $selectedPetId ? \App\Models\Pet::with('client')->find($selectedPetId) : null;
                                    @endphp
                                    @if($selectedPet)
                                        <option value="{{ $selectedPet->id }}" selected>
                                            {{ $selectedPet->name }} ({{ $selectedPet->client->name ?? 'Без владельца' }})
                                        </option>
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
                                    @php
                                        $selectedVetId = old('veterinarian_id', $item->veterinarian_id);
                                        $selectedVet = $selectedVetId ? \App\Models\Employee::find($selectedVetId) : null;
                                    @endphp
                                    @if($selectedVet)
                                        <option value="{{ $selectedVet->id }}" selected>
                                            {{ $selectedVet->name }}
                                        </option>
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
                                    value="{{ old('administered_at', $item->administered_at?->format('d.m.Y')) }}" placeholder="дд.мм.гггг" autocomplete="off">
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
                                    value="{{ old('next_due', $item->next_due?->format('d.m.Y')) }}" placeholder="дд.мм.гггг" autocomplete="off">
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
                            @php
                                $oldDrugs = old('drugs');
                                $existingDrugs = $oldDrugs ?: $item->drugs->map(function($drug) {
                                    return [
                                        'drug_id' => $drug->id,
                                        // 'batch_number' => $drug->pivot->batch_number,
                                        'dosage' => $drug->pivot->dosage
                                    ];
                                })->toArray();
                            @endphp
                            
                            @if($existingDrugs)
                                @foreach($existingDrugs as $index => $drug)
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
                                            <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                                                <label class="form-label">Дозировка</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0.01" max="9999.99" name="drugs[{{ $index }}][dosage]" class="form-control" value="{{ $drug['dosage'] }}">
                                                    <span class="input-group-text dosage-unit" data-drug-index="{{ $index }}">
                                                        @if($drug['drug_id'])
                                                            @php
                                                                $selectedDrug = \App\Models\Drug::with('unit')->find($drug['drug_id']);
                                                            @endphp
                                                            {{ $selectedDrug && $selectedDrug->unit ? $selectedDrug->unit->symbol : 'у.е.' }}
                                                        @else
                                                            у.е.
                                                        @endif
                                                    </span>
                                                </div>
                                                <input type="hidden" name="drugs[{{ $index }}][batch_number]" value="BATCH{{ $drug['drug_id'] ?? '' }}">
                                            </div>
                                            <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                                                <button type="button" class="btn btn-outline-danger remove-drug ms-md-2">
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
                                            <select name="drugs[0][drug_id]" class="form-select drug-select" 
                                                    data-url="{{ route('admin.vaccinations.drug-options') }}">
                                            </select>
                                        </div>
                                        <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                                            <label class="form-label">Дозировка</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0.01" max="9999.99" name="drugs[0][dosage]" class="form-control" value="1">
                                                <span class="input-group-text dosage-unit" data-drug-index="0">у.е.</span>
                                            </div>
                                            <input type="hidden" name="drugs[0][batch_number]" value="">
                                        </div>
                                        <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                                            <button type="button" class="btn btn-outline-danger remove-drug ms-md-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.vaccinations.show', $item) }}" class="btn btn-outline-secondary">
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
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedPetId = '{{ old('pet_id', $item->pet_id) }}';
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
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        const selectedVetId = '{{ old('veterinarian_id', $item->veterinarian_id) }}';
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
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        let drugIndex = parseInt('{{ count($existingDrugs ?? []) }}');
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
                onChange: function(value) {
                    updateDosageUnit(this.input, value);
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
        }
        
        function updateDosageUnit(selectElement, drugId) {
            const drugItem = selectElement.closest('.drug-item');
            const unitSpan = drugItem.querySelector('.dosage-unit');
            
            if (!drugId || !unitSpan) {
                if (unitSpan) unitSpan.textContent = 'у.е.';
                return;
            }
            
            fetch(`{{ route('admin.vaccinations.drug-options') }}?selected=${drugId}`)
                .then(response => response.json())
                .then(data => {
                    const drug = data.find(item => item.value == drugId);
                    if (drug && drug.text) {
                        const match = drug.text.match(/\(([^)]+)\)$/);
                        if (match) {
                            unitSpan.textContent = match[1];
                        } else {
                            unitSpan.textContent = 'у.е.';
                        }
                    } else {
                        unitSpan.textContent = 'у.е.';
                    }
                })
                .catch(() => {
                    unitSpan.textContent = 'у.е.';
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
                        <select name="drugs[${drugIndex}][drug_id]" class="form-select drug-select" 
                                data-url="{{ route('admin.vaccinations.drug-options') }}"></select>
                    </div>
                    <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                        <label class="form-label">Дозировка</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" max="9999.99" name="drugs[${drugIndex}][dosage]" class="form-control" value="1">
                            <span class="input-group-text dosage-unit" data-drug-index="${drugIndex}">у.е.</span>
                        </div>
                        <input type="hidden" name="drugs[${drugIndex}][batch_number]" value="">
                    </div>
                    <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
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
        let prevAdministeredAt = document.getElementById('administered_at').value;
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
                    } else {
                    }
                }
            }
        });
        createDatepicker('#next_due');
    });
</script>
@endpush 