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

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="branchesTable">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Адрес</th>
                        <th>Телефон</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                        <tr data-id="{{ $branch->id }}" data-original="{{ json_encode($branch->toArray()) }}">
                            <td>
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->address }}" 
                                       data-field="address" onchange="markAsChanged(this)">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="{{ $branch->phone }}" 
                                       data-field="phone" onchange="markAsChanged(this)">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow({{ $branch->id }})">
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

<!-- Save Changes Button -->
<div id="saveChangesBtn" class="position-fixed bottom-0 end-0 m-3" style="display: none;">
    <button type="button" class="btn btn-success btn-lg" onclick="saveChanges()">
        <i class="bi bi-check-circle"></i> Сохранить изменения
    </button>
</div>

@endsection

@push('scripts')
<script>
    let hasChanges = false;
    let changedRows = new Set();

    function markAsChanged(input) {
        const row = input.closest('tr');
        const rowId = row.dataset.id;
        
        if (rowId) {
            changedRows.add(rowId);
        } else {
            // New row
            hasChanges = true;
        }
        
        hasChanges = true;
        updateSaveButton();
    }

    function updateSaveButton() {
        const saveBtn = document.getElementById('saveChangesBtn');
        if (hasChanges) {
            saveBtn.style.display = 'block';
        } else {
            saveBtn.style.display = 'none';
        }
    }

    function addNewRow() {
        const tbody = document.querySelector('#branchesTable tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" value="" 
                       data-field="name" onchange="markAsChanged(this)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="" 
                       data-field="address" onchange="markAsChanged(this)">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" value="" 
                       data-field="phone" onchange="markAsChanged(this)">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNewRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        hasChanges = true;
        updateSaveButton();
    }

    function removeNewRow(button) {
        button.closest('tr').remove();
        updateSaveButton();
    }

    function deleteRow(id) {
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
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    row.remove();
                    changedRows.delete(id.toString());
                    showNotification(data.message, 'success');
                } else {
                    showNotification('Ошибка при удалении', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка при удалении', 'error');
            });
        }
    }

    function saveChanges() {
        const updates = [];
        const creates = [];

        // Collect updates
        changedRows.forEach(rowId => {
            const row = document.querySelector(`tr[data-id="${rowId}"]`);
            if (row) {
                const data = {};
                row.querySelectorAll('input[data-field]').forEach(input => {
                    data[input.dataset.field] = input.value;
                });
                updates.push({ id: rowId, data: data });
            }
        });

        // Collect creates
        document.querySelectorAll('#branchesTable tbody tr').forEach(row => {
            if (!row.dataset.id) {
                const data = {};
                row.querySelectorAll('input[data-field]').forEach(input => {
                    data[input.dataset.field] = input.value;
                });
                creates.push(data);
            }
        });

        // Send updates
        Promise.all([
            ...updates.map(update => 
                fetch(`{{ route('admin.settings.branches.update', '') }}/${update.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(update.data)
                })
            ),
            ...creates.map(create => 
                fetch('{{ route('admin.settings.branches.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(create)
                })
            )
        ])
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
            const allSuccess = results.every(result => result.success);
            if (allSuccess) {
                hasChanges = false;
                changedRows.clear();
                updateSaveButton();
                showNotification('Изменения сохранены', 'success');
                // Reload page to get updated data
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('Ошибка при сохранении', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ошибка при сохранении', 'error');
        });
    }

    function showNotification(message, type) {
        // Simple notification - you can replace with your preferred notification system
        alert(message);
    }
</script>
@endpush 