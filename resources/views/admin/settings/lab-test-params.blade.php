@extends('layouts.admin')

@section('title', 'Параметры анализов')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Параметры анализов</h1>
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить параметр
        </button>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Тип анализа</th>
                            <th>Единица измерения</th>
                            <th>Описание</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="paramsTable">
                        @foreach($labTestParams as $param)
                        <tr data-id="{{ $param->id }}" class="existing-row">
                            <td>
                                <input type="text" class="form-control" name="name" value="{{ $param->name }}" onchange="markAsChanged(this)">
                            </td>
                            <td>
                                <select class="form-control" name="lab_test_type_id" onchange="markAsChanged(this)">
                                    @foreach($labTestTypes as $type)
                                        <option value="{{ $type->id }}" {{ $param->lab_test_type_id == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control" name="unit_id" onchange="markAsChanged(this)">
                                    <option value="">Без единицы</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ $param->unit_id == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="description" value="{{ $param->description }}" onchange="markAsChanged(this)">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow({{ $param->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div id="saveButton" class="position-fixed bottom-0 end-0 p-3" style="display: none; z-index: 1000;">
    <button type="button" class="btn btn-success btn-lg" onclick="saveChanges()">
        <i class="bi bi-check-circle"></i> Сохранить изменения
    </button>
</div>

@push('scripts')
<script>
    let changedRows = new Set();
    let newRows = new Set();

    function markAsChanged(element) {
        const row = element.closest('tr');
        if (row.classList.contains('new-row')) {
            newRows.add(row);
        } else {
            changedRows.add(row);
        }
        updateSaveButton();
    }

    function updateSaveButton() {
        const saveButton = document.getElementById('saveButton');
        if (changedRows.size > 0 || newRows.size > 0) {
            saveButton.style.display = 'block';
        } else {
            saveButton.style.display = 'none';
        }
    }

    function addNewRow() {
        const tbody = document.getElementById('paramsTable');
        const newRow = document.createElement('tr');
        newRow.className = 'new-row';
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control" name="name" onchange="markAsChanged(this)">
            </td>
            <td>
                <select class="form-control" name="lab_test_type_id" onchange="markAsChanged(this)">
                    @foreach($labTestTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-control" name="unit_id" onchange="markAsChanged(this)">
                    <option value="">Без единицы</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control" name="description" onchange="markAsChanged(this)">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeNewRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        newRows.add(newRow);
        updateSaveButton();
    }

    function removeNewRow(button) {
        const row = button.closest('tr');
        row.remove();
        newRows.delete(row);
        updateSaveButton();
    }

    function deleteRow(id) {
        if (confirm('Вы уверены, что хотите удалить этот параметр?')) {
            fetch(`/admin/settings/lab-test-params/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.remove();
                    changedRows.delete(row);
                    updateSaveButton();
                    showAlert('Параметр удален', 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Произошла ошибка при удалении', 'error');
            });
        }
    }

    function saveChanges() {
        const updates = [];
        const creates = [];

        // Collect updates
        changedRows.forEach(row => {
            const id = row.dataset.id;
            const data = {
                name: row.querySelector('[name="name"]').value,
                lab_test_type_id: row.querySelector('[name="lab_test_type_id"]').value,
                unit_id: row.querySelector('[name="unit_id"]').value || null,
                description: row.querySelector('[name="description"]').value,
            };
            updates.push({ id, data });
        });

        // Collect creates
        newRows.forEach(row => {
            const data = {
                name: row.querySelector('[name="name"]').value,
                lab_test_type_id: row.querySelector('[name="lab_test_type_id"]').value,
                unit_id: row.querySelector('[name="unit_id"]').value || null,
                description: row.querySelector('[name="description"]').value,
            };
            creates.push(data);
        });

        // Send updates
        Promise.all([
            ...updates.map(update => 
                fetch(`/admin/settings/lab-test-params/${update.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(update.data)
                }).then(response => response.json())
            ),
            ...creates.map(create => 
                fetch('/admin/settings/lab-test-params', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(create)
                }).then(response => response.json())
            )
        ])
        .then(results => {
            const hasErrors = results.some(result => !result.success);
            if (hasErrors) {
                showAlert('Некоторые изменения не были сохранены', 'error');
            } else {
                showAlert('Изменения сохранены', 'success');
                changedRows.clear();
                newRows.clear();
                updateSaveButton();
                
                // Reload page to get fresh data
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Произошла ошибка при сохранении', 'error');
        });
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
</script>
@endpush
@endsection 