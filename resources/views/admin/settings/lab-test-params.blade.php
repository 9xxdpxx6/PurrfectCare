@extends('layouts.admin')

@section('title', 'Параметры анализов')

@push('styles')
<style>
    /* Минимальные стили для TomSelect */
    .lab-test-type-select,
    .unit-select {
        min-height: 38px;
    }
    
    /* Унификация размеров всех элементов */
    .edit-fields .form-control,
    .edit-fields .form-select {
        height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
    }
    
    .edit-fields .btn {
        height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        display: flex;
        align-items: center;
        justify-content: center;
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
    <h1 class="h2">Параметры анализов - {{ $labTestParams->total() }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить параметр
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
                            <a href="{{ route('admin.lab-tests.params.index') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($labTestParams as $param)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif" data-id="{{ $param->id }}" data-original="{{ json_encode(['name' => $param->name, 'lab_test_type_id' => $param->lab_test_type_id, 'unit_id' => $param->unit_id, 'description' => $param->description]) }}">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $param->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Тип анализа:</span> {{ $param->labTestType->name ?? 'Не указан' }}
                            </div>
                            <div class="text-muted mb-2">
                                <span>Единица измерения:</span> 
                                @if($param->unit)
                                    {{ $param->unit->name }}
                                    @if($param->unit->symbol)
                                        ({{ $param->unit->symbol }})
                                    @endif
                                @else
                                    Без единицы
                                @endif
                            </div>
                            @if($param->description)
                                <div class="text-muted">
                                    <span>Описание:</span> {{ $param->description }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="d-none edit-fields">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="{{ $param->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Тип анализа</label>
                                <select class="form-select lab-test-type-select" data-field="lab_test_type_id" onchange="markAsChanged(this)">
                                    @if($param->lab_test_type_id)
                                        <option value="{{ $param->lab_test_type_id }}" selected>{{ $param->labTestType->name ?? 'Тип анализа' }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Единица измерения</label>
                                <select class="form-select unit-select" data-field="unit_id" onchange="markAsChanged(this)">
                                    @if($param->unit_id)
                                        <option value="{{ $param->unit_id }}" selected>
                                            {{ $param->unit->name ?? 'Единица' }}
                                            @if($param->unit && $param->unit->symbol)
                                                ({{ $param->unit->symbol }})
                                            @endif
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Описание</label>
                                <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)">{{ $param->description }}</textarea>
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
                        <button type="button" class="btn btn-outline-danger" title="Удалить" onclick="deleteRow(this)" data-param-id="{{ $param->id }}">
                            <span class="d-none d-lg-inline-block">Удалить</span>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($labTestParams->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-clipboard-data display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Параметры анализов не найдены</h3>
        <p class="text-muted">Добавьте новый параметр анализа.</p>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить параметр
        </button>
    </div>
@endif

@if($labTestParams->hasPages())
    <div class="mt-4">
        {{ $labTestParams->links() }}
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
        if (value && value.trim() !== '') {
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
        }
    });
    
    // Удаляем карточки добавления без заполненных полей
    const addCard = getAddCard();
    if (addCard && !hasFilledFields(addCard)) {
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
            }
        }
    });
}

let hasChanges = false;
let changedRows = new Set();

    function markAsChanged(input) {
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
        
        // Инициализируем TomSelect для селектов
        initTomSelects(card);
    }

    function saveRow(button) {
        const card = button.closest('.card');
        const rowId = card.dataset.id;
        
        if (rowId) {
            const data = {};
            card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
                data[input.dataset.field] = input.value;
            });
            
            // Проверяем обязательные поля
            if (!data.name || !data.name.trim()) {
                showWarning('Название параметра обязательно для заполнения');
                return;
            }
            if (!data.lab_test_type_id || !data.lab_test_type_id.trim()) {
                showWarning('Тип анализа обязателен для выбора');
                return;
            }
            
            fetch(`{{ route('admin.lab-tests.params.update', '') }}/${rowId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
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
                    showSuccess('Параметр успешно обновлен');
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
        newCard.innerHTML = `
            <div class="card h-100 border-0 border-bottom shadow-sm bg-body-tertiary">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start d-none">
                        <h5 class="card-title">Новый параметр</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Тип анализа:</span> <span class="lab-test-type-display">Не выбран</span>
                            </div>
                            <div class="text-muted mb-2">
                                <span>Единица измерения:</span> <span class="unit-display">Не выбрана</span>
                            </div>
                            <div class="text-muted">
                                <span>Описание:</span> <span class="description-display">Не указано</span>
                            </div>
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="edit-fields">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control" value="" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Тип анализа</label>
                                <select class="form-select lab-test-type-select" data-field="lab_test_type_id" onchange="markAsChanged(this)">
                                    <option value="">Выберите тип анализа</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Единица измерения</label>
                                <select class="form-select unit-select" data-field="unit_id" onchange="markAsChanged(this)">
                                    <option value="">Без единицы</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Описание</label>
                                <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0 text-nowrap">
                        <button type="button" class="btn btn-outline-success save-btn" title="Сохранить" onclick="saveNewRow(this)">
                            <span class="d-none d-lg-inline-block">Сохранить</span>
                            <i class="bi bi-check"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary cancel-btn" title="Отменить" onclick="removeNewRow(this)">
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
        initTomSelects(newCard);
        
        hasChanges = true;
    }

    function saveNewRow(button) {
        const card = button.closest('.card');
        const data = {};
        card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
            data[input.dataset.field] = input.value;
        });
        
        // Проверяем обязательные поля
        if (!data.name || !data.name.trim()) {
            showWarning('Название параметра обязательно для заполнения');
            return;
        }
        if (!data.lab_test_type_id || !data.lab_test_type_id.trim()) {
            showWarning('Тип анализа обязателен для выбора');
            return;
        }
        
        fetch("{{ route('admin.lab-tests.params.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
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
                showSuccess('Параметр успешно создан');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showWarning(data.message || 'Ошибка при создании параметра');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showWarning('Произошла ошибка при создании параметра: ' + error.message);
        });
    }

    function removeNewRow(button) {
        const card = button.closest('.col-12');
        card.remove();
    }

    function deleteRow(button) {
        const id = button.dataset.paramId;
        if (confirm('Вы уверены, что хотите удалить этот параметр?')) {
            fetch(`{{ route('admin.lab-tests.params.destroy', '') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
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
                    showSuccess('Параметр успешно удален');
                } else {
                    showWarning(data.message || 'Ошибка при удалении параметра');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWarning('Произошла ошибка при удалении параметра: ' + error.message);
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

    // Функции для работы с TomSelect
    function initTomSelects(container) {
        if (!container) return;
        
        // Инициализируем селект для типа анализа
        const labTestTypeSelect = container.querySelector('.lab-test-type-select');
        if (labTestTypeSelect && !labTestTypeSelect.tomselect) {
            initLabTestTypeSelect(labTestTypeSelect);
        }
        
        // Инициализируем селект для единицы измерения
        const unitSelect = container.querySelector('.unit-select');
        if (unitSelect && !unitSelect.tomselect) {
            initUnitSelect(unitSelect);
        }
    }

    function initLabTestTypeSelect(select) {
        const selectedValue = select.value || '';
        const selectedText = select.selectedIndex >= 0 ? select.options[select.selectedIndex].text : '';
        
        const tomSelect = createTomSelect(select, {
            placeholder: 'Поиск типа анализа...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            maxOptions: 50,
            maxItems: 1,
            load: function(query, callback) {
                let url = '{{ route("admin.lab-tests.lab-test-type-options") }}?q=' + encodeURIComponent(query || '');
                
                if (selectedValue && !query) {
                    url += '&selected=' + encodeURIComponent(selectedValue);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onChange: function() {
                if (this.input && typeof markAsChanged === 'function') {
                    markAsChanged(this.input);
                }
            }
        });
        
        if (selectedValue && selectedText && selectedText !== 'Выберите тип анализа') {
            try {
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

    function initUnitSelect(select) {
        const selectedValue = select.value || '';
        const selectedText = select.selectedIndex >= 0 ? select.options[select.selectedIndex].text : '';
        
        const tomSelect = createTomSelect(select, {
            placeholder: 'Поиск единицы измерения...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: true,
            preload: true,
            maxOptions: 50,
            maxItems: 1,
            load: function(query, callback) {
                let url = "{{ route('admin.settings.unit-options') }}?q=" + encodeURIComponent(query || '');
                
                if (selectedValue && !query) {
                    url += '&selected=' + encodeURIComponent(selectedValue);
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        // Форматируем текст для отображения с символом
                        const formattedOptions = json.map(option => ({
                            value: option.value,
                            text: option.symbol ? `${option.text} (${option.symbol})` : option.text
                        }));
                        callback(formattedOptions);
                    })
                    .catch(() => callback());
            },
            onChange: function() {
                if (this.input && typeof markAsChanged === 'function') {
                    markAsChanged(this.input);
                }
            }
        });
        
        if (selectedValue && selectedText && selectedText !== 'Без единицы') {
            try {
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

    // Инициализация TomSelect при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Инициализируем все существующие селекты
        document.querySelectorAll('.lab-test-type-select, .unit-select').forEach(select => {
            if (!select.tomselect) {
                if (select.classList.contains('lab-test-type-select')) {
                    initLabTestTypeSelect(select);
                } else if (select.classList.contains('unit-select')) {
                    initUnitSelect(select);
                }
            }
        });
    });
</script>
@endpush 