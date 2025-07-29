@extends('layouts.admin')

@section('title', 'Статусы')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Статусы</h1>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить статус
        </button>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.settings.statuses') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Поиск по названию..." value="{{ request('search') }}">
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Найти</button>
                        <a href="{{ route('admin.settings.statuses') }}" class="btn btn-secondary">Сбросить</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statuses List -->
    <div class="row">
        @foreach($statuses as $status)
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="d-flex flex-column">
                                <strong class="mb-1">Название</strong>
                                <span class="status-name">{{ $status->name }}</span>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex flex-column">
                                <strong class="mb-1">Цвет</strong>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="background-color: {{ $status->color }}; width: 20px; height: 20px; border-radius: 4px;"></div>
                                    <span class="status-color">{{ $status->color }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleEdit({{ $status->id }})">
                                    <i class="bi bi-pencil"></i> Редактировать
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStatus({{ $status->id }})">
                                    <i class="bi bi-trash"></i> Удалить
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form (Hidden by default) -->
                    <div class="edit-form mt-3" id="edit-form-{{ $status->id }}" style="display: none;">
                        <hr>
                        <form onsubmit="updateStatus(event, {{ $status->id }})">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">Название</label>
                                        <input type="text" class="form-control" name="name" value="{{ $status->name }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">Цвет</label>
                                        <input type="color" class="form-control form-control-color" name="color" value="{{ $status->color }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-check"></i> Сохранить
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit({{ $status->id }})">
                                            <i class="bi bi-x"></i> Отмена
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- New Status Form (Hidden by default) -->
    <div class="row" id="new-status-form" style="display: none;">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Новый статус</h5>
                    <form onsubmit="createStatus(event)">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Название</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Цвет</label>
                                    <input type="color" class="form-control form-control-color" name="color" value="#000000" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check"></i> Сохранить
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="removeNewRow()">
                                        <i class="bi bi-x"></i> Отмена
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($statuses->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $statuses->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>

<script>
function toggleEdit(id) {
    closeAllEditCards();
    const form = document.getElementById(`edit-form-${id}`);
    if (form) {
        form.style.display = 'block';
    }
}

function cancelEdit(id) {
    const form = document.getElementById(`edit-form-${id}`);
    if (form) {
        form.style.display = 'none';
    }
}

function closeAllEditCards() {
    const editForms = document.querySelectorAll('.edit-form');
    editForms.forEach(form => {
        form.style.display = 'none';
    });
}

function addNewRow() {
    closeAllEditCards();
    document.getElementById('new-status-form').style.display = 'block';
}

function removeNewRow() {
    document.getElementById('new-status-form').style.display = 'none';
    // Clear form inputs
    const form = document.getElementById('new-status-form').querySelector('form');
    form.reset();
}

function updateStatus(event, id) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch(`/admin/settings/statuses/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: formData.get('name'),
            color: formData.get('color')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при обновлении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при обновлении статуса');
    });
}

function createStatus(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('/admin/settings/statuses', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: formData.get('name'),
            color: formData.get('color')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при создании статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при создании статуса');
    });
}

function deleteStatus(id) {
    if (confirm('Вы уверены, что хотите удалить этот статус?')) {
        fetch(`/admin/settings/statuses/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Ошибка при удалении статуса');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении статуса');
        });
    }
}
</script>
@endsection 