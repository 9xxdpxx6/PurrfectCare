@extends('layouts.admin')

@section('title', 'Типы анализов')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Типы анализов</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" onclick="addNewRow()">
            <i class="bi bi-plus"></i> Добавить тип
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Desktop view -->
        <div class="d-none d-md-block">
            <div class="row fw-bold border-bottom pb-2 mb-3">
                <div class="col-md-5 col-lg-4">Название / Цена</div>
                <div class="col-md-6 col-lg-7">Описание</div>
                <div class="col-md-1 col-lg-1 text-center"><i class="bi bi-trash"></i></div>
            </div>
            
            <div id="labTestTypesContainer">
                @foreach($labTestTypes as $labTestType)
                    <div class="row align-items-start border-bottom py-3" data-id="{{ $labTestType->id }}" data-original="{{ json_encode($labTestType->toArray()) }}">
                        <div class="col-md-5 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Название</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $labTestType->name }}" 
                                       data-field="name" onchange="markAsChanged(this)">
                            </div>
                            <div>
                                <label class="form-label small text-muted">Цена</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" value="{{ $labTestType->price }}" 
                                       data-field="price" onchange="markAsChanged(this)">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7">
                            <label class="form-label small text-muted">Описание</label>
                            <textarea class="form-control" rows="4" data-field="description" onchange="markAsChanged(this)">{{ $labTestType->description }}</textarea>
                        </div>
                        <div class="col-md-1 col-lg-1 d-flex align-items-center justify-content-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-3" onclick="deleteRow({{ $labTestType->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Mobile view -->
        <div class="d-md-none">
            <div id="labTestTypesMobileContainer">
                @foreach($labTestTypes as $labTestType)
                    <div class="card mb-3" data-id="{{ $labTestType->id }}" data-original="{{ json_encode($labTestType->toArray()) }}">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small text-muted">Название</label>
                                    <input type="text" class="form-control" value="{{ $labTestType->name }}" 
                                           data-field="name" onchange="markAsChanged(this)">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-muted">Описание</label>
                                    <input type="text" class="form-control" value="{{ $labTestType->description }}" 
                                           data-field="description" onchange="markAsChanged(this)">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted">Цена</label>
                                    <input type="number" step="0.01" class="form-control" value="{{ $labTestType->price }}" 
                                           data-field="price" onchange="markAsChanged(this)">
                                </div>
                                <div class="col-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger w-100" onclick="deleteRow({{ $labTestType->id }})">
                                        <i class="bi bi-trash"></i> Удалить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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
        const row = input.closest('[data-id]') || input.closest('.card');
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
        const isMobile = window.innerWidth < 768;
        const container = isMobile ? 
            document.getElementById('labTestTypesMobileContainer') : 
            document.getElementById('labTestTypesContainer');
        
        if (isMobile) {
            const newCard = document.createElement('div');
            newCard.className = 'card mb-3';
            newCard.innerHTML = `
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small text-muted">Название</label>
                            <input type="text" class="form-control" value="" 
                                   data-field="name" onchange="markAsChanged(this)">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Описание</label>
                            <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Цена</label>
                            <input type="number" step="0.01" class="form-control" value="0" 
                                   data-field="price" onchange="markAsChanged(this)">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeNewRow(this)">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newCard);
        } else {
            const newRow = document.createElement('div');
            newRow.className = 'row align-items-start border-bottom py-3';
            newRow.innerHTML = `
                <div class="col-md-4 col-lg-3">
                    <div class="mb-2">
                        <label class="form-label small text-muted">Название</label>
                        <input type="text" class="form-control form-control-sm" value="" 
                               data-field="name" onchange="markAsChanged(this)">
                    </div>
                    <div>
                        <label class="form-label small text-muted">Цена</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" value="0" 
                               data-field="price" onchange="markAsChanged(this)">
                    </div>
                </div>
                <div class="col-md-4 col-lg-6">
                    <label class="form-label small text-muted">Описание</label>
                    <textarea class="form-control" rows="3" data-field="description" onchange="markAsChanged(this)"></textarea>
                </div>
                <div class="col-md-4 col-lg-3 d-flex align-items-center justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNewRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
        }
        
        hasChanges = true;
        updateSaveButton();
    }

    function removeNewRow(button) {
        const card = button.closest('.card');
        const row = button.closest('.row');
        if (card) {
            card.remove();
        } else if (row) {
            row.remove();
        }
        updateSaveButton();
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
                    const row = document.querySelector(`[data-id="${id}"]`);
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
            const row = document.querySelector(`[data-id="${rowId}"]`);
            if (row) {
                const data = {};
                row.querySelectorAll('input[data-field]').forEach(input => {
                    data[input.dataset.field] = input.value;
                });
                updates.push({ id: rowId, data: data });
            }
        });

        // Collect creates
        const isMobile = window.innerWidth < 768;
        const container = isMobile ? 
            document.getElementById('labTestTypesMobileContainer') : 
            document.getElementById('labTestTypesContainer');
            
        container.querySelectorAll('[data-id]').forEach(row => {
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
                fetch(`{{ route('admin.settings.lab-test-types.update', '') }}/${update.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(update.data)
                })
            ),
            ...creates.map(create => 
                fetch('{{ route('admin.settings.lab-test-types.store') }}', {
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