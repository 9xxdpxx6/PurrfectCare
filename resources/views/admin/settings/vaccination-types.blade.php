@extends('layouts.admin')

@section('title', 'Типы вакцинаций')



@push('styles')
<style>
    /* Минимальные стили для TomSelect в секции препаратов */
    .drug-select {
        min-height: 38px;
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
    
    /* Стили для Bootstrap уведомлений */
    .notification-toast {
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: none;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .notification-toast .toast-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background-color: transparent;
    }
    
    .notification-toast .toast-body {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    /* Стили для кнопки закрытия в зависимости от типа уведомления */
    .notification-toast.bg-warning .btn-close {
        filter: invert(1) grayscale(100%) brightness(0);
        opacity: 1;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast.fade-out {
        animation: slideOutRight 0.3s ease-in forwards;
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

<!-- Bootstrap уведомления -->
<div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060; max-width: 400px;">
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
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-add-drug" onclick="addDrugRow(this)" data-type-id="{{ $type->id }}">
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
                        <button type="button" class="btn btn-outline-danger" title="Удалить" onclick="deleteRow(this)" data-type-id="{{ $type->id }}">
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
// Функции для работы с Bootstrap уведомлениями
function createToast(message, type = 'info', title = null) {
    const container = document.getElementById('notifications-container');
    if (!container) return;
    
    const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    
    // Определяем иконку и цвет в зависимости от типа
    let icon, bgClass, textClass;
    switch (type) {
        case 'success':
            icon = 'bi-check-circle-fill';
            bgClass = 'bg-success';
            textClass = 'text-white';
            title = title || 'Успешно';
            break;
        case 'error':
        case 'danger':
            icon = 'bi-exclamation-triangle-fill';
            bgClass = 'bg-danger';
            textClass = 'text-white';
            title = title || 'Ошибка';
            break;
        case 'warning':
            icon = 'bi-exclamation-circle-fill';
            bgClass = 'bg-warning';
            textClass = 'text-dark';
            title = title || 'Предупреждение';
            break;
        case 'info':
        default:
            icon = 'bi-info-circle-fill';
            bgClass = 'bg-info';
            textClass = 'text-white';
            title = title || 'Информация';
            break;
    }
    
    const toastHtml = `
        <div id="${toastId}" class="toast notification-toast ${bgClass} ${textClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${textClass}">
                <i class="bi ${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'}" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'error' || type === 'danger' ? 8000 : 5000
    });
    
    toast.show();
    
    // Автоматическое удаление элемента после скрытия
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
    
    return toast;
}

function showNotification(message, type = 'info', title = null) {
    return createToast(message, type, title);
}

function showError(message, title = 'Ошибка') {
    return createToast(message, 'error', title);
}

function showSuccess(message, title = 'Успешно') {
    return createToast(message, 'success', title);
}

function showWarning(message, title = 'Предупреждение') {
    return createToast(message, 'warning', title);
}

function showInfo(message, title = 'Информация') {
    return createToast(message, 'info', title);
}

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
                                   data-field="name" onchange="markAsChanged(this)">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Цена</label>
                            <input type="number" class="form-control" 
                                   data-field="price" onchange="markAsChanged(this)" value="0">
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
                                                <select class="form-select drug-select" data-field="drug_id" onchange="markAsChanged(this)">
                                                    <option value="">Выберите препарат</option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control" 
                                                       placeholder="Дозировка" data-field="dosage" 
                                                       onchange="markAsChanged(this)" step="0.01" min="0.01">
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

function addDrugRow(button) {
    const typeId = button.dataset.typeId;
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
                <select class="form-select drug-select" data-field="drug_id" onchange="markAsChanged(this)">
                    <option value="">Выберите препарат</option>
                </select>
            </div>
            <div class="col-4">
                <input type="number" class="form-control" 
                       placeholder="Дозировка" data-field="dosage" 
                       onchange="markAsChanged(this)" step="0.01" min="0.01">
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
    const tomSelect = createTomSelect(select, {
        placeholder: 'Поиск препарата...',
        valueField: 'value',
        labelField: 'text',
        searchField: 'text',
        allowEmptyOption: false,
        preload: true,
        maxOptions: 50,
        maxItems: 1,
        load: function(query, callback) {
            let url = '{{ route("admin.vaccinations.drug-options") }}?q=' + encodeURIComponent(query || '');
            
            // Если есть выбранное значение и это первая загрузка, передаём его
            if (selectedValue && !query) {
                url += '&selected=' + encodeURIComponent(selectedValue);
            }
            
            fetch(url)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
        onChange: function() {
            // Вызываем markAsChanged для отслеживания изменений
            if (this.input && typeof markAsChanged === 'function') {
                markAsChanged(this.input);
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
    
    // Инициализируем селекты препаратов
    initDrugSelects(card);
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
        showError('Не найдена карточка');
        return;
    }
    
    const id = card.dataset.id;
    
    // Проверяем существование элементов
    const nameField = card.querySelector('[data-field="name"]');
    const priceField = card.querySelector('[data-field="price"]');
    const descriptionField = card.querySelector('[data-field="description"]');
    
    if (!nameField || !priceField || !descriptionField) {
        showError('Не найдены обязательные поля формы');
        return;
    }
    
    const data = {
        name: nameField.value,
        price: parseFloat(priceField.value) || 0,
        description: descriptionField.value,
        drugs: []
    };
    
    // Собираем данные о препаратах
    const drugRows = card.querySelectorAll('.drug-row');
    let hasDrugWithoutDosage = false;
    let hasDrugWithDosage = false;
    
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
            
            if (drugId && dosage) {
                data.drugs.push({
                    drug_id: parseInt(drugId),
                    dosage: parseFloat(dosage),
                });
                hasDrugWithDosage = true;
            } else if (drugId && !dosage) {
                hasDrugWithoutDosage = true;
            }
        }
    });
    
    if (!data.name.trim()) {
        showWarning('Название обязательно для заполнения');
        return;
    }
    
    if (data.drugs.length === 0) {
        if (hasDrugWithoutDosage) {
            showWarning('Для всех выбранных препаратов необходимо указать дозировку');
        } else {
            showWarning('Необходимо добавить хотя бы один препарат');
        }
        return;
    }
    
    const url = id === 'new' 
                        ? '{{ route("admin.settings.vaccination-types.store") }}'
                  : '{{ url("admin/settings/vaccination-types") }}/' + id;
    
    const method = id === 'new' ? 'POST' : 'POST';
    
    // Получаем CSRF токен
    const csrfToken = '{{ csrf_token() }}';
    

    
    // Для обновления добавляем _method: PATCH
    if (id !== 'new') {
        data._method = 'PATCH';
    }
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        // Проверяем Content-Type
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                if (!response.ok) {
                    console.error('Error response:', data);
                    
                    // Если есть ошибки валидации, показываем их
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        throw new Error(errorMessages);
                    }
                    
                    throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
                }
                return data;
            });
        } else {
            // Если ответ не JSON, читаем как текст
            return response.text().then(text => {
                console.error('Non-JSON response:', text.substring(0, 500));
                throw new Error('Сервер вернул неверный формат ответа');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Закрываем поля редактирования перед перезагрузкой
            closeAllEditFields();
            showSuccess('Тип вакцинации успешно сохранен');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showError(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showError('Ошибка при сохранении: ' + error.message);
    });
}

function deleteRow(button) {
    const id = button.dataset.typeId;
    if (!confirm('Вы уверены, что хотите удалить этот тип вакцинации?')) {
        return;
    }
    
    // Получаем CSRF токен
    const csrfToken = '{{ csrf_token() }}';
    
    fetch('{{ url("admin/settings/vaccination-types") }}/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
                }
                return data;
            });
        } else {
            return response.text().then(text => {
                console.error('Non-JSON delete response:', text.substring(0, 500));
                throw new Error('Сервер вернул неверный формат ответа');
            });
        }
    })
    .then(data => {
        if (data.success) {
            showSuccess('Тип вакцинации успешно удален');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            // Проверяем, является ли это ошибкой зависимостей
            if (data.message && data.message.includes('связаны')) {
                showWarning(data.message);
            } else {
                showError(data.message || 'Ошибка при удалении');
            }
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        // Проверяем, является ли это ошибкой зависимостей
        if (error.message && error.message.includes('связаны')) {
            showWarning(error.message);
        } else {
            showError('Ошибка при удалении');
        }
    });
}

// Инициализация TomSelect при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем все существующие селекты препаратов
    document.querySelectorAll('.drug-select').forEach(select => {
        if (!select.tomselect) {
            initTomSelectForDrug(select);
        }
    });
});
</script>
@endsection
