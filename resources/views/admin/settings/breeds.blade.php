@extends('layouts.admin')

@section('title', 'Породы')

@push('styles')
<style>
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
    
    /* Стили для TomSelect - выравнивание отступов */
    .ts-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .ts-control {
        margin: 0 !important;
        padding: 0.375rem 0.75rem !important;
        border-radius: 0.375rem !important;
        border: 1px solid #ced4da !important;
        background-color: #fff !important;
        min-height: 38px !important;
    }
    
    .ts-dropdown {
        margin: 0 !important;
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
    <h1 class="h2">Породы - {{ $breeds->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить породу
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
                            <a href="{{ route('admin.settings.breeds.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($breeds as $breed)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif" data-id="{{ $breed->id }}" data-original="{{ json_encode(['name' => $breed->name, 'species_id' => $breed->species_id, 'description' => $breed->description]) }}">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $breed->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Вид животного:</span> {{ $breed->species->name ?? 'Не указан' }}
                            </div>
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="d-none edit-fields">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="{{ $breed->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Вид животного</label>
                                <select class="form-control" data-field="species_id" data-tomselect onchange="markAsChanged(this)">
                                    @foreach($species as $speciesItem)
                                        <option value="{{ $speciesItem->id }}" {{ $breed->species_id == $speciesItem->id ? 'selected' : '' }}>
                                            {{ $speciesItem->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                        <button type="button" class="btn btn-outline-warning edit-btn" onclick="toggleEdit(this)">
                            <span class="d-none d-lg-inline-block">Редактировать</span>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success save-btn d-none" onclick="saveRow(this)">
                            <span class="d-none d-lg-inline-block">Сохранить</span>
                            <i class="bi bi-check"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary cancel-btn d-none" onclick="cancelEdit(this)">
                            <span class="d-none d-lg-inline-block">Отменить</span>
                            <i class="bi bi-x"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteRow({{ $breed->id }})">
                            <span class="d-none d-lg-inline-block">Удалить</span>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($breeds->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-gitlab display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Породы не найдены</h3>
        <p class="text-muted">Добавьте новую породу.</p>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить породу
        </button>
    </div>
@endif

@if($breeds->hasPages())
    <div class="mt-4">
        {{ $breeds->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script>
// Инициализация TomSelect для всех селектов с атрибутом data-tomselect
document.addEventListener('DOMContentLoaded', function() {
    initializeTomSelects();
});

function initializeTomSelects() {
    const tomSelectElements = document.querySelectorAll('select[data-tomselect]');
    tomSelectElements.forEach(element => {
        if (!element.tomselect) {
            const tomSelect = window.createTomSelect(element, {
                placeholder: 'Выберите вид животного...',
                plugins: ['remove_button'],
                allowEmptyOption: true,
                maxOptions: 30,
                persist: false
            });
            
            // Добавляем обработчик изменения для обновления отображения вида животного
            tomSelect.on('change', function(value) {
                const card = element.closest('.card');
                if (card) {
                    const speciesDisplay = card.querySelector('.species-display');
                    if (speciesDisplay) {
                        if (value && value !== '') {
                            const selectedOption = element.querySelector(`option[value="${value}"]`);
                            if (selectedOption) {
                                speciesDisplay.textContent = selectedOption.textContent;
                            }
                        } else {
                            speciesDisplay.textContent = 'Не выбран';
                        }
                    }
                }
            });
        }
    });
}

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

// Функции для управления карточками
function hasFilledFields(card) {
    // Проверяем обычные поля
    const inputs = card.querySelectorAll('input[data-field], textarea[data-field]');
    for (let input of inputs) {
        if (input.value && input.value.trim() !== '' && input.value !== '0') {
            return true;
        }
    }
    
    // Проверяем селекты (включая TomSelect)
    const selects = card.querySelectorAll('select[data-field]');
    for (let select of selects) {
        let value = '';
        if (select.tomselect) {
            value = select.tomselect.getValue();
        } else {
            value = select.value;
        }
        if (value && value.toString().trim() !== '' && value !== '0') {
            return true;
        }
    }
    
    return false;
}

function getAddCard() {
    // Ищем карточку добавления (без data-id или с data-id="new")
    const cards = document.querySelectorAll('.card');
    for (let card of cards) {
        if (!card.dataset.id || card.dataset.id === 'new') {
            return card;
        }
    }
    return null;
}

function closeAllCards() {
    // Закрываем все карточки редактирования
    document.querySelectorAll('.card').forEach(card => {
        const editFields = card.querySelector('.edit-fields');
        const editBtn = card.querySelector('.edit-btn');
        const saveBtn = card.querySelector('.save-btn');
        const cancelBtn = card.querySelector('.cancel-btn');
        
        if (editFields && !editFields.classList.contains('d-none')) {
            editFields.classList.add('d-none');
            if (editBtn) editBtn.classList.remove('d-none');
            if (saveBtn) saveBtn.classList.add('d-none');
            if (cancelBtn) cancelBtn.classList.add('d-none');
            
            // Уничтожаем TomSelect при закрытии карточки
            const editSelect = card.querySelector('select[data-tomselect]');
            if (editSelect && editSelect.tomselect) {
                editSelect.tomselect.destroy();
            }
        }
    });
    
    // Удаляем карточки добавления без заполненных полей
    const addCard = getAddCard();
    if (addCard && !hasFilledFields(addCard)) {
        // Уничтожаем TomSelect перед удалением карточки
        const editSelect = addCard.querySelector('select[data-tomselect]');
        if (editSelect && editSelect.tomselect) {
            editSelect.tomselect.destroy();
        }
        addCard.closest('.col-12').remove();
    }
}

function closeAllEditCards() {
    // Закрываем только карточки редактирования (не трогаем карточки добавления)
    document.querySelectorAll('.card[data-id]').forEach(card => {
        if (card.dataset.id && card.dataset.id !== 'new') {
            const editFields = card.querySelector('.edit-fields');
            const editBtn = card.querySelector('.edit-btn');
            const saveBtn = card.querySelector('.save-btn');
            const cancelBtn = card.querySelector('.cancel-btn');
            
            if (editFields && !editFields.classList.contains('d-none')) {
                editFields.classList.add('d-none');
                if (editBtn) editBtn.classList.remove('d-none');
                if (saveBtn) saveBtn.classList.add('d-none');
                if (cancelBtn) cancelBtn.classList.add('d-none');
                
                // Уничтожаем TomSelect при закрытии карточки
                const editSelect = card.querySelector('select[data-tomselect]');
                if (editSelect && editSelect.tomselect) {
                    editSelect.tomselect.destroy();
                }
            }
        }
    });
}

let hasChanges = false;
let changedRows = new Set();

    function markAsChanged(input) {
        const card = input.closest('.card');
        const rowId = card ? card.dataset.id : null;
        
        // Если это селект с TomSelect, получаем значение через TomSelect API
        if (input.tagName === 'SELECT' && input.tomselect) {
            input.value = input.tomselect.getValue();
        }
        
        if (rowId) {
            changedRows.add(rowId);
        } else {
            // New row
            hasChanges = true;
        }
        
        hasChanges = true;
    }

    function closeAllEditCards() {
        document.querySelectorAll('.card').forEach(card => {
            const editFields = card.querySelector('.edit-fields');
            const editBtn = card.querySelector('.edit-btn');
            const saveBtn = card.querySelector('.save-btn');
            const cancelBtn = card.querySelector('.cancel-btn');
            
            if (editFields && !editFields.classList.contains('d-none')) {
                editFields.classList.add('d-none');
                editBtn.classList.remove('d-none');
                saveBtn.classList.add('d-none');
                cancelBtn.classList.add('d-none');
            }
        });
    }

    function toggleEdit(button) {
        // Проверяем, есть ли карточка добавления с заполненными полями
        const addCard = getAddCard();
        if (addCard && hasFilledFields(addCard)) {
            showWarning('Завершите заполнение карточки добавления перед редактированием');
            return;
        }
        
        // Закрываем все карточки (включая карточки добавления)
        closeAllCards();
        
        const card = button.closest('.card');
        const editFields = card.querySelector('.edit-fields');
        const editBtn = card.querySelector('.edit-btn');
        const saveBtn = card.querySelector('.save-btn');
        const cancelBtn = card.querySelector('.cancel-btn');
        
        editFields.classList.remove('d-none');
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
        
        // Инициализируем TomSelect для полей редактирования
        const editSelect = card.querySelector('select[data-tomselect]');
        if (editSelect && !editSelect.tomselect) {
            const tomSelect = window.createTomSelect(editSelect, {
                placeholder: 'Выберите вид животного...',
                plugins: ['remove_button'],
                allowEmptyOption: true,
                maxOptions: 30,
                persist: false
            });
            
            // Добавляем обработчик изменения для обновления отображения вида животного
            tomSelect.on('change', function(value) {
                const typeDisplay = card.querySelector('.text-muted');
                if (typeDisplay) {
                    if (value && value !== '') {
                        const selectedOption = editSelect.querySelector(`option[value="${value}"]`);
                        if (selectedOption) {
                            typeDisplay.innerHTML = `<span>Вид животного:</span> ${selectedOption.textContent}`;
                        }
                    } else {
                        typeDisplay.innerHTML = '<span>Вид животного:</span> Не указан';
                    }
                }
            });
        }
    }

    function saveRow(button) {
        const card = button.closest('.card');
        const rowId = card.dataset.id;
        
        if (rowId) {
            const data = {};
            card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
                let value = input.value;
                // Если это селект с TomSelect, получаем значение через TomSelect API
                if (input.tagName === 'SELECT' && input.tomselect) {
                    value = input.tomselect.getValue();
                }
                data[input.dataset.field] = value;
            });
            
            // Проверяем обязательные поля
            if (!data.name || !data.name.trim()) {
                showWarning('Название породы обязательно для заполнения');
                return;
            }
            
            if (!data.species_id || !data.species_id.trim()) {
                showWarning('Вид животного обязательно для выбора');
                return;
            }
            
            fetch(`{{ route('admin.settings.breeds.update', '') }}/${rowId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({...data, _method: 'PATCH'})
            })
            .then(response => {
                if (!response.ok) {
                    // Проверяем, является ли ответ JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                        });
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccess('Порода успешно обновлена');
                    // Уничтожаем TomSelect перед перезагрузкой
                    const editSelect = card.querySelector('select[data-tomselect]');
                    if (editSelect && editSelect.tomselect) {
                        editSelect.tomselect.destroy();
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showWarning(data.message || 'Ошибка при сохранении');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWarning('Ошибка при сохранении: ' + error.message);
            });
        }
    }

    function cancelEdit(button) {
        const card = button.closest('.card');
        const editFields = card.querySelector('.edit-fields');
        const editBtn = card.querySelector('.edit-btn');
        const saveBtn = card.querySelector('.save-btn');
        const cancelBtn = card.querySelector('.cancel-btn');
        
        editFields.classList.add('d-none');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
        
        // Уничтожаем TomSelect при отмене редактирования
        const editSelect = card.querySelector('select[data-tomselect]');
        if (editSelect && editSelect.tomselect) {
            editSelect.tomselect.destroy();
        }
    }

    function addNewRow() {
        // Проверяем, есть ли уже карточка добавления
        const existingAddCard = getAddCard();
        if (existingAddCard) {
            // Если есть карточка с заполненными полями, показываем предупреждение
            if (hasFilledFields(existingAddCard)) {
                showWarning('Завершите заполнение текущей карточки добавления');
                return;
            } else {
                // Если поля пустые, удаляем старую карточку
                existingAddCard.closest('.col-12').remove();
            }
        }
        
        // Закрываем все карточки редактирования
        closeAllEditCards();
        
        const container = document.querySelector('.row.g-3');
        const newCard = document.createElement('div');
        newCard.className = 'col-12';
        
        // Create species options HTML
        let speciesOptions = '';
        @foreach($species as $speciesItem)
            speciesOptions += `<option value="{{ $speciesItem->id }}">{{ $speciesItem->name }}</option>`;
        @endforeach
        
        newCard.innerHTML = `
            <div class="card h-100 border-0 border-bottom shadow-sm bg-body-tertiary">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start d-none">
                        <h5 class="card-title">Новая порода</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Вид животного:</span> <span class="species-display">Не выбран</span>
                            </div>
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="edit-fields">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Вид животного</label>
                                <select class="form-control" data-field="species_id" data-tomselect onchange="markAsChanged(this)">
                                    <option value="">Выберите вид животного</option>
                                    ${speciesOptions}
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                        <button type="button" class="btn btn-outline-success save-btn" onclick="saveNewRow(this)">
                            <span class="d-none d-lg-inline-block">Сохранить</span>
                            <i class="bi bi-check"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary cancel-btn" onclick="removeNewRow(this)">
                            <span class="d-none d-lg-inline-block">Отменить</span>
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Добавляем карточку в начало списка
        const firstCard = container.querySelector('.col-12');
        if (firstCard) {
            container.insertBefore(newCard, firstCard);
        } else {
            container.appendChild(newCard);
        }
        
        // Инициализируем TomSelect для новой карточки
        const newSelect = newCard.querySelector('select[data-tomselect]');
        if (newSelect) {
            const tomSelect = window.createTomSelect(newSelect, {
                placeholder: 'Выберите вид животного...',
                plugins: ['remove_button'],
                allowEmptyOption: true,
                maxOptions: 30,
                persist: false
            });
            
            // Добавляем обработчик изменения для обновления отображения вида животного
            tomSelect.on('change', function(value) {
                const speciesDisplay = newCard.querySelector('.species-display');
                if (speciesDisplay) {
                    if (value && value !== '') {
                        const selectedOption = newSelect.querySelector(`option[value="${value}"]`);
                        if (selectedOption) {
                            speciesDisplay.textContent = selectedOption.textContent;
                        }
                    } else {
                        speciesDisplay.textContent = 'Не выбран';
                    }
                }
            });
        }
        
        hasChanges = true;
    }

    function saveNewRow(button) {
        const card = button.closest('.card');
        const data = {};
        card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
            let value = input.value;
            // Если это селект с TomSelect, получаем значение через TomSelect API
            if (input.tagName === 'SELECT' && input.tomselect) {
                value = input.tomselect.getValue();
            }
            data[input.dataset.field] = value;
        });
        
        // Проверяем обязательные поля
        if (!data.name || !data.name.trim()) {
            showWarning('Название породы обязательно для заполнения');
            return;
        }
        
        if (!data.species_id || !data.species_id.trim()) {
            showWarning('Вид животного обязательно для выбора');
            return;
        }
        
        fetch('{{ route('admin.settings.breeds.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                // Проверяем, является ли ответ JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                    });
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSuccess('Порода успешно создана');
                // Уничтожаем TomSelect перед перезагрузкой
                const editSelect = card.querySelector('select[data-tomselect]');
                if (editSelect && editSelect.tomselect) {
                    editSelect.tomselect.destroy();
                }
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showWarning(data.message || 'Ошибка при создании породы');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showWarning('Произошла ошибка при создании породы: ' + error.message);
        });
    }

    function removeNewRow(button) {
        const card = button.closest('.col-12');
        // Уничтожаем TomSelect перед удалением карточки
        const editSelect = card.querySelector('select[data-tomselect]');
        if (editSelect && editSelect.tomselect) {
            editSelect.tomselect.destroy();
        }
        card.remove();
    }

    function deleteRow(id) {
        if (confirm('Вы уверены, что хотите удалить эту породу?')) {
            fetch(`{{ route('admin.settings.breeds.destroy', '') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(response => {
                if (!response.ok) {
                    // Проверяем, является ли ответ JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                        });
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    // Уничтожаем TomSelect перед удалением карточки
                    const editSelect = card.querySelector('select[data-tomselect]');
                    if (editSelect && editSelect.tomselect) {
                        editSelect.tomselect.destroy();
                    }
                    card.closest('.col-12').remove();
                    changedRows.delete(id.toString());
                    showSuccess('Порода успешно удалена');
                } else {
                    showWarning(data.message || 'Ошибка при удалении породы');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWarning('Произошла ошибка при удалении породы: ' + error.message);
            });
        }
    }

    // Функция для совместимости с существующим кодом
    function showNotification(message, type) {
        // Используем новую функцию createToast напрямую
        if (type === 'error') {
            createToast(message, 'error', 'Ошибка');
        } else if (type === 'success') {
            createToast(message, 'success', 'Успешно');
        } else if (type === 'warning') {
            createToast(message, 'warning', 'Предупреждение');
        } else {
            createToast(message, 'info', 'Информация');
        }
    }
</script>
@endpush 