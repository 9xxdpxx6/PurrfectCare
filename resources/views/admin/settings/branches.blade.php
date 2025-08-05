@extends('layouts.admin')

@section('title', 'Филиалы')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Филиалы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить филиал
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
            <a href="{{ route('admin.settings.branches') }}" class="btn btn-outline-secondary">
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
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Адрес</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->address }}" 
                                       data-field="address" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Телефон</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->phone }}" 
                                       data-field="phone" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время открытия</label>
                                <input type="text" class="form-control form-control-sm time-input" value="{{ $branch->opens_at ? $branch->opens_at->format('H:i') : '' }}" 
                                       data-field="opens_at" data-type="opens_at" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время закрытия</label>
                                <input type="text" class="form-control form-control-sm time-input" value="{{ $branch->closes_at ? $branch->closes_at->format('H:i') : '' }}" 
                                       data-field="closes_at" data-type="closes_at" onchange="markAsChanged(this)">
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
                        <button type="button" class="btn btn-outline-danger" title="Удалить" onclick="deleteRow({{ $branch->id }})">
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

    window.closeAllEditCards = function() {
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

    window.toggleEdit = function(button) {
        // Закрываем все другие карточки
        closeAllEditCards();
        
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
            
            fetch(`{{ route('admin.settings.branches.update', '') }}/${rowId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    showNotification('Ошибка при сохранении', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка при сохранении', 'error');
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
        // Закрываем все другие карточки
        closeAllEditCards();
        
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
                                <input type="text" class="form-control form-control-sm" value="" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Адрес</label>
                                <input type="text" class="form-control form-control-sm" value="" 
                                       data-field="address" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Телефон</label>
                                <input type="text" class="form-control form-control-sm" value="" 
                                       data-field="phone" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время открытия</label>
                                <input type="text" class="form-control form-control-sm time-input" value="" 
                                       data-field="opens_at" data-type="opens_at" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Время закрытия</label>
                                <input type="text" class="form-control form-control-sm time-input" value="" 
                                       data-field="closes_at" data-type="closes_at" onchange="markAsChanged(this)">
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
        
        fetch("{{ route('admin.settings.branches.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Ошибка при создании филиала');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при создании филиала');
        });
    }

    window.removeNewRow = function(button) {
        const card = button.closest('.col-12');
        card.remove();
    }

    window.deleteRow = function(id) {
        if (confirm('Вы уверены, что хотите удалить этот филиал?')) {
            fetch(`{{ route('admin.settings.branches.destroy', '') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-id="${id}"]`);
                    card.closest('.col-12').remove();
                    changedRows.delete(id.toString());
                } else {
                    alert(data.message || 'Ошибка при удалении филиала');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении филиала');
            });
        }
    }

    window.showNotification = function(message, type) {
        // Only show error notifications
        if (type === 'error') {
            alert(message);
        }
    }
});
</script>
@endpush 
