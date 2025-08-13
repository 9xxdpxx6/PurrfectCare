@extends('layouts.admin')

@section('title', 'Добавить заказ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Добавить заказ</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
    @csrf
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
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
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" data-url="{{ route('admin.orders.client-options') }}">
                                @if(old('client_id') || $selectedClientId)
                                    @php
                                        $clientId = old('client_id', $selectedClientId);
                                        $selectedClient = \App\Models\User::find($clientId);
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
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" data-url="{{ route('admin.orders.pet-options') }}">
                                @if(old('pet_id') || $selectedPetId)
                                    @php
                                        $petId = old('pet_id', $selectedPetId);
                                        $selectedPet = \App\Models\Pet::with('client')->find($petId);
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
                            <select name="status_id" id="status_id" class="form-select @error('status_id') is-invalid @enderror" data-url="{{ route('admin.orders.status-options') }}">
                                @if(old('status_id'))
                                    @php
                                        $selectedStatus = \App\Models\Status::find(old('status_id'));
                                    @endphp
                                    @if($selectedStatus)
                                        <option value="{{ $selectedStatus->id }}" selected>{{ $selectedStatus->name }}</option>
                                    @endif
                                @else
                                    @php
                                        $defaultStatus = \App\Models\Status::where('name', 'Новый')->first();
                                    @endphp
                                    @if($defaultStatus)
                                        <option value="{{ $defaultStatus->id }}" selected>{{ $defaultStatus->name }}</option>
                                    @endif
                                @endif
                            </select>
                            @error('status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="branch_id" class="form-label">Филиал</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" data-url="{{ route('admin.orders.branch-options') }}">
                                @if(old('branch_id'))
                                    @php
                                        $selectedBranch = \App\Models\Branch::find(old('branch_id'));
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
                            <select name="manager_id" id="manager_id" class="form-select @error('manager_id') is-invalid @enderror" data-url="{{ route('admin.orders.manager-options') }}">
                                @if(old('manager_id'))
                                    @php
                                        $selectedManager = \App\Models\Employee::find(old('manager_id'));
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
                        
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_paid">
                                        Оплачен
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_closed" id="is_closed" value="1" {{ old('is_closed') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_closed">
                                        Выполнен
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Заметки</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Дополнительная информация о заказе...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Состав заказа -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Состав заказа</h5>
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
                            @if(old('items'))
                                @foreach(old('items') as $index => $item)
                                    @if($item['item_type'] === 'service')
                                        <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="service">
                                            <div class="row g-3">
                                                <div class="col-12 col-lg-5">
                                                    <label class="form-label">Услуга</label>
                                                    <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.service-options') }}">
                                                        @php
                                                            $service = \App\Models\Service::find($item['item_id']);
                                                        @endphp
                                                        @if($service)
                                                            <option value="{{ $service->id }}" selected>{{ $service->name }}</option>
                                                        @endif
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" value="service">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Кол-во</label>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $item['quantity'] ?? 1 }}" min="0.01" max="9999" step="0.01">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Цена</label>
                                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" max="999999.99" step="0.01">
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
                                                        <span class="item-total fw-bold">{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }} ₽</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <hr class="mb-5">

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
                            @if(old('items'))
                                @foreach(old('items') as $index => $item)
                                    @if($item['item_type'] === 'drug')
                                        <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="drug">
                                            <div class="row g-3">
                                                <div class="col-12 col-lg-5">
                                                    <label class="form-label">Препарат</label>
                                                    <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.drug-options') }}">
                                                        @php
                                                            $drug = \App\Models\Drug::find($item['item_id']);
                                                        @endphp
                                                        @if($drug)
                                                            <option value="{{ $drug->id }}" selected>{{ $drug->name }}</option>
                                                        @endif
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" value="drug">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Кол-во</label>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $item['quantity'] ?? 1 }}" min="0.01" max="9999" step="0.01">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Цена</label>
                                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" max="999999.99" step="0.01">
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
                                                        <span class="item-total fw-bold">{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }} ₽</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <hr class="mb-5">

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
                            @if(old('items'))
                                @foreach(old('items') as $index => $item)
                                    @if($item['item_type'] === 'lab_test')
                                        <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="lab_test">
                                            <div class="row g-3">
                                                <div class="col-12 col-lg-8">
                                                    <label class="form-label">Анализ</label>
                                                    <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.lab-test-options') }}">
                                                        @php
                                                            $labTestType = \App\Models\LabTestType::find($item['item_id']);
                                                        @endphp
                                                        @if($labTestType)
                                                            <option value="{{ $labTestType->id }}" selected>{{ $labTestType->name }}</option>
                                                        @endif
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" value="lab_test">
                                                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Цена</label>
                                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" max="999999.99" step="0.01">
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
                                                        <span class="item-total fw-bold">{{ number_format(($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0), 2) }} ₽</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <hr class="mb-5">

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
                            @if(old('items'))
                                @foreach(old('items') as $index => $item)
                                    @if($item['item_type'] === 'vaccination')
                                        <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="vaccination">
                                            <div class="row g-3">
                                                <div class="col-12 col-lg-8">
                                                    <label class="form-label">Вакцинация</label>
                                                    <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.vaccination-options') }}">
                                                        @php
                                                            $vaccinationType = \App\Models\VaccinationType::find($item['item_id']);
                                                        @endphp
                                                        @if($vaccinationType)
                                                            <option value="{{ $vaccinationType->id }}" selected>{{ $vaccinationType->name }}</option>
                                                        @endif
                                                    </select>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" value="vaccination">
                                                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}">
                                                </div>
                                                
                                                <div class="col-6 col-lg-3">
                                                    <label class="form-label">Цена</label>
                                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" max="999999.99" step="0.01">
                                                </div>
                                                
                                                <div class="col-lg-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <small class="text-muted">Препараты в вакцинации:</small>
                                                    <ul class="vaccination-drugs-list mt-1 mb-0" style="list-style: none; padding-left: 0;">
                                                        @if($vaccinationType)
                                                            @foreach($vaccinationType->drugs as $drug)
                                                                <li class="mb-1">
                                                                    <small class="text-muted">• {{ $drug->name }} - {{ $drug->pivot->dosage }} шт.</small>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <hr class="mb-4">
                    
                    <div class="row mt-3">
                        <div class="col-md-6 offset-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Итого:</h5>
                                <h4 class="mb-0" id="totalAmount">{{ number_format(old('total', 0), 2) }} ₽</h4>
                            </div>
                            <input type="hidden" name="total" id="total" value="{{ old('total', 0) }}">
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
                    <i class="bi bi-check-lg"></i> Добавить заказ
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Шаблоны для состава заказа -->
<template id="serviceItemTemplate">
    <div class="order-item border rounded p-3 mb-3" data-item-index="" data-item-type="service">
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <label class="form-label">Услуга</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.service-options') }}">
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="service">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="0.01" max="9999" step="0.01">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01">
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
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.drug-options') }}">
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="drug">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Кол-во</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" value="1" min="0.01" max="9999" step="0.01">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01">
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
            <div class="col-12 col-lg-8">
                <label class="form-label">Анализ</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.lab-test-options') }}">
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="lab_test">
                <input type="hidden" name="items[INDEX][quantity]" value="1">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01">
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
            <div class="col-12 col-lg-8">
                <label class="form-label">Вакцинация</label>
                <select name="items[INDEX][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.vaccination-options') }}">
                </select>
                <input type="hidden" name="items[INDEX][item_type]" value="vaccination">
                <input type="hidden" name="items[INDEX][quantity]" value="1">
            </div>
            
            <div class="col-6 col-lg-3">
                <label class="form-label">Цена</label>
                <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" value="0" min="0" max="999999.99" step="0.01">
            </div>
            
            <div class="col-lg-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeOrderItem(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-12">
                <small class="text-muted">Препараты в вакцинации:</small>
                <ul class="vaccination-drugs-list mt-1 mb-0" style="list-style: none; padding-left: 0;">
                    <!-- Список препаратов будет добавляться сюда -->
                </ul>
            </div>
        </div>
    </div>
</template>
@endsection

@push('styles')
<style>
    /* Растягиваем TomSelect на всю ширину */
    .ts-wrapper {
        width: 100% !important;
    }
    .ts-control {
        width: 100% !important;
    }
    .ts-dropdown {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let itemIndex = 0;
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
        
        // Получаем предустановленные значения
        const selectedClientId = '{{ old('client_id', $selectedClientId ?? "") }}';
        const selectedPetId = '{{ old('pet_id', $selectedPetId ?? "") }}';
        
        // Обработчик отправки формы
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            // Удаляем пустые элементы заказа перед отправкой
            const items = document.querySelectorAll('.order-item');
            items.forEach(item => {
                const itemIdSelect = item.querySelector('select[name*="[item_id]"]');
                const quantityInput = item.querySelector('input[name*="[quantity]"]');
                const priceInput = item.querySelector('input[name*="[unit_price]"]');
                
                // Проверяем, есть ли выбранный элемент и заполнены ли обязательные поля
                const hasItemId = itemIdSelect && itemIdSelect.value;
                const hasQuantity = quantityInput && parseFloat(quantityInput.value) > 0;
                const hasPrice = priceInput && parseFloat(priceInput.value) > 0;
                
                // Если элемент не заполнен полностью, удаляем его
                if (!hasItemId || !hasQuantity || !hasPrice) {
                    item.remove();
                }
            });
        });
        
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
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false';
                
                // Если выбран клиент, фильтруем по нему
                if (clientId) {
                    url += '&client_id=' + clientId;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback([]));
            },
            onItemAdd: function(value) {
                // При выборе питомца автоматически заполняем клиента
                const selectedOption = this.options[value];
                if (selectedOption && selectedOption.dataset && selectedOption.dataset.client) {
                    const clientId = selectedOption.dataset.client;
                    clientTomSelect.setValue(clientId);
                }
                
                setTimeout(() => {
                    this.close();
                    this.blur();
                }, 50);
            }
        });
        
        // Устанавливаем предустановленные значения с задержкой для полной инициализации
        setTimeout(() => {
            // Сначала устанавливаем клиента
            if (selectedClientId) {
                clientTomSelect.setValue(selectedClientId);
            }
            
            // Затем с задержкой устанавливаем питомца
            setTimeout(() => {
                if (selectedPetId) {
                    petTomSelect.setValue(selectedPetId);
                }
            }, 200);
        }, 100);
        
        // Обработчик изменения клиента
        clientTomSelect.on('change', function(value) {
            // Очищаем выбранного питомца при смене клиента
            petTomSelect.clear();
            // Обновляем список питомцев
            petTomSelect.refreshOptions();
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
                    const currentPetId = '{{ old("pet_id") }}';
                    if (currentPetId && clientId === '{{ old("client_id") }}') {
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
            
            // Сохраняем текущее значение для следующей проверки
            this.lastValue = value;
        });
        
        // Инициализация состояния кнопок
        updatePetDependentButtons();
        

        
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
            
            if (quantityInput) {
                quantityInput.addEventListener('input', calculateItemTotal);
            }
            if (priceInput) {
                priceInput.addEventListener('input', calculateItemTotal);
            }
        });
        
        // Обновляем индекс для новых элементов
        itemIndex = existingItems.length;
        
        // Инициализируем тоталы для существующих элементов
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
        
        // Для анализов и вакцинаций количество всегда 1, для остальных - обычная обработка
        if (itemType === 'lab_test' || itemType === 'vaccination') {
            if (quantityInput) {
                quantityInput.value = '1';
                quantityInput.readOnly = true;
            }
            if (priceInput) {
                priceInput.addEventListener('input', calculateItemTotal);
            }
        } else {
            if (quantityInput) {
                quantityInput.addEventListener('input', calculateItemTotal);
            }
            if (priceInput) {
                priceInput.addEventListener('input', calculateItemTotal);
            }
        }
        
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

    function addVaccinationDrugs(vaccinationTypeId, vaccinationItemDiv = null) {
        // Получаем препараты из типа вакцинации
        fetch(`{{ route('admin.vaccination-types.drugs', 'VACCINATION_TYPE_ID') }}`.replace('VACCINATION_TYPE_ID', vaccinationTypeId))
            .then(response => response.json())
            .then(drugs => {
                // Обновляем список препаратов в вакцинации
                if (vaccinationItemDiv) {
                    const drugsList = vaccinationItemDiv.querySelector('.vaccination-drugs-list');
                    if (drugsList) {
                        drugsList.innerHTML = '';
                        drugs.forEach(drug => {
                            const li = document.createElement('li');
                            li.className = 'mb-1';
                            li.innerHTML = `<small class="text-muted">• ${drug.name} - ${drug.dosage} шт.</small>`;
                            drugsList.appendChild(li);
                        });
                    }
                }
                
                // Добавляем препараты отдельно в секцию препаратов
                drugs.forEach(drug => {
                    addDrugItem(); // Добавляем новый элемент препарата
                    const drugItems = document.getElementById('drugItems');
                    const lastDrugItem = drugItems.lastElementChild;
                    if (lastDrugItem) {
                        const drugSelect = lastDrugItem.querySelector('.item-select');
                        if (drugSelect && drugSelect.tomselect) {
                            // Добавляем опцию с названием препарата
                            drugSelect.tomselect.addOption({
                                value: drug.id,
                                text: drug.name
                            });
                            drugSelect.tomselect.setValue(drug.id); // Устанавливаем выбранный препарат
                        }
                        const quantityInput = lastDrugItem.querySelector('.item-quantity');
                        if (quantityInput) {
                            quantityInput.value = drug.dosage; // Устанавливаем дозировку
                            quantityInput.readOnly = true; // Делаем поле только для чтения вместо отключения
                            quantityInput.setAttribute('data-vaccination-drug', 'true'); // Помечаем как препарат из вакцинации
                        }
                        const priceInput = lastDrugItem.querySelector('.item-price');
                        if (priceInput) {
                            priceInput.value = drug.price || 0; // Устанавливаем цену
                        }
                        calculateItemTotal.call(priceInput); // Обновляем тоталы
                    }
                });
            })
            .catch(error => {
                console.error('Ошибка при получении препаратов вакцинации:', error);
            });
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
                const itemDiv = this.input.closest('.order-item');
                const itemType = itemDiv.querySelector('input[name*="[item_type]"]').value;
                
                if (itemType === 'lab_test') {
                    // Для анализов получаем цену из типа анализа
                    fetch(this.input.dataset.url + '?selected=' + value)
                        .then(response => response.json())
                        .then(data => {
                            const selectedItem = data.find(item => item.value == value);
                            if (selectedItem && selectedItem.price) {
                                const priceInput = itemDiv.querySelector('.item-price');
                                if (priceInput) {
                                    priceInput.value = selectedItem.price;
                                    calculateItemTotal.call(priceInput);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка при получении цены анализа:', error);
                        });
                } else if (itemType === 'vaccination') {
                    // Для вакцинаций получаем цену из типа вакцинации (за работу) и добавляем препараты
                    fetch(this.input.dataset.url + '?selected=' + value)
                        .then(response => response.json())
                        .then(data => {
                            const selectedItem = data.find(item => item.value == value);
                            if (selectedItem && selectedItem.price) {
                                const priceInput = itemDiv.querySelector('.item-price');
                                if (priceInput) {
                                    priceInput.value = selectedItem.price;
                                    calculateItemTotal.call(priceInput);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка при получении цены вакцинации:', error);
                        });
                    
                    // Добавляем препараты отдельно
                    addVaccinationDrugs(value, itemDiv);
                } else {
                    // Для услуг и препаратов устанавливаем цену по умолчанию
                    const priceInput = itemDiv.querySelector('.item-price');
                    if (value && value.price !== undefined) {
                        priceInput.value = value.price;
                        calculateItemTotal.call(priceInput);
                    } else {
                        fetch(this.input.dataset.url + '?selected=' + value)
                            .then(response => response.json())
                            .then(data => {
                                const selectedItem = data.find(item => item.value == value);
                                if (selectedItem && selectedItem.price) {
                                    priceInput.value = selectedItem.price;
                                    calculateItemTotal.call(priceInput);
                                }
                            })
                            .catch(error => {
                                console.error('Ошибка при получении цены элемента:', error);
                            });
                    }
                }
                
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
        const itemDiv = this.closest ? this.closest('.order-item') : this;
        const itemType = itemDiv.getAttribute('data-item-type');
        
        const quantityInput = itemDiv.querySelector('.item-quantity');
        const priceInput = itemDiv.querySelector('.item-price');
        
        // Для анализов и вакцинаций количество всегда 1, для остальных берем из поля
        let quantity = 1;
        if (quantityInput && !quantityInput.readOnly) {
            quantity = parseFloat(quantityInput.value) || 0;
        } else if (quantityInput) {
            quantity = parseFloat(quantityInput.value) || 0;
        }
        
        // Цена берется из поля ввода
        let price = 0;
        if (priceInput) {
            price = parseFloat(priceInput.value) || 0;
        }
        
        const total = quantity * price;
        
        const totalElement = itemDiv.querySelector('.item-total');
        if (totalElement) {
            totalElement.textContent = total.toFixed(2) + ' ₽';
        }
        calculateTotal();
    }

    function calculateTotal() {
        const items = document.querySelectorAll('.order-item');
        let total = 0;
        
        items.forEach(item => {
            const itemType = item.getAttribute('data-item-type');
            const quantityInput = item.querySelector('.item-quantity');
            const priceInput = item.querySelector('.item-price');
            
            // Для анализов и вакцинаций количество всегда 1, для остальных берем из поля
            let quantity = 1;
            if (quantityInput && !quantityInput.readOnly) {
                quantity = parseFloat(quantityInput.value) || 0;
            } else if (quantityInput) {
                quantity = parseFloat(quantityInput.value) || 0;
            }
            
            // Цена берется из поля ввода
            let price = 0;
            if (priceInput) {
                price = parseFloat(priceInput.value) || 0;
            }
            
            total += quantity * price;
        });
        
        document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ₽';
        document.getElementById('total').value = total.toFixed(2);
    }

    // Функция для пересчета стоимости вакцинации
    function recalculateVaccinationCost(vaccinationId, vaccinationItem) {
        fetch(`{{ route('admin.vaccinations.drugs', 'VACCINATION_ID') }}`.replace('VACCINATION_ID', vaccinationId))
            .then(response => response.json())
            .then(drugs => {
                // Обновляем список препаратов в вакцинации
                const drugsList = vaccinationItem.querySelector('.vaccination-drugs-list');
                if (drugsList) {
                    drugsList.innerHTML = '';
                    drugs.forEach(drug => {
                        const li = document.createElement('li');
                        li.className = 'mb-1';
                        li.innerHTML = `<small class="text-muted">• ${drug.name} - ${drug.dosage} шт.</small>`;
                        drugsList.appendChild(li);
                    });
                }
            })
            .catch(error => {
                console.error('Ошибка при получении препаратов вакцинации:', error);
            });
    }
</script>
@endpush 