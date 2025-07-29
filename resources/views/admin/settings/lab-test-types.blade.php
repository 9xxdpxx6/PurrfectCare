@extends('layouts.admin')

@section('title', 'Типы анализов')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Типы анализов</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить тип анализа
        </button>
    </div>
</div>

<form method="GET" class="mb-4">
    <div class="d-flex flex-wrap align-items-end gap-2">
        <div class="flex-grow-1" style="min-width:200px;">
            <label for="search" class="form-label mb-1">Поиск</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Поиск по названию или описанию..." value="{{ request('search') }}">
        </div>
        <div class="d-flex gap-2 ms-auto w-auto">
            <a href="{{ route('admin.settings.lab-test-types') }}" class="btn btn-outline-secondary">
                <span class="d-none d-lg-inline">Сбросить</span> <i class="bi bi-x-lg"></i>
            </a>
            <button type="submit" class="btn btn-outline-primary">
                <span class="d-none d-lg-inline">Найти</span> <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($labTestTypes as $type)
        <div class="col-12">
            <div class="card h-100 border-0 border-bottom shadow-sm
        @if($loop->iteration % 2 == 1) bg-body-tertiary @endif" data-id="{{ $type->id }}" data-original="{{ json_encode(['name' => $type->name, 'description' => $type->description, 'price' => $type->price]) }}">

                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">{{ $type->name }}</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Цена:</span> {{ number_format($type->price, 0, ',', ' ') }} ₽
                            </div>
                            @if($type->description)
                                <div class="text-muted">
                                    <span>Описание:</span> {{ $type->description }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Поля для редактирования -->
                    <div class="d-none edit-fields">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $type->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Цена</label>
                                <input type="number" class="form-control form-control-sm" value="{{ $type->price }}" 
                                       data-field="price" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Описание</label>
                                <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)">{{ $type->description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
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
                        <button type="button" class="btn btn-outline-danger" title="Удалить" onclick="deleteRow({{ $type->id }})">
                            <span class="d-none d-lg-inline-block">Удалить</span>
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($labTestTypes->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-clipboard-data display-1 text-muted"></i>
        <h3 class="mt-3 text-muted">Типы анализов не найдены</h3>
        <p class="text-muted">Создайте первый тип анализа.</p>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить тип анализа
        </button>
    </div>
@endif

@if($labTestTypes->hasPages())
    <div class="mt-4">
        {{ $labTestTypes->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script>
    let hasChanges = false;
    let changedRows = new Set();

    function markAsChanged(input) {
        const card = input.closest('[data-id]');
        const rowId = card.dataset.id;
        
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

    function saveRow(button) {
        const card = button.closest('.card');
        const rowId = card.dataset.id;
        
        if (rowId) {
            const data = {};
            card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
                data[input.dataset.field] = input.value;
            });
            
            fetch(`{{ route('admin.settings.lab-test-types.update', '') }}/${rowId}`, {
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
        // Закрываем все другие карточки
        closeAllEditCards();
        
        const container = document.querySelector('.row.g-3');
        const newCard = document.createElement('div');
        newCard.className = 'col-12';
        newCard.innerHTML = `
            <div class="card h-100 border-0 border-bottom shadow-sm bg-body-tertiary">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                    <!-- Основная информация -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 align-items-start">
                        <h5 class="card-title">Новый тип анализа</h5>
                        <div class="mt-auto w-100">
                            <div class="text-muted mb-2">
                                <span>Цена:</span> <span class="price-display">Не указана</span>
                            </div>
                            <div class="text-muted">
                                <span>Описание:</span> <span class="description-display">Не указано</span>
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
                                <label class="form-label small text-muted">Цена</label>
                                <input type="number" class="form-control form-control-sm" value="" 
                                       data-field="price" onchange="markAsChanged(this)">
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Описание</label>
                                <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="d-flex flex-row flex-lg-column gap-2 ms-lg-4 align-self-start mt-3 mt-lg-0">
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
        container.appendChild(newCard);
        
        hasChanges = true;
    }

    function saveNewRow(button) {
        const card = button.closest('.card');
        const data = {};
        card.querySelectorAll('input[data-field], select[data-field], textarea[data-field]').forEach(input => {
            data[input.dataset.field] = input.value;
        });
        
        fetch('{{ route('admin.settings.lab-test-types.store') }}', {
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
                alert('Ошибка при создании типа анализа');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при создании типа анализа');
        });
    }

    function removeNewRow(button) {
        const card = button.closest('.col-12');
        card.remove();
    }

    function deleteRow(id) {
        if (confirm('Вы уверены, что хотите удалить этот тип анализа?')) {
            fetch(`{{ route('admin.settings.lab-test-types.destroy', '') }}/${id}`, {
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
                    alert('Ошибка при удалении типа анализа');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении типа анализа');
            });
        }
    }

    function showNotification(message, type) {
        // Only show error notifications
        if (type === 'error') {
            alert(message);
        }
    }
</script>
@endpush 