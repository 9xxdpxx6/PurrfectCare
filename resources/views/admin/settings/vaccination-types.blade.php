@extends('layouts.admin')

@section('title', 'Типы вакцинаций')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
<style>
    /* Стили для TomSelect в секции препаратов - динамичная высота */
    .drug-select {
        min-height: 38px;
    }
    
    .ts-wrapper {
        min-height: 38px;
    }
    
    .ts-control {
        min-height: 38px;
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
        font-size: 1rem;
        line-height: 1.5;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 2px;
    }
    
    .ts-dropdown {
        max-height: 200px;
        overflow-y: auto;
        z-index: 1050;
    }
    
    .ts-dropdown .option {
        padding: 0.375rem 0.75rem;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .ts-dropdown .option:hover {
        background-color: var(--bs-primary);
        color: white;
    }
    
    .ts-dropdown .option.active {
        background-color: var(--bs-primary);
        color: white;
    }
    
    /* Улучшение отображения в карточках */
    .card .drug-row .ts-wrapper {
        width: 100%;
    }
    
    /* Адаптивность для мобильных */
    @media (max-width: 768px) {
        .ts-dropdown {
            max-height: 150px;
        }
        
        .drug-row .col-6 {
            margin-bottom: 8px;
        }
    }
    
    /* Дополнительные стили для корректного отображения */
    .edit-fields .ts-wrapper {
        position: relative;
    }
    
    .edit-fields .ts-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    /* Улучшение отображения выбранных элементов */
    .ts-control .item {
        background-color: var(--bs-primary);
        color: white;
        border-radius: 0.25rem;
        padding: 2px 6px;
        margin: 2px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }
    
    .ts-control .item .remove {
        color: white;
        font-weight: bold;
        margin-left: 4px;
    }
    
    .ts-control .item .remove:hover {
        color: #ffcccc;
    }
    
    /* Улучшение отображения длинных названий */
    .ts-control .item {
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }
    
    .ts-control .item .text {
        max-width: calc(100% - 20px);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Унификация размеров всех элементов */
    .drug-row .form-control,
    .drug-row .form-select {
        height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .drug-row .btn {
        height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Стили для кнопки добавления препарата */
    .btn-add-drug {
        border-style: dashed;
        border-width: 2px;
        background-color: transparent;
        color: var(--bs-primary);
        transition: all 0.2s ease-in-out;
    }
    
    .btn-add-drug:hover {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-add-drug:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    /* Улучшение отступов в секции препаратов */
    .drugs-section {
        padding: 1rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.375rem;
        background-color: var(--bs-secondary-bg);
        margin-bottom: 1rem;
    }
    
    .drugs-section .form-label {
        font-weight: 500;
        color: var(--bs-body-color);
        margin-bottom: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Типы вакцинаций - {{ $vaccinationTypes->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить тип вакцинации
        </button>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:200px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск..." value="{{ request('search') }}">
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.settings.vaccination-types.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($vaccinationTypes as $type)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif" data-id="{{ $type->id }}" data-original="{{ json_encode(['name' => $type->name, 'description' => $type->description, 'price' => $type->price, 'drugs' => $type->drugs->map(function($drug) { return ['drug_id' => $drug->id, 'dosage' => $drug->pivot->dosage]; })]) }}">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $type->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Цена:</span> {{ number_format($type->price, 0, ',', ' ') }} ₽
                            </div>
                            @if($type->description)
                                <div class="text-muted mb-2">
                                    <span>Описание:</span> {{ $type->description }}
                                </div>
                            @endif
                            @if($type->drugs->count() > 0)
                                <div class="text-muted">
                                    <span>Препараты:</span>
                                    <ul class="mb-0 mt-1">
                                        @foreach($type->drugs as $drug)
                                            <li>{{ $drug->name }} - {{ $drug->pivot->dosage }} {{ $drug->unit->name ?? 'мл' }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="d-none edit-fields">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="{{ $type->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Цена</label>
                                <input type="number" class="form-control" value="{{ $type->price }}" 
                                       data-field="price" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Описание</label>
                                <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)">{{ $type->description }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="drugs-section">
                                    <label class="form-label">Препараты</label>
                                    <div id="drugs-container-{{ $type->id }}">
                                        @foreach($type->drugs as $drug)
                                            <div class="drug-row mb-2">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <select class="form-select drug-select" data-field="drug_id" onchange="markAsChanged(this)">
                                                            <option value="{{ $drug->id }}" selected>{{ $drug->name }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" 
                                                               placeholder="Дозировка" value="{{ $drug->pivot->dosage }}"
                                                               data-field="dosage" onchange="markAsChanged(this)">
                                                    </div>
                                                    <div class="col-2">
                                                        <button type="button" class="btn btn-outline-danger" onclick="removeDrugRow(this)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div> 
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-add-drug" onclick="addDrugRow({{ $type->id }})">
                                            <i class="bi bi-plus-circle me-1"></i> Добавить препарат
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                        <button type="button" class="btn btn-outline-warning edit-btn" title="Редактировать" onclick="toggleEdit(this)">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success save-btn d-none" title="Сохранить" onclick="saveRow(this)">
                            <span class="d-none d-lg-inline-block">Сохранить</span>
                            <i class="bi bi-check"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary cancel-btn d-none" title="Отменить" onclick="cancelEdit(this)">
                            <span class="d-none d-lg-inline-block">Отменить</span>
                            <i class="bi bi-x"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" title="Удалить" onclick='deleteRow({{ $type->id }})'>
                            <span class="d-none d-lg-inline-block">Удалить</span>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($vaccinationTypes->isEmpty())
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="bi bi-shield-check display-3 text-muted"></i>
        </div>
        <h5 class="text-muted">Типы вакцинаций не найдены</h5>
        <p class="text-muted">Добавьте первый тип вакцинации для начала работы</p>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить тип вакцинации
        </button>
    </div>
@endif

{{ $vaccinationTypes->links('vendor.pagination.bootstrap-5') }}

<script>
function addNewRow() {
    const container = document.querySelector('.row.g-3');
    const newRow = createEditableRow();
    container.insertBefore(newRow, container.firstChild);
    
    // Инициализируем селекты для препаратов
    initDrugSelects(newRow);
}

function createEditableRow() {
    const div = document.createElement('div');
    div.className = 'col-12';
    div.innerHTML = `
        <div class="card h-100 border-0 border-bottom shadow-sm border-primary" data-id="new">
            <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div class="edit-fields flex-grow-1">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small text-muted">Название</label>
                            <input type="text" class="form-control" 
                                   data-field="name" onchange="markAsChanged(this)" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Цена</label>
                            <input type="number" class="form-control" 
                                   data-field="price" onchange="markAsChanged(this)" value="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Описание</label>
                            <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="drugs-section">
                                <label class="form-label">Препараты</label>
                                <div class="drugs-container">
                                    <div class="drug-row mb-2">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <select class="form-select drug-select" data-field="drug_id" onchange="markAsChanged(this)" required>
                                                    <option value="">Выберите препарат</option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control" 
                                                       placeholder="Дозировка" data-field="dosage" 
                                                       onchange="markAsChanged(this)" step="0.01" min="0.01" required>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-outline-danger" onclick="removeDrugRow(this)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-add-drug" onclick="addDrugRowToNew(this)">
                                        <i class="bi bi-plus-circle me-1"></i> Добавить препарат
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                    <button type="button" class="btn btn-outline-success save-btn" title="Сохранить" onclick="saveRow(this)">
                        <span class="d-none d-lg-inline-block">Сохранить</span>
                        <i class="bi bi-check"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary cancel-btn" title="Отменить" onclick="cancelNewRow(this)">
                        <span class="d-none d-lg-inline-block">Отменить</span>
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    return div;
}

function addDrugRow(typeId) {
    const container = document.querySelector(`#drugs-container-${typeId}`);
    if (container) {
        const drugRow = createDrugRow();
        container.appendChild(drugRow);
        initDrugSelects(drugRow);
    }
}

function addDrugRowToNew(button) {
    // Ищем контейнер препаратов в новой строке
    const drugsSection = button.closest('.drugs-section');
    if (drugsSection) {
        const container = drugsSection.querySelector('.drugs-container');
        if (container) {
            const drugRow = createDrugRow();
            container.appendChild(drugRow);
            initDrugSelects(drugRow);
        }
    }
}

function createDrugRow() {
    const div = document.createElement('div');
    div.className = 'drug-row mb-2';
    div.innerHTML = `
        <div class="row g-2">
            <div class="col-6">
                <select class="form-select drug-select" data-field="drug_id" onchange="markAsChanged(this)" required>
                    <option value="">Выберите препарат</option>
                </select>
            </div>
            <div class="col-4">
                <input type="number" class="form-control" 
                       placeholder="Дозировка" data-field="dosage" 
                       onchange="markAsChanged(this)" step="0.01" min="0.01" required>
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger" onclick="removeDrugRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    return div;
}

function removeDrugRow(button) {
    const drugRow = button.closest('.drug-row');
    if (drugRow) {
        drugRow.remove();
    }
}

function initDrugSelects(container) {
    if (!container) {
        console.error('Container is null or undefined');
        return;
    }
    
    const selects = container.querySelectorAll('.drug-select');
    selects.forEach(select => {
        // Если селект еще не инициализирован как TomSelect
        if (select && !select.tomselect) {
            try {
                initTomSelectForDrug(select);
            } catch (error) {
                console.error('Error initializing TomSelect for drug:', error);
            }
        }
    });
}

function initDrugSelectsSilently(container) {
    if (!container) {
        console.error('Container is null or undefined');
        return;
    }
    
    const selects = container.querySelectorAll('.drug-select');
    selects.forEach(select => {
        // Если селект еще не инициализирован как TomSelect
        if (select && !select.tomselect) {
            try {
                initTomSelectForDrugSilently(select);
            } catch (error) {
                console.error('Error initializing TomSelect for drug:', error);
            }
        }
    });
}

function initTomSelectForDrug(select) {
    // Проверяем, что селект существует
    if (!select) {
        console.error('Select element is null or undefined');
        return null;
    }
    
    // Если есть уже выбранное значение, сохраняем его
    const selectedValue = select.value || '';
    const selectedText = (select.selectedIndex >= 0 && select.options[select.selectedIndex]) ? select.options[select.selectedIndex].text : '';
    
    // Инициализируем TomSelect
    const tomSelect = new createTomSelect(select, {
        placeholder: 'Поиск препарата...',
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        allowEmptyOption: false,
        preload: true,
        maxOptions: 50,
        maxItems: 1,
        closeAfterSelect: true,
        load: function(query, callback) {
            let url = '{{ route("admin.vaccinations.drug-options") }}?q=' + encodeURIComponent(query);
            
            // Если есть выбранное значение и это первая загрузка, передаём его
            if (selectedValue && !query) {
                url += '&selected=' + encodeURIComponent(selectedValue);
            }
            
            fetch(url)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        onItemAdd: function() {
            if (this.setTextboxValue) {
                this.setTextboxValue('');
            }
            if (this.refreshOptions) {
                this.refreshOptions();
            }
            setTimeout(() => {
                if (this.close) this.close();
                if (this.blur) this.blur();
            }, 50);
        },
        onChange: function() {
            // Вызываем markAsChanged для отслеживания изменений
            if (this.input && typeof markAsChanged === 'function') {
                markAsChanged(this.input);
            }
        },
        onDropdownOpen: function() {
            // Убеждаемся, что dropdown не выходит за границы
            const dropdown = this.dropdown;
            if (dropdown) {
                dropdown.style.maxHeight = '200px';
                dropdown.style.overflowY = 'auto';
            }
        }
    });
    
    // Если было выбранное значение, восстанавливаем его
    if (selectedValue && selectedText && selectedText !== 'Выберите препарат' && selectedText !== '') {
        try {
            // Добавляем опцию и выбираем её
            tomSelect.addOption({
                value: selectedValue,
                text: selectedText
            });
            tomSelect.setValue(selectedValue);
        } catch (error) {
            console.error('Error setting initial value:', error);
        }
    }
    
    return tomSelect;
}

function initTomSelectForDrugSilently(select) {
    // Проверяем, что селект существует
    if (!select) {
        console.error('Select element is null or undefined');
        return null;
    }
    
    // Если есть уже выбранное значение, сохраняем его
    const selectedValue = select.value || '';
    const selectedText = (select.selectedIndex >= 0 && select.options[select.selectedIndex]) ? select.options[select.selectedIndex].text : '';
    
    // Инициализируем TomSelect с отключенным автозаполнением
    const tomSelect = new createTomSelect(select, {
        placeholder: 'Поиск препарата...',
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        allowEmptyOption: false,
        preload: false, // Отключаем предзагрузку
        maxOptions: 50,
        maxItems: 1,
        closeAfterSelect: true,
        load: function(query, callback) {
            let url = '{{ route("admin.vaccinations.drug-options") }}?q=' + encodeURIComponent(query);
            
            // Если есть выбранное значение и это первая загрузка, передаём его
            if (selectedValue && !query) {
                url += '&selected=' + encodeURIComponent(selectedValue);
            }
            
            fetch(url)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        onItemAdd: function() {
            if (this.setTextboxValue) {
                this.setTextboxValue('');
            }
            if (this.refreshOptions) {
                this.refreshOptions();
            }
            setTimeout(() => {
                if (this.close) this.close();
                if (this.blur) this.blur();
            }, 50);
        },
        onChange: function() {
            // Вызываем markAsChanged для отслеживания изменений
            if (this.input && typeof markAsChanged === 'function') {
                markAsChanged(this.input);
            }
        },
        onDropdownOpen: function() {
            // Убеждаемся, что dropdown не выходит за границы
            const dropdown = this.dropdown;
            if (dropdown) {
                dropdown.style.maxHeight = '200px';
                dropdown.style.overflowY = 'auto';
            }
        }
    });
    
    // Если было выбранное значение, восстанавливаем его БЕЗ открытия dropdown
    if (selectedValue && selectedText && selectedText !== 'Выберите препарат' && selectedText !== '') {
        try {
            // Добавляем опцию и выбираем её
            tomSelect.addOption({
                value: selectedValue,
                text: selectedText
            });
            tomSelect.setValue(selectedValue);
        } catch (error) {
            console.error('Error setting initial value:', error);
        }
    }
    
    return tomSelect;
}

function toggleEdit(button) {
    // Сначала закрываем все открытые поля редактирования
    closeAllEditFields();
    
    const card = button.closest('.card');
    if (!card) return;
    
    const editFields = card.querySelector('.edit-fields');
    const editBtn = card.querySelector('.edit-btn');
    const saveBtn = card.querySelector('.save-btn');
    const cancelBtn = card.querySelector('.cancel-btn');
    
    if (editFields) editFields.classList.remove('d-none');
    if (editBtn) editBtn.classList.add('d-none');
    if (saveBtn) saveBtn.classList.remove('d-none');
    if (cancelBtn) cancelBtn.classList.remove('d-none');
    
    // Инициализируем селекты препаратов БЕЗ автоматического открытия
    initDrugSelectsSilently(card);
}

function closeAllEditFields() {
    // Закрываем все открытые поля редактирования
    document.querySelectorAll('.edit-fields').forEach(editFields => {
        if (editFields && !editFields.classList.contains('d-none')) {
            const card = editFields.closest('.card');
            if (card) {
                const editBtn = card.querySelector('.edit-btn');
                const saveBtn = card.querySelector('.save-btn');
                const cancelBtn = card.querySelector('.cancel-btn');
                
                editFields.classList.add('d-none');
                if (editBtn) editBtn.classList.remove('d-none');
                if (saveBtn) saveBtn.classList.add('d-none');
                if (cancelBtn) cancelBtn.classList.add('d-none');
                
                // Убираем предупреждение об изменениях
                card.classList.remove('border-warning');
            }
        }
    });
}

function cancelEdit(button) {
    const card = button.closest('.card');
    if (!card) return;
    
    const editFields = card.querySelector('.edit-fields');
    if (!editFields) return;
    
    // Восстанавливаем оригинальные значения
    const original = JSON.parse(card.dataset.original || '{}');
    
    const nameField = editFields.querySelector('[data-field="name"]');
    const priceField = editFields.querySelector('[data-field="price"]');
    const descriptionField = editFields.querySelector('[data-field="description"]');
    
    if (nameField && original.name) nameField.value = original.name;
    if (priceField && original.price) priceField.value = original.price;
    if (descriptionField) descriptionField.value = original.description || '';
    
    // Восстанавливаем препараты в TomSelect
    const drugSelects = editFields.querySelectorAll('.drug-select');
    drugSelects.forEach((select, index) => {
        if (select.tomselect && original.drugs && original.drugs[index]) {
            const drug = original.drugs[index];
            // Очищаем и добавляем оригинальное значение
            select.tomselect.clear();
            select.tomselect.addOption({
                value: drug.drug_id,
                text: (select.selectedIndex >= 0 && select.options[select.selectedIndex]) ? select.options[select.selectedIndex].text : 'Препарат'
            });
            select.tomselect.setValue(drug.drug_id);
        }
    });
    
    // Закрываем только текущее поле редактирования
    closeEditFields(card);
}

function closeEditFields(card) {
    if (!card) return;
    
    const editFields = card.querySelector('.edit-fields');
    const editBtn = card.querySelector('.edit-btn');
    const saveBtn = card.querySelector('.save-btn');
    const cancelBtn = card.querySelector('.cancel-btn');
    
    if (editFields) editFields.classList.add('d-none');
    if (editBtn) editBtn.classList.remove('d-none');
    if (saveBtn) saveBtn.classList.add('d-none');
    if (cancelBtn) cancelBtn.classList.add('d-none');
    
    card.classList.remove('border-warning');
}

function cancelNewRow(button) {
    const card = button.closest('.col-12');
    if (card) {
        card.remove();
    }
}

function markAsChanged(input) {
    const card = input.closest('.card');
    if (card) {
        card.classList.add('border-warning');
    }
}

function saveRow(button) {
    const card = button.closest('.card');
    if (!card) {
        alert('Ошибка: не найдена карточка');
        return;
    }
    
    const id = card.dataset.id;
    
    // Проверяем существование элементов
    const nameField = card.querySelector('[data-field="name"]');
    const priceField = card.querySelector('[data-field="price"]');
    const descriptionField = card.querySelector('[data-field="description"]');
    
    if (!nameField || !priceField || !descriptionField) {
        alert('Ошибка: не найдены обязательные поля формы');
        return;
    }
    
    const data = {
        name: nameField.value,
        price: parseFloat(priceField.value) || 0,
        description: descriptionField.value,
        drugs: []
    };
    
    console.log('Saving data:', data);
    
    // Собираем данные о препаратах
    const drugRows = card.querySelectorAll('.drug-row');
    drugRows.forEach(row => {
        const drugSelect = row.querySelector('[data-field="drug_id"]');
        const dosageField = row.querySelector('[data-field="dosage"]');
        
        if (drugSelect && dosageField) {
            // Получаем значение из TomSelect или обычного селекта
            let drugId;
            if (drugSelect.tomselect) {
                drugId = drugSelect.tomselect.getValue();
            } else {
                drugId = drugSelect.value;
            }
            
            const dosage = dosageField.value;
            
            console.log('Drug data:', { drugId, dosage, drugSelect: drugSelect.outerHTML });
            
            if (drugId && dosage) {
                data.drugs.push({
                    drug_id: parseInt(drugId),
                    dosage: parseFloat(dosage),
                });
            }
        }
    });
    
    if (!data.name.trim()) {
        alert('Название обязательно для заполнения');
        return;
    }
    
    if (data.drugs.length === 0) {
        alert('Необходимо добавить хотя бы один препарат');
        return;
    }
    
    const url = id === 'new' 
        ? '{{ route("admin.settings.vaccination-types.store") }}'
        : `{{ route("admin.settings.vaccination-types.index") }}/${id}`;
    
    const method = id === 'new' ? 'POST' : 'PATCH';
    
    // Получаем CSRF токен с проверкой
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value ||
                     '{{ csrf_token() }}';
    
    console.log('Sending request to:', url, 'with method:', method);
    console.log('Request data:', data);
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}. Response: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Закрываем поля редактирования перед перезагрузкой
            closeAllEditFields();
            location.reload();
        } else {
            alert(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при сохранении: ' + error.message);
    });
}

function deleteRow(id) {
    if (!confirm('Вы уверены, что хотите удалить этот тип вакцинации?')) {
        return;
    }
    
    // Получаем CSRF токен с проверкой
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : 
                     document.querySelector('input[name="_token"]')?.value ||
                     '{{ csrf_token() }}';
    
    fetch(`{{ route("admin.settings.vaccination-types.index") }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при удалении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Ошибка при удалении');
    });
}

// Инициализация TomSelect при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем все существующие селекты препаратов БЕЗ автоматического открытия
    document.querySelectorAll('.drug-select').forEach(select => {
        if (!select.tomselect) {
            initTomSelectForDrugSilently(select);
        }
    });
});
</script>
@endsection
