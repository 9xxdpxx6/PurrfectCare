@extends('layouts.admin')

@section('title', 'Филиалы')

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
    <h1 class="h2">Филиалы - {{ $branches->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить филиал
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
                            <a href="{{ route('admin.settings.branches.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($branches as $branch)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif" data-id="{{ $branch->id }}" data-original="{{ json_encode(['name' => $branch->name, 'address' => $branch->address, 'phone' => $branch->phone, 'opens_at' => $branch->opens_at, 'closes_at' => $branch->closes_at]) }}">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $branch->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Адрес:</span> {{ $branch->address }}
                            </div>
                            @if($branch->phone)
                                <div class="text-muted mb-2">
                                    <span>Телефон:</span> {{ $branch->phone }}
                                </div>
                            @endif
                            @if($branch->opens_at || $branch->closes_at)
                                <div class="text-muted">
                                    <span>Время работы:</span> 
                                    @if($branch->opens_at && $branch->closes_at)
                                        {{ $branch->opens_at->format('H:i') }} - {{ $branch->closes_at->format('H:i') }}
                                    @elseif($branch->opens_at)
                                        с {{ $branch->opens_at->format('H:i') }}
                                    @elseif($branch->closes_at)
                                        до {{ $branch->closes_at->format('H:i') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="d-none edit-fields">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="{{ $branch->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Адрес</label>
                                <input type="text" class="form-control" value="{{ $branch->address }}" 
                                       data-field="address" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Телефон</label>
                                <input type="text" class="form-control" value="{{ $branch->phone }}" 
                                       data-field="phone" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время открытия</label>
                                <input type="text" class="form-control time-input" value="{{ $branch->opens_at ? $branch->opens_at->format('H:i') : '' }}" 
                                       data-field="opens_at" data-type="opens_at" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время закрытия</label>
                                <input type="text" class="form-control time-input" value="{{ $branch->closes_at ? $branch->closes_at->format('H:i') : '' }}" 
                                       data-field="closes_at" data-type="closes_at" onchange="markAsChanged(this)">
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
                        <button type="button" class="btn btn-outline-danger" onclick="deleteRow({{ $branch->id }})">
                            <span class="d-none d-lg-inline-block">Удалить</span>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($branches->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-building display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Филиалы не найдены</h3>
        <p class="text-muted">Добавьте новый филиал.</p>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить филиал
        </button>
    </div>
@endif

@if($branches->hasPages())
    <div class="mt-4">
        {{ $branches->links() }}
    </div>
@endif

@endsection

@push('scripts')
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
window.hasFilledFields = function(card) {
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
        if (value && value.trim() !== '') {
            return true;
        }
    }
    
    return false;
}

window.getAddCard = function() {
    // Ищем карточку добавления (без data-id или с data-id="new")
    const cards = document.querySelectorAll('.card');
    for (let card of cards) {
        if (!card.dataset.id || card.dataset.id === 'new') {
            return card;
        }
    }
    return null;
}

window.closeAllCards = function() {
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
        }
    });
    
    // Удаляем карточки добавления без заполненных полей
    const addCard = window.getAddCard();
    if (addCard && !window.hasFilledFields(addCard)) {
        addCard.closest('.col-12').remove();
    }
}

window.closeAllEditCards = function() {
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
            }
        }
    });
}

let hasChanges = false;
let changedRows = new Set();
let timePickers = new Map();

document.addEventListener('DOMContentLoaded', function () {

    // Инициализация Air Datepicker для полей времени
    window.initTimePicker = function(input) {
        const type = input.dataset.type;
        const pickerKey = `${type}_${input.closest('.card').dataset.id || 'new'}`;

        if (timePickers.has(pickerKey)) {
            timePickers.get(pickerKey).destroy();
        }

        const picker = createDatepicker(input, {
            timepicker: true,
            onlyTimepicker: true,
            timeFormat: 'HH:mm',
            startDate: new Date(new Date().setHours(type === 'opens_at' ? 9 : 18, 0, 0, 0)),
            onSelect: function({date, datepicker}) {
                markAsChanged(input);
            }
        });

        timePickers.set(pickerKey, picker);
    }

    // Инициализация всех полей времени
    function initAllTimePickers() {
        document.querySelectorAll('.time-input').forEach(input => {
            initTimePicker(input);
        });
    }

    // Инициализация при загрузке страницы
    initAllTimePickers();

    window.markAsChanged = function(input) {
        const card = input.closest('.card');
        const rowId = card ? card.dataset.id : null;
        
        if (rowId) {
            changedRows.add(rowId);
        } else {
            // New row
            hasChanges = true;
        }
        
        hasChanges = true;
    }



    window.toggleEdit = function(button) {
        // Проверяем, есть ли карточка добавления с заполненными полями
        const addCard = window.getAddCard();
        if (addCard && window.hasFilledFields(addCard)) {
            showWarning('Завершите заполнение карточки добавления перед редактированием');
            return;
        }
        
        // Закрываем все карточки (включая карточки добавления)
        window.closeAllCards();
        
        const card = button.closest('.card');
        const editFields = card.querySelector('.edit-fields');
        const editBtn = card.querySelector('.edit-btn');
        const saveBtn = card.querySelector('.save-btn');
        const cancelBtn = card.querySelector('.cancel-btn');
        
        editFields.classList.remove('d-none');
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    }

    window.saveRow = function(button) {
        const card = button.closest('.card');
        const rowId = card.dataset.id;
        
        if (rowId) {
            const data = {};
            card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
                data[input.dataset.field] = input.value;
            });
            
            // Проверяем обязательные поля
            if (!data.name || !data.name.trim()) {
                showWarning('Название филиала обязательно для заполнения');
                return;
            }
            
            if (!data.address || !data.address.trim()) {
                showWarning('Адрес филиала обязателен для заполнения');
                return;
            }
            
            // Проверяем обязательность времени работы
            const opensTime = data.opens_at ? data.opens_at.trim() : '';
            const closesTime = data.closes_at ? data.closes_at.trim() : '';
            
            if (!opensTime) {
                showWarning('Время открытия обязательно для заполнения');
                return;
            }
            
            if (!closesTime) {
                showWarning('Время закрытия обязательно для заполнения');
                return;
            }
            
            // Проверяем логику времени работы
            if (opensTime && closesTime) {
                const opens = new Date(`2000-01-01 ${opensTime}`);
                const closes = new Date(`2000-01-01 ${closesTime}`);
                
                if (opens >= closes) {
                    showWarning('Время закрытия должно быть позже времени открытия');
                    return;
                }
            }
            
            fetch(`{{ route('admin.settings.branches.update', '') }}/${rowId}`, {
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
                    showSuccess('Филиал успешно обновлен');
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

    window.cancelEdit = function(button) {
        const card = button.closest('.card');
        const editFields = card.querySelector('.edit-fields');
        const editBtn = card.querySelector('.edit-btn');
        const saveBtn = card.querySelector('.save-btn');
        const cancelBtn = card.querySelector('.cancel-btn');
        
        editFields.classList.add('d-none');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }

    window.addNewRow = function() {
        // Проверяем, есть ли уже карточка добавления
        const existingAddCard = window.getAddCard();
        if (existingAddCard) {
            // Если есть карточка с заполненными полями, показываем предупреждение
            if (window.hasFilledFields(existingAddCard)) {
                showWarning('Завершите заполнение текущей карточки добавления');
                return;
            } else {
                // Если поля пустые, удаляем старую карточку
                existingAddCard.closest('.col-12').remove();
            }
        }
        
        // Закрываем все карточки редактирования
        window.closeAllEditCards();
        
        const container = document.querySelector('.row.g-3');
        const newCard = document.createElement('div');
        newCard.className = 'col-12';
        newCard.innerHTML = `
            <div class="card h-100 border-0 border-bottom shadow-sm bg-body-tertiary">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start d-none">
                        <h5 class="card-title">Новый филиал</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Адрес:</span> <span class="address-display">Не указан</span>
                            </div>
                            <div class="text-muted mb-2">
                                <span>Телефон:</span> <span class="phone-display">Не указан</span>
                            </div>
                            <div class="text-muted">
                                <span>Время работы:</span> <span class="work-hours-display">Не указано</span>
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
                                <label class="form-label small text-muted">Адрес</label>
                                <input type="text" class="form-control" value="" 
                                       data-field="address" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Телефон</label>
                                <input type="text" class="form-control" value="" 
                                       data-field="phone" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время открытия</label>
                                <input type="text" class="form-control time-input" value="" 
                                       data-field="opens_at" data-type="opens_at" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время закрытия</label>
                                <input type="text" class="form-control time-input" value="" 
                                       data-field="closes_at" data-type="closes_at" onchange="markAsChanged(this)">
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
        
        // Инициализируем таймпикеры для новых полей
        newCard.querySelectorAll('.time-input').forEach(input => {
            initTimePicker(input);
        });
        
        hasChanges = true;
    }

    window.saveNewRow = function(button) {
        const card = button.closest('.card');
        const data = {};
        card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
            data[input.dataset.field] = input.value;
        });
        
        // Проверяем обязательные поля
        if (!data.name || !data.name.trim()) {
            showWarning('Название филиала обязательно для заполнения');
            return;
        }
        
        if (!data.address || !data.address.trim()) {
            showWarning('Адрес филиала обязателен для заполнения');
            return;
        }
        
        // Проверяем обязательность времени работы
        const opensTime = data.opens_at ? data.opens_at.trim() : '';
        const closesTime = data.closes_at ? data.closes_at.trim() : '';
        
        if (!opensTime) {
            showWarning('Время открытия обязательно для заполнения');
            return;
        }
        
        if (!closesTime) {
            showWarning('Время закрытия обязательно для заполнения');
            return;
        }
        
        // Проверяем логику времени работы
        if (opensTime && closesTime) {
            const opens = new Date(`2000-01-01 ${opensTime}`);
            const closes = new Date(`2000-01-01 ${closesTime}`);
            
            if (opens >= closes) {
                showWarning('Время закрытия должно быть позже времени открытия');
                return;
            }
        }
        
        fetch("{{ route('admin.settings.branches.store') }}", {
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
                showSuccess('Филиал успешно создан');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                            } else {
                    showWarning(data.message || 'Ошибка при создании филиала');
                }
        })
        .catch(error => {
            console.error('Error:', error);
            showWarning('Произошла ошибка при создании филиала: ' + error.message);
        });
    }

    window.removeNewRow = function(button) {
        const card = button.closest('.col-12');
        card.remove();
    }

    window.deleteRow = function(id) {
        if (confirm('Вы уверены, что хотите удалить этот филиал?')) {
            fetch(`{{ route('admin.settings.branches.destroy', '') }}/${id}`, {
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
                    card.closest('.col-12').remove();
                    changedRows.delete(id.toString());
                    showSuccess('Филиал успешно удален');
                } else {
                    showError(data.message || 'Ошибка при удалении филиала');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Произошла ошибка при удалении филиала: ' + error.message);
            });
        }
    }

    // Функция для совместимости с существующим кодом
    window.showNotification = function(message, type) {
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
});
</script>
@endpush 
