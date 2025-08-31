@extends('layouts.admin')

@section('title', 'Редактировать анализ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать анализ</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.lab-tests.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clipboard-data"></i> Информация об анализе
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.lab-tests.update', $item) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pet_id" class="form-label">Питомец</label>
                                <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.lab-tests.pet-options') }}">
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
                                        data-url="{{ route('admin.lab-tests.veterinarian-options') }}">
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
                                <label for="lab_test_type_id" class="form-label">Тип анализа</label>
                                <select name="lab_test_type_id" id="lab_test_type_id" class="form-select @error('lab_test_type_id') is-invalid @enderror" 
                                        data-url="{{ route('admin.lab-tests.lab-test-type-options') }}">
                                    @php
                                        $selectedLabTestTypeId = old('lab_test_type_id', $item->lab_test_type_id);
                                        $selectedLabTestType = $selectedLabTestTypeId ? \App\Models\LabTestType::find($selectedLabTestTypeId) : null;
                                    @endphp
                                    @if($selectedLabTestType)
                                        <option value="{{ $selectedLabTestType->id }}" selected>
                                            {{ $selectedLabTestType->name }}
                                        </option>
                                    @endif
                                </select>
                                @error('lab_test_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="received_at" class="form-label">Дата получения</label>
                                <input type="text" name="received_at" id="received_at" 
                                    class="form-control @error('received_at') is-invalid @enderror" 
                                    value="{{ old('received_at') ? \Carbon\Carbon::parse(old('received_at'))->format('d.m.Y') : $item->received_at?->format('d.m.Y') }}" placeholder="дд.мм.гггг" autocomplete="off">
                                @error('received_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="completed_at" class="form-label">Дата завершения</label>
                                <input type="text" name="completed_at" id="completed_at" 
                                    class="form-control @error('completed_at') is-invalid @enderror" 
                                    value="{{ old('completed_at') ? \Carbon\Carbon::parse(old('completed_at'))->format('d.m.Y') : $item->completed_at?->format('d.m.Y') }}" placeholder="дд.мм.гггг" autocomplete="off">
                                @error('completed_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label">Результаты анализов</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-result">
                                <i class="bi bi-plus"></i> Добавить результат
                            </button>
                        </div>
                        
                        @if($errors->has('results'))
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->get('results') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        

                        
                        <div id="results-container">
                            @php
                                $oldResults = old('results');
                                $existingResults = $oldResults ?: $item->results->map(function($result) {
                                    return [
                                        'lab_test_param_id' => $result->lab_test_param_id,
                                        'value' => $result->value,
                                        'notes' => $result->notes
                                    ];
                                })->toArray();
                            @endphp
                            
                            @if($existingResults)
                                @foreach($existingResults as $index => $result)
                                    <div class="result-item border p-3 mb-3 rounded">
                                        <div class="row gy-2 flex-row align-items-start">
                                            <div class="col-12 col-lg d-flex flex-column">
                                                <label class="form-label">Параметр</label>
                                                <select name="results[{{ $index }}][lab_test_param_id]" class="form-select result-select w-100 @error('results.'.$index.'.lab_test_param_id') is-invalid @enderror" 
                                                        data-url="{{ route('admin.lab-tests.lab-test-param-options') }}">
                                                    @if($result['lab_test_param_id'])
                                                        @php
                                                            $selectedParam = \App\Models\LabTestParam::find($result['lab_test_param_id']);
                                                        @endphp
                                                        @if($selectedParam)
                                                            <option value="{{ $selectedParam->id }}" selected>
                                                                {{ $selectedParam->name }}
                                                            </option>
                                                        @endif
                                                    @endif
                                                </select>
                                                @error('results.'.$index.'.lab_test_param_id')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @if(!$errors->has('results.'.$index.'.lab_test_param_id'))
                                                    <div class="invalid-feedback d-block invisible">&nbsp;</div>
                                                @endif
                                            </div>
                                            <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                                                <label class="form-label">Значение</label>
                                                <input type="text" name="results[{{ $index }}][value]" class="form-control @error('results.'.$index.'.value') is-invalid @enderror" value="{{ $result['value'] }}" autocomplete="off">
                                                @error('results.'.$index.'.value')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                @if(!$errors->has('results.'.$index.'.value'))
                                                    <div class="invalid-feedback d-block invisible">&nbsp;</div>
                                                @endif
                                            </div>
                                            <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                                                <button type="button" class="btn btn-outline-danger remove-result ms-md-2">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <label class="form-label">Заметки</label>
                                                <textarea name="results[{{ $index }}][notes]" class="form-control" rows="2" placeholder="Дополнительные заметки...">{{ $result['notes'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="result-item border p-3 mb-3 rounded">
                                    <div class="row gy-2 flex-row align-items-start">
                                        <div class="col-12 col-lg d-flex flex-column">
                                            <label class="form-label">Параметр</label>
                                            <select name="results[0][lab_test_param_id]" class="form-select result-select w-100 @error('results.0.lab_test_param_id') is-invalid @enderror" 
                                                    data-url="{{ route('admin.lab-tests.lab-test-param-options') }}">
                                        </select>
                                            @error('results.0.lab_test_param_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @if(!$errors->has('results.0.lab_test_param_id'))
                                                <div class="invalid-feedback d-block invisible">&nbsp;</div>
                                            @endif
                                    </div>
                                    <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                                        <label class="form-label">Значение</label>
                                        <input type="text" name="results[0][value]" class="form-control @error('results.0.value') is-invalid @enderror" autocomplete="off">
                                            @error('results.0.value')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @if(!$errors->has('results.0.value'))
                                                <div class="invalid-feedback d-block invisible">&nbsp;</div>
                                            @endif
                                    </div>
                                        <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                                            <button type="button" class="btn btn-outline-danger remove-result ms-md-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <label class="form-label">Заметки</label>
                                            <textarea name="results[0][notes]" class="form-control" rows="2" placeholder="Дополнительные заметки..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.lab-tests.show', $item) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> <span class="d-none d-md-inline">Отмена</span>
                        </a>
                        @can('lab_tests.update')
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Сохранить
                        </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.result-item .row {
    align-items: start;
}

.result-item .form-control,
.result-item .form-select {
    height: 38px;
}

.result-item .btn {
    height: 38px;
    width: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 32px;
}

.result-item .invalid-feedback {
    min-height: 20px;
    margin-top: 0.25rem;
}

.result-item .form-label {
    margin-bottom: 0.5rem;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectedPetId = '{{ old("pet_id", $item->pet_id) }}';
        new createTomSelect('#pet_id', {
            placeholder: 'Введите кличку питомца или ФИО владельца...',
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
                    .then(response => {
                        return response.json();
                    })
                    .then(json => {
                        callback(json);
                    })
                    .catch((error) => {
                        console.error('Ошибка загрузки опций питомца:', error);
                        callback();
                    });
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

        const selectedVetId = '{{ old("veterinarian_id", $item->veterinarian_id) }}';
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

        const selectedLabTestTypeId = '{{ old("lab_test_type_id", $item->lab_test_type_id) }}';
        new createTomSelect('#lab_test_type_id', {
            placeholder: 'Выберите тип анализа...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                if (selectedLabTestTypeId && !query) {
                    url += '&selected=' + encodeURIComponent(selectedLabTestTypeId);
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

        let resultIndex = parseInt('{{ count($existingResults ?? []) }}');
        
        // Восстанавливаем динамически добавленные поля при ошибке валидации
        function restoreDynamicFields() {
            const container = document.getElementById('results-container');
            const oldResults = @json(old('results', []));
            
            // Если нет сохраненных данных - ничего не делаем
            if (!oldResults || oldResults.length === 0) {
                return;
            }
            
            // Проверяем, есть ли хотя бы одно поле с заполненными данными
            let hasValidData = false;
            for (let i = 0; i < oldResults.length; i++) {
                if (oldResults[i] && oldResults[i].lab_test_param_id && oldResults[i].value && oldResults[i].value !== 'null' && oldResults[i].value !== '') {
                    hasValidData = true;
                    break;
                }
            }
            
            // Если нет валидных данных - НЕ ТРОГАЕМ поля вообще
            if (!hasValidData) {
                return;
            }
            
            // Если есть валидные данные - восстанавливаем все поля
            // Удаляем все поля кроме первого
            const firstItem = container.querySelector('.result-item');
            container.innerHTML = '';
            container.appendChild(firstItem);
            
            // Добавляем остальные поля
            for (let i = 1; i < oldResults.length; i++) {
                addResultField(i, oldResults[i]);
            }
            
            // Обновляем первое поле данными и ошибками
            if (oldResults[0]) {
                const firstSelect = firstItem.querySelector('.result-select');
                const firstValue = firstItem.querySelector('input[name*="[value]"]');
                const firstNotes = firstItem.querySelector('textarea[name*="[notes]"]');
                
                // Проверяем ошибки для первого поля
                const hasFirstParamError = !oldResults[0].lab_test_param_id || oldResults[0].lab_test_param_id === '';
                const hasFirstValueError = !oldResults[0].value || oldResults[0].value === '' || oldResults[0].value === 'null';
                
                // Применяем классы ошибок
                if (hasFirstParamError) {
                    firstSelect.classList.add('is-invalid');
                }
                if (hasFirstValueError) {
                    firstValue.classList.add('is-invalid');
                }
                
                // Устанавливаем значения
                if (firstValue && oldResults[0].value && oldResults[0].value !== 'null' && oldResults[0].value !== '') {
                    firstValue.value = oldResults[0].value;
                }
                if (firstNotes && oldResults[0].notes && oldResults[0].notes !== 'null' && oldResults[0].notes !== '') {
                    firstNotes.value = oldResults[0].notes;
                }
            }
        }
        
        function addResultField(index, data = null) {
            const container = document.getElementById('results-container');
            const resultItem = document.createElement('div');
            resultItem.className = 'result-item border p-3 mb-3 rounded';
            
            const paramValue = data ? data.lab_test_param_id : '';
            const valueValue = data && data.value && data.value !== 'null' ? data.value : '';
            const notesValue = data && data.notes && data.notes !== 'null' ? data.notes : '';
            
            // Проверяем, есть ли ошибки для этого поля
            const oldResults = @json(old('results', []));
            const hasParamError = oldResults[index] && (!oldResults[index].lab_test_param_id || oldResults[index].lab_test_param_id === '');
            const hasValueError = oldResults[index] && (!oldResults[index].value || oldResults[index].value === '' || oldResults[index].value === 'null');
            
            resultItem.innerHTML = `
                <div class="row gy-2 flex-row align-items-start">
                    <div class="col-12 col-lg d-flex flex-column">
                        <label class="form-label">Параметр</label>
                        <select name="results[${index}][lab_test_param_id]" class="form-select result-select w-100 ${hasParamError ? 'is-invalid' : ''}" 
                                data-url="{{ route('admin.lab-tests.lab-test-param-options') }}">
                        </select>
                        ${hasParamError ? '<div class="invalid-feedback d-block">Параметр анализа обязателен</div>' : '<div class="invalid-feedback d-block invisible">&nbsp;</div>'}
                    </div>
                    <div class="col-8 col-md-6 col-lg-3 d-flex flex-column">
                        <label class="form-label">Значение</label>
                        <input type="text" name="results[${index}][value]" class="form-control ${hasValueError ? 'is-invalid' : ''}" value="${valueValue}" autocomplete="off">
                        ${hasValueError ? '<div class="invalid-feedback d-block">Значение результата обязательно</div>' : '<div class="invalid-feedback d-block invisible">&nbsp;</div>'}
                    </div>
                    <div class="col-4 col-md-6 col-lg-auto d-flex justify-content-end align-items-center" style="min-width:48px;">
                        <button type="button" class="btn btn-outline-danger remove-result ms-md-2">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <label class="form-label">Заметки</label>
                        <textarea name="results[${index}][notes]" class="form-control" rows="2" placeholder="Дополнительные заметки...">${notesValue}</textarea>
                    </div>
                </div>
            `;
            
            container.appendChild(resultItem);
            const select = resultItem.querySelector('.result-select');
            initResultSelect(select);
            
            // Если есть данные, устанавливаем значение после инициализации TomSelect
            if (data && data.lab_test_param_id) {
                setTimeout(() => {
                    if (select.tomselect) {
                        select.tomselect.setValue(data.lab_test_param_id);
                    }
                }, 100);
            }
        }
        
        function initResultSelect(select) {
            new createTomSelect(select, {
                placeholder: 'Выберите параметр...',
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                preload: true,
                load: function(query, callback) {
                    let url = this.input.dataset.url + '?q=' + encodeURIComponent(query);
                    fetch(url)
                        .then(response => {
                            return response.json();
                        })
                        .then(json => {
                            callback(json);
                        })
                        .catch((error) => {
                            console.error('Ошибка загрузки опций:', error);
                            callback();
                        });
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

        document.getElementById('add-result').addEventListener('click', function() {
            addResultField(resultIndex);
            resultIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-result')) {
                const resultItem = e.target.closest('.result-item');
                const container = document.getElementById('results-container');
                if (container.children.length > 1) {
                    resultItem.remove();
                } else {
                    alert('Должен быть указан хотя бы один результат');
                }
            }
        });

        document.querySelectorAll('.result-select').forEach(function(select) {
            const selected = select.querySelector('option[selected]');
            initResultSelect(select);
        });

        // Восстанавливаем динамически добавленные поля при ошибке валидации
        restoreDynamicFields();

        createDatepicker('#received_at');
        createDatepicker('#completed_at');
    });
</script>
@endpush 