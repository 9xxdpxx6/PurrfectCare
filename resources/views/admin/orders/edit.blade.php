@extends('layouts.admin')

@section('title', 'Редактировать заказ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать заказ #{{ $item->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<form action="{{ route('admin.orders.update', $item) }}" method="POST" id="orderForm">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Основная информация -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Клиент</label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" data-url="{{ route('admin.orders.client-options') }}" required>
                                @if(old('client_id', $item->client_id))
                                    @php
                                        $selectedClient = \App\Models\User::find(old('client_id', $item->client_id));
                                    @endphp
                                    @if($selectedClient)
                                        <option value="{{ $selectedClient->id }}" selected>{{ $selectedClient->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="pet_id" class="form-label">Питомец</label>
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" data-url="{{ route('admin.orders.pet-options') }}" required>
                                @if(old('pet_id', $item->pet_id))
                                    @php
                                        $selectedPet = \App\Models\Pet::with('client')->find(old('pet_id', $item->pet_id));
                                    @endphp
                                    @if($selectedPet)
                                        <option value="{{ $selectedPet->id }}" selected data-client="{{ $selectedPet->client_id }}">{{ $selectedPet->name }} ({{ $selectedPet->client->name ?? 'Без владельца' }})</option>
                                    @endif
                                @endif
                            </select>
                            @error('pet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="status_id" class="form-label">Статус</label>
                            <select name="status_id" id="status_id" class="form-select @error('status_id') is-invalid @enderror" data-url="{{ route('admin.orders.status-options') }}" required>
                                @if(old('status_id', $item->status_id))
                                    @php
                                        $selectedStatus = \App\Models\Status::find(old('status_id', $item->status_id));
                                    @endphp
                                    @if($selectedStatus)
                                        <option value="{{ $selectedStatus->id }}" selected>{{ $selectedStatus->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="branch_id" class="form-label">Филиал</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" data-url="{{ route('admin.orders.branch-options') }}" required>
                                @if(old('branch_id', $item->branch_id))
                                    @php
                                        $selectedBranch = \App\Models\Branch::find(old('branch_id', $item->branch_id));
                                    @endphp
                                    @if($selectedBranch)
                                        <option value="{{ $selectedBranch->id }}" selected>{{ $selectedBranch->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="manager_id" class="form-label">Менеджер</label>
                            <select name="manager_id" id="manager_id" class="form-select @error('manager_id') is-invalid @enderror" data-url="{{ route('admin.orders.manager-options') }}" required>
                                @if(old('manager_id', $item->manager_id))
                                    @php
                                        $selectedManager = \App\Models\Employee::find(old('manager_id', $item->manager_id));
                                    @endphp
                                    @if($selectedManager)
                                        <option value="{{ $selectedManager->id }}" selected>{{ $selectedManager->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Дополнительная информация о заказе...">{{ old('notes', $item->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Элементы заказа -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Элементы заказа</h5>
                </div>
                <div class="card-body">
                    <!-- Услуги -->
                    <div class="mb-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-8 col-xl-9">
                                <h6 class="mb-0">Услуги</h6>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-3 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="addServiceItem()">
                                    <i class="bi bi-plus-lg"></i> Добавить услугу
                                </button>
                            </div>
                        </div>
                        <div id="serviceItems">
                            @foreach($item->items->where('item_type', 'App\Models\Service') as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="service">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Услуга</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.service-options') }}" required>
                                                @if($orderItem->item)
                                                    <option value="{{ $orderItem->item_id }}" selected>{{ $orderItem->item->name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="service">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="1" max="9999" required>
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01" required>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Сумма:</span>
                                                <span class="item-total fw-bold">{{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Препараты -->
                    <div class="mb-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-8 col-xl-9">
                                <h6 class="mb-0">Препараты</h6>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-3 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="addDrugItem()">
                                    <i class="bi bi-plus-lg"></i> Добавить препарат
                                </button>
                            </div>
                        </div>
                        <div id="drugItems">
                            @foreach($item->items->where('item_type', 'App\Models\Drug') as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="drug">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Препарат</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.drug-options') }}" required>
                                                @if($orderItem->item)
                                                    <option value="{{ $orderItem->item_id }}" selected>{{ $orderItem->item->name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="drug">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="1" max="9999" required>
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01" required>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Сумма:</span>
                                                <span class="item-total fw-bold">{{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Анализы -->
                    <div class="mb-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-8 col-xl-9">
                                <h6 class="mb-0">Анализы</h6>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-3 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-sm w-100" id="addLabTestBtn" onclick="addLabTestItem()" disabled>
                                    <i class="bi bi-plus-lg"></i> Добавить анализ
                                </button>
                            </div>
                        </div>
                        <div id="labTestItems">
                            @foreach($item->items->where('item_type', 'App\Models\LabTest') as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="lab_test">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Анализ</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.lab-test-options') }}" required>
                                                @if($orderItem->item)
                                                    @php
                                                        $date = $orderItem->item->received_at ? \Carbon\Carbon::parse($orderItem->item->received_at)->format('d.m.Y') : 'без даты';
                                                    @endphp
                                                    <option value="{{ $orderItem->item_id }}" selected>Анализ от {{ $date }} - {{ $orderItem->item->pet->name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="lab_test">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="1" max="9999" required>
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01" required>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Сумма:</span>
                                                <span class="item-total fw-bold">{{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Вакцинации -->
                    <div class="mb-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 col-lg-8 col-xl-9">
                                <h6 class="mb-0">Вакцинации</h6>
                            </div>
                            <div class="col-md-6 col-lg-4 col-xl-3 mt-2 mt-md-0">
                                <button type="button" class="btn btn-success btn-sm w-100" id="addVaccinationBtn" onclick="addVaccinationItem()" disabled>
                                    <i class="bi bi-plus-lg"></i> Добавить вакцинацию
                                </button>
                            </div>
                        </div>
                        <div id="vaccinationItems">
                            @foreach($item->items->where('item_type', 'App\Models\Vaccination') as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="vaccination">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Вакцинация</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.vaccination-options') }}" required>
                                                @if($orderItem->item)
                                                    @php
                                                        $date = $orderItem->item->vaccination_date ? \Carbon\Carbon::parse($orderItem->item->vaccination_date)->format('d.m.Y') : 'без даты';
                                                    @endphp
                                                    <option value="{{ $orderItem->item_id }}" selected>Вакцинация от {{ $date }} - {{ $orderItem->item->pet->name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="vaccination">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="1" max="9999" required>
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01" required>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Сумма:</span>
                                                <span class="item-total fw-bold">{{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }} ₽</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6 offset-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Итого:</h5>
                                <h4 class="mb-0" id="totalAmount">{{ number_format($item->total, 2) }} ₽</h4>
                            </div>
                            <input type="hidden" name="total" id="total" value="{{ $item->total }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="col-12">
            <div class="d-flex justify-content-between gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Отмена
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg"></i> Сохранить изменения
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Шаблоны для элементов заказа -->
<template id="serviceItemTemplate">
    <div class="order-item border rounded p-3 mb-3" data-item-index="" data-item-type="service">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Услуга</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.service-options') }}" required>
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="service">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="1" max="9999" required>
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01" required>
            </div>
            
            <div class="col-lg-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6 offset-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Сумма:</span>
                    <span class="item-total fw-bold">0.00 ₽</span>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="drugItemTemplate">
    <div class="order-item border rounded p-3 mb-3" data-item-index="" data-item-type="drug">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Препарат</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.drug-options') }}" required>
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="drug">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="1" max="9999" required>
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01" required>
            </div>
            
            <div class="col-lg-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6 offset-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Сумма:</span>
                    <span class="item-total fw-bold">0.00 ₽</span>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="labTestItemTemplate">
    <div class="order-item border rounded p-3 mb-3" data-item-index="" data-item-type="lab_test">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Анализ</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.lab-test-options') }}" required>
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="lab_test">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="1" max="9999" required>
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01" required>
            </div>
            
            <div class="col-lg-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6 offset-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Сумма:</span>
                    <span class="item-total fw-bold">0.00 ₽</span>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="vaccinationItemTemplate">
    <div class="order-item border rounded p-3 mb-3" data-item-index="" data-item-type="vaccination">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Вакцинация</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.vaccination-options') }}" required>
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="vaccination">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="1" max="9999" required>
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01" required>
            </div>
            
            <div class="col-lg-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6 offset-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Сумма:</span>
                    <span class="item-total fw-bold">0.00 ₽</span>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@php
    $itemCount = $item->items->count();
@endphp
@push('scripts')
<script>
    let itemIndex = {{ $itemCount }};
    let petTomSelect; // Глобальная переменная для доступа из других функций
    
    const itemUrls = {
        service: '{{ route("admin.orders.service-options") }}',
        drug: '{{ route("admin.orders.drug-options") }}',
        lab_test: '{{ route("admin.orders.lab-test-options") }}',
        vaccination: '{{ route("admin.orders.vaccination-options") }}'
    };

    document.addEventListener('DOMContentLoaded', function () {
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        
        // TomSelect для основных полей
        const clientTomSelect = new createTomSelect('#client_id', {
            placeholder: 'Выберите клиента...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        petTomSelect = new createTomSelect('#pet_id', {
            placeholder: 'Выберите питомца...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: false,
            load: function(query, callback) {
                const clientId = clientTomSelect.getValue();
                if (!clientId) {
                    callback([]);
                    return;
                }
                
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false&client_id=' + clientId;
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback([]));
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        new createTomSelect('#status_id', {
            placeholder: 'Выберите статус...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        new createTomSelect('#branch_id', {
            placeholder: 'Выберите филиал...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        new createTomSelect('#manager_id', {
            placeholder: 'Выберите менеджера...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function() {
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });

        // Фильтрация питомцев по клиенту
        function filterPetsByClient(clientId) {
            petTomSelect.clear();
            petTomSelect.clearOptions();
            
            if (!clientId) {
                petTomSelect.disable();
                return;
            } else {
                petTomSelect.enable();
            }
            
            // Загружаем питомцев для выбранного клиента
            fetch(`{{ route('admin.orders.pet-options') }}?client_id=${clientId}&filter=false`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(option => {
                        petTomSelect.addOption(option);
                    });
                    
                    // Восстанавливаем выбранное значение питомца только если клиент совпадает
                    const currentPetId = '{{ old("pet_id", $item->pet_id) }}';
                    const currentClientId = '{{ old("client_id", $item->client_id) }}';
                    if (currentPetId && clientId === currentClientId) {
                        petTomSelect.setValue(currentPetId);
                    }
                })
                .catch(() => {
                    petTomSelect.disable();
                });
        }
        
        // Слушатель изменения клиента
        clientTomSelect.on('change', function(value) {
            filterPetsByClient(value);
        });
        
        // Инициализация при загрузке страницы
        const initialClientId = clientTomSelect.getValue();
        if (initialClientId) {
            filterPetsByClient(initialClientId);
        }
        
        // Управление кнопками анализов и вакцинаций
        function updatePetDependentButtons() {
            const petId = petTomSelect.getValue();
            const addLabTestBtn = document.getElementById('addLabTestBtn');
            const addVaccinationBtn = document.getElementById('addVaccinationBtn');
            
            if (petId) {
                addLabTestBtn.disabled = false;
                addVaccinationBtn.disabled = false;
            } else {
                addLabTestBtn.disabled = true;
                addVaccinationBtn.disabled = true;
            }
        }
        
        // Слушатель изменения питомца
        petTomSelect.on('change', function(value) {
            updatePetDependentButtons();
            
            const currentPetId = '{{ $item->pet_id }}';
            
            // Проверяем, есть ли анализы или вакцинации и изменился ли питомец
            const labTestItems = document.getElementById('labTestItems');
            const vaccinationItems = document.getElementById('vaccinationItems');
            
            if ((labTestItems.children.length > 0 || vaccinationItems.children.length > 0) && value !== currentPetId) {
                if (!confirm('Внимание! При смене питомца все добавленные анализы и вакцинации будут удалены. Продолжить?')) {
                    // Если пользователь отменил, возвращаем предыдущее значение
                    const previousValue = this.lastValue || currentPetId;
                    // Временно отключаем слушатель события
                    petTomSelect.off('change');
                    this.setValue(previousValue);
                    // Включаем обратно слушатель события
                    setTimeout(() => {
                        petTomSelect.on('change', arguments.callee);
                    }, 100);
                    return;
                }
            }
            
            // Сохраняем текущее значение для следующей проверки
            this.lastValue = value;
            updatePetDependentItems();
        });
        
        // Инициализация состояния кнопок
        updatePetDependentButtons();
        
        // Функция для обновления элементов анализов и вакцинаций при смене питомца
        function updatePetDependentItems() {
            const petId = petTomSelect.getValue();
            const currentPetId = '{{ $item->pet_id }}';
            
            // Если питомец изменился, очищаем анализы и вакцинации
            if (petId !== currentPetId) {
                const labTestItems = document.getElementById('labTestItems');
                const vaccinationItems = document.getElementById('vaccinationItems');
                
                labTestItems.innerHTML = '';
                vaccinationItems.innerHTML = '';
                
                // Пересчитываем общую сумму
                calculateTotal();
            }
        }
        
        // Инициализируем lastValue для petTomSelect
        petTomSelect.lastValue = petTomSelect.getValue();

        // Инициализируем TomSelect для существующих элементов заказа
        const existingItems = document.querySelectorAll('.order-item');
        existingItems.forEach((item, index) => {
            const itemSelect = item.querySelector('.item-select');
            const itemType = item.getAttribute('data-item-type');
            
            if (itemType && itemUrls[itemType]) {
                itemSelect.dataset.url = itemUrls[itemType];
                initItemTomSelect(itemSelect, itemType);
            }
            
            // Обработчики для расчета суммы
            const quantityInput = item.querySelector('.item-quantity');
            const priceInput = item.querySelector('.item-price');
            
            quantityInput.addEventListener('input', calculateItemTotal);
            priceInput.addEventListener('input', calculateItemTotal);
        });
        
        calculateTotal();
    });

    // Базовый метод для добавления элемента заказа
    function addOrderItemBase(templateId, containerId, itemType) {
        const template = document.getElementById(templateId);
        const container = document.getElementById(containerId);
        const clone = template.content.cloneNode(true);
        
        // Обновляем индексы
        const itemDiv = clone.querySelector('.order-item');
        itemDiv.setAttribute('data-item-index', itemIndex);
        
        const selects = clone.querySelectorAll('select');
        const inputs = clone.querySelectorAll('input');
        
        selects.forEach(select => {
            select.name = select.name.replace('INDEX', itemIndex);
        });
        
        inputs.forEach(input => {
            input.name = input.name.replace('INDEX', itemIndex);
        });
        
        container.appendChild(clone);
        
        // Инициализируем TomSelect для нового элемента
        const itemSelect = container.querySelector(`[data-item-index="${itemIndex}"] .item-select`);
        
        // Инициализируем TomSelect для элемента
        initItemTomSelect(itemSelect, itemType);
        
        // Обработчики для расчета суммы
        const quantityInput = container.querySelector(`[data-item-index="${itemIndex}"] .item-quantity`);
        const priceInput = container.querySelector(`[data-item-index="${itemIndex}"] .item-price`);
        
        quantityInput.addEventListener('input', calculateItemTotal);
        priceInput.addEventListener('input', calculateItemTotal);
        
        itemIndex++;
        calculateTotal();
    }

    // Методы для добавления элементов каждого типа
    function addServiceItem() {
        addOrderItemBase('serviceItemTemplate', 'serviceItems', 'service');
    }

    function addDrugItem() {
        addOrderItemBase('drugItemTemplate', 'drugItems', 'drug');
    }

    function addLabTestItem() {
        addOrderItemBase('labTestItemTemplate', 'labTestItems', 'lab_test');
    }

    function addVaccinationItem() {
        addOrderItemBase('vaccinationItemTemplate', 'vaccinationItems', 'vaccination');
    }

    function initItemTomSelect(select, type) {
        const url = itemUrls[type];
        if (!url) return;
        
        new createTomSelect(select, {
            placeholder: 'Выберите элемент...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: true,
            load: function(query, callback) {
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                
                // Добавляем pet_id для анализов и вакцинаций
                if (type === 'lab_test' || type === 'vaccination') {
                    if (petTomSelect && petTomSelect.getValue) {
                        const petId = petTomSelect.getValue();
                        if (petId) {
                            url += '&pet_id=' + petId;
                        }
                    }
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onItemAdd: function(value) {
                // Устанавливаем цену по умолчанию
                const itemDiv = this.input.closest('.order-item');
                const priceInput = itemDiv.querySelector('.item-price');
                const itemType = itemDiv.querySelector('input[name*="[item_type]"]').value;
                
                // Получаем цену по умолчанию из выбранного элемента
                if (value && value.price !== undefined) {
                    priceInput.value = value.price;
                } else if (itemType === 'service' || itemType === 'drug') {
                    // Для услуг и препаратов устанавливаем цену по умолчанию
                    fetch(this.input.dataset.url + '?selected=' + value)
                        .then(response => response.json())
                        .then(data => {
                            const selectedItem = data.find(item => item.value == value);
                            if (selectedItem && selectedItem.price) {
                                priceInput.value = selectedItem.price;
                            }
                        });
                }
                
                calculateItemTotal.call(priceInput);
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
    }

    function removeOrderItem(button) {
        const itemDiv = button.closest('.order-item');
        itemDiv.remove();
        calculateTotal();
    }

    function calculateItemTotal() {
        const itemDiv = this.closest('.order-item');
        const quantity = parseFloat(itemDiv.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(itemDiv.querySelector('.item-price').value) || 0;
        const total = quantity * price;
        
        itemDiv.querySelector('.item-total').textContent = total.toFixed(2) + ' ₽';
        calculateTotal();
    }

    function calculateTotal() {
        const items = document.querySelectorAll('.order-item');
        let total = 0;
        
        items.forEach(item => {
            const quantity = parseFloat(item.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(item.querySelector('.item-price').value) || 0;
            total += quantity * price;
        });
        
        document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ₽';
        document.getElementById('total').value = total.toFixed(2);
    }
</script>
@endpush 