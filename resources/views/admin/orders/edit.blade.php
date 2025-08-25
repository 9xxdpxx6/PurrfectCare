@extends('layouts.admin')

@section('title', 'Редактировать заказ')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать заказ #{{ $item->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<form action="{{ route('admin.orders.update', $item) }}" method="POST" id="orderForm">
    @csrf
    @method('PATCH')
    
    @if ($errors->any())
        @php
            // Исключаем ошибки полей, которые уже показываются рядом с полями
            $fieldErrors = ['client_id', 'pet_id', 'status_id', 'branch_id', 'visits', 'notes'];
            $generalErrors = [];
            
            foreach ($errors->all() as $error) {
                $isFieldError = false;
                foreach ($fieldErrors as $field) {
                    // Проверяем, относится ли ошибка к конкретному полю
                    if ($errors->has($field)) {
                        $fieldErrorMessages = $errors->get($field);
                        if (in_array($error, $fieldErrorMessages)) {
                            $isFieldError = true;
                            break;
                        }
                    }
                }
                if (!$isFieldError) {
                    $generalErrors[] = $error;
                }
            }
        @endphp
        
        @if (count($generalErrors) > 0)
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($generalErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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
                            <select name="pet_id" id="pet_id" class="form-select @error('pet_id') is-invalid @enderror" data-url="{{ route('admin.orders.pet-options') }}">
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
                            <select name="status_id" id="status_id" class="form-select @error('status_id') is-invalid @enderror" data-url="{{ route('admin.orders.status-options') }}">
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
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" data-url="{{ route('admin.orders.branch-options') }}">
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
                        
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid', $item->is_paid) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_paid">
                                        Оплачен
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_closed" id="is_closed" value="1" {{ old('is_closed', $item->closed_at) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_closed">
                                        Выполнен
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="visits" class="form-label">Связанные приемы</label>
                            @php
                                $visits = $item->visits;
                            @endphp
                            <select name="visits[]" id="visits" class="form-select @error('visits') is-invalid @enderror" multiple data-url="{{ route('admin.orders.visit-options') }}">
                                @if(old('visits'))
                                    @foreach(old('visits') as $visitId)
                                        @php
                                            $selectedVisit = \App\Models\Visit::with(['client', 'pet', 'status'])->find($visitId);
                                        @endphp
                                        @if($selectedVisit)
                                            <option value="{{ $selectedVisit->id }}" selected>
                                                Прием от {{ $selectedVisit->starts_at->format('d.m.Y H:i') }}
                                                @if($selectedVisit->client) - {{ $selectedVisit->client->name }} @endif
                                                @if($selectedVisit->pet) ({{ $selectedVisit->pet->name }}) @endif
                                                @if($selectedVisit->status) [{{ $selectedVisit->status->name }}] @endif
                                            </option>
                                        @endif
                                    @endforeach
                                @else
                                    @foreach($item->visits as $visit)
                                        <option value="{{ $visit->id }}" selected>
                                            Прием от {{ $visit->starts_at->format('d.m.Y H:i') }}
                                            @if($visit->client) - {{ $visit->client->name }} @endif
                                            @if($visit->pet) ({{ $visit->pet->name }}) @endif
                                            @if($visit->status) [{{ $visit->status->name }}] @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('visits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Выберите приемы, связанные с этим заказом</small>
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
                    @php
                        // Отладочная информация
                        // dd([
                        //     'items_count' => $item->items->count(),
                        //     'items' => $item->items->map(function($item) {
                        //         return [
                        //             'id' => $item->id,
                        //             'item_type' => $item->item_type,
                        //             'item_id' => $item->item_id,
                        //             'itemable' => $item->itemable ? $item->itemable->name : null,
                        //             'item' => $item->item ? $item->item->name : null,
                        //         ];
                        //     })->toArray(),
                        // ]);
                    @endphp
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
                            @php
                                $serviceItems = $item->items->where('item_type', 'App\Models\Service');
                                // dd($serviceItems->toArray());
                            @endphp
                            @foreach($serviceItems as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="service">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Услуга</label>
                                                                                                <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.service-options') }}">
                                                @if($orderItem->itemable)
                                                    <option value="{{ $orderItem->itemable->id }}" selected>{{ $orderItem->itemable->name }}</option>
                                                @elseif($orderItem->item)
                                                    <option value="{{ $orderItem->item->id }}" selected>{{ $orderItem->item_name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="service">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="0.01" max="9999" step="0.01">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01">
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
                            @php
                                $drugItems = $item->items->where('item_type', 'App\Models\Drug');
                                // dd($drugItems->toArray());
                            @endphp
                            @foreach($drugItems as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="drug">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5">
                                            <label class="form-label">Препарат</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.drug-options') }}">
                                                @if($orderItem->itemable)
                                                    <option value="{{ $orderItem->itemable->id }}" selected>{{ $orderItem->itemable->name }}</option>
                                                @elseif($orderItem->item)
                                                    <option value="{{ $orderItem->item->id }}" selected>{{ $orderItem->item_name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="drug">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Кол-во</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $orderItem->quantity }}" min="0.01" max="9999" step="0.01">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01">
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
                            @php
                                $labTestItems = $item->items->where('item_type', 'App\Models\LabTestType');
                                // dd($labTestItems->toArray());
                            @endphp
                            @foreach($labTestItems as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="lab_test">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-8">
                                            <label class="form-label">Анализ</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.lab-test-options') }}">
                                                @if($orderItem->itemable)
                                                    <option value="{{ $orderItem->itemable->id }}" selected>{{ $orderItem->itemable->name }}</option>
                                                @elseif($orderItem->item)
                                                    <option value="{{ $orderItem->item->id }}" selected>{{ $orderItem->item_name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="lab_test">
                                            <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $orderItem->quantity }}">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->unit_price }}" min="0" max="999999.99" step="0.01">
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
                            @php
                                $vaccinationItems = $item->items->where('item_type', 'App\Models\VaccinationType');
                                // dd($vaccinationItems->toArray());
                            @endphp
                            @foreach($vaccinationItems as $index => $orderItem)
                                <div class="order-item border rounded p-3 mb-3" data-item-index="{{ $index }}" data-item-type="vaccination">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-8">
                                            <label class="form-label">Вакцинация</label>
                                            <select name="items[{{ $index }}][item_id]" class="form-select item-select" data-url="{{ route('admin.orders.vaccination-options') }}">
                                                @if($orderItem->itemable)
                                                    <option value="{{ $orderItem->itemable->id }}" selected>{{ $orderItem->itemable->name }}</option>
                                                @elseif($orderItem->item)
                                                    <option value="{{ $orderItem->item->id }}" selected>{{ $orderItem->item_name }}</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_type]" value="vaccination">
                                            <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $orderItem->quantity }}">
                                        </div>
                                        
                                        <div class="col-6 col-lg-3">
                                            <label class="form-label">Цена</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-price" value="{{ $orderItem->itemable->price ?? $orderItem->item->price ?? 0 }}" min="0" max="999999.99" step="0.01">
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
                                                @if($orderItem->itemable && $orderItem->itemable->drugs)
                                                    @foreach($orderItem->itemable->drugs as $drug)
                                                        <li class="mb-1">
                                                            <small class="text-muted">• {{ $drug->name }} - {{ $drug->pivot->dosage }} шт.</small>
                                                        </li>
                                                    @endforeach
                                                @elseif($orderItem->item && $orderItem->item->vaccinationType && $orderItem->item->vaccinationType->drugs)
                                                    @foreach($orderItem->item->vaccinationType->drugs as $drug)
                                                        <li class="mb-1">
                                                            <small class="text-muted">• {{ $drug->name }} - {{ $drug->pivot->dosage }} шт.</small>
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
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
                            <input type="hidden" name="total" id="total" value="{{ $item->total }}">
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

@php
    $itemCount = $item->items->count();
    
    // Отладочная информация
    // dd([
    //     'items_count' => $item->items->count(),
    //     'visits_count' => $item->visits->count(),
    //     'items' => $item->items->map(function($item) {
    //         return [
    //             'id' => $item->id,
    //             'item_type' => $item->item_type,
    //             'item_id' => $item->item_id,
    //             'itemable' => $item->itemable ? $item->itemable->name : null,
    //             'item' => $item->item ? $item->item->name : null,
    //         ];
    //     })->toArray(),
    //     'visits' => $item->visits->map(function($visit) {
    //         return [
    //             'id' => $visit->id,
    //             'starts_at' => $visit->starts_at,
    //             'client_name' => $visit->client ? $visit->client->name : null,
    //             'pet_name' => $visit->pet ? $visit->pet->name : null,
    //             'status_name' => $visit->status ? $visit->status->name : null,
    //         ];
    //     })->toArray(),
    // ]);
@endphp
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
    let itemIndex = parseInt('{!! $itemCount ?? 0 !!}') || 0;
    let petTomSelect; // Глобальная переменная для доступа из других функций
    
    const itemUrls = {
        service: '{{ route("admin.orders.service-options") }}',
        drug: '{{ route("admin.orders.drug-options") }}',
        lab_test: '{{ route("admin.orders.lab-test-options") }}',
        vaccination: '{{ route("admin.orders.vaccination-options") }}'
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Обработчик отправки формы
        const orderForm = document.getElementById('orderForm');
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
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
        }
        
        const clientSelect = document.getElementById('client_id');
        const petSelect = document.getElementById('pet_id');
        
        // Получаем предустановленные значения
        const selectedVisits = JSON.parse('{!! json_encode(old("visits", $item->visits->pluck("id")->toArray())) !!}');
        
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



        // Загрузка опций питомцев для клиента (без сброса текущего значения)
        function loadPetOptionsForClient(clientId, isInitialization = false) {
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
                    // Очищаем только опции, но не значение
                    petTomSelect.clearOptions();
                    
                    data.forEach(option => {
                        petTomSelect.addOption(option);
                    });
                    
                    // При инициализации восстанавливаем значение питомца
                    if (isInitialization) {
                        const currentPetIdFromServer = '{!! old("pet_id", $item->pet_id) !!}';
                        const currentClientIdFromServer = '{!! old("client_id", $item->client_id) !!}';
                        if (currentPetIdFromServer && clientId === currentClientIdFromServer) {
                            petTomSelect.setValue(currentPetIdFromServer);
                            
                            // После установки питомца предзагружаем приемы
                            setTimeout(() => {
                                if (visitsTomSelect && visitsTomSelect.load) {
                                    visitsTomSelect.load('');
                                }
                            }, 100);
                        }
                    }
                })
                .catch(() => {
                    petTomSelect.disable();
                });
        }
        
        // Фильтрация питомцев по клиенту (с полным сбросом)
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
                    
                    // Проверяем, принадлежит ли текущий питомец новому клиенту
                    const currentPetId = petTomSelect.getValue();
                    if (currentPetId) {
                        const currentPetOption = data.find(option => option.value == currentPetId);
                        if (!currentPetOption) {
                            petTomSelect.clear(); // Очищаем только если питомец не принадлежит клиенту
                        }
                    }
                })
                .catch(() => {
                    petTomSelect.disable();
                });
        }
        
        // Устанавливаем флаг инициализации для TomSelect элементов
        clientTomSelect.isInitializing = true;
        petTomSelect.isInitializing = true;
        
        // Слушатель изменения клиента
        clientTomSelect.on('change', function(value) {
            filterPetsByClient(value);
        });
        
        // Инициализация при загрузке страницы
        const initialClientId = clientTomSelect.getValue();
        if (initialClientId) {
            // При инициализации не сбрасываем питомца, только загружаем опции
            loadPetOptionsForClient(initialClientId, true);
            
            // Предзагружаем приемы для текущего клиента
            setTimeout(() => {
                if (visitsTomSelect && visitsTomSelect.load) {
                    visitsTomSelect.load('');
                }
            }, 200);
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
            
            // Обновляем опции для анализов и вакцинаций при изменении питомца
            updateLabTestAndVaccinationOptions();
            
            // Сохраняем текущее значение для следующей проверки
            this.lastValue = value;
            
            // При изменении питомца НЕ сбрасываем приемы, если это не инициализация
            if (!this.isInitializing) {
                // Обновляем только фильтрацию приемов по питомцу, но не очищаем их
                updateVisitFilterByPet(value);
            }
        });
        
        // Инициализация состояния кнопок
        updatePetDependentButtons();
        
        // Функция для обновления опций анализов и вакцинаций
        function updateLabTestAndVaccinationOptions() {
            const petId = petTomSelect.getValue();
            
            // Обновляем опции для анализов
            const labTestSelects = document.querySelectorAll('.order-item[data-item-type="lab_test"] .item-select');
            labTestSelects.forEach(select => {
                if (select.tomselect) {
                    select.tomselect.clear();
                    select.tomselect.clearOptions();
                    select.tomselect.load('');
                }
            });
            
            // Обновляем опции для вакцинаций
            const vaccinationSelects = document.querySelectorAll('.order-item[data-item-type="vaccination"] .item-select');
            vaccinationSelects.forEach(select => {
                if (select.tomselect) {
                    select.tomselect.clear();
                    select.tomselect.clearOptions();
                    select.tomselect.load('');
                }
            });
        }
        
        // Функция для обновления фильтрации приемов по питомцу (без сброса)
        function updateVisitFilterByPet(petId) {
            // Если приемы еще не инициализированы, не делаем ничего
            if (!visitsTomSelect.visitsInitialized) {
                return;
            }
            
            // Если это инициализация, не сбрасываем приемы
            if (clientTomSelect.isInitializing || petTomSelect.isInitializing) {
                return;
            }
            
            // Получаем текущие приемы
            const currentVisits = visitsTomSelect.getValue();
            if (!currentVisits || currentVisits.length === 0) {
                return; // Если приемов нет, ничего не делаем
            }
            
            // Проверяем, соответствуют ли текущие приемы выбранному питомцу
            const currentVisitOptions = visitsTomSelect.options;
            let shouldKeepVisits = true;
            
            currentVisits.forEach(visitId => {
                const visitOption = currentVisitOptions[visitId];
                if (visitOption && visitOption.pet_id && visitOption.pet_id != petId) {
                    shouldKeepVisits = false;
                }
            });
            
            // Если приемы не соответствуют питомцу, очищаем их
            if (!shouldKeepVisits) {
                visitsTomSelect.clear();
                visitsTomSelect.clearOptions();
                // Перезагружаем приемы с новым фильтром
                const clientId = clientTomSelect.getValue();
                if (clientId) {
                    visitsTomSelect.load('');
                }
            }
        }
        
        // TomSelect для приемов
        const visitsTomSelect = new createTomSelect('#visits', {
            onInitialize: function() {
                console.log('TomSelect для приемов инициализирован');
                // После инициализации устанавливаем выбранные значения
                if (selectedVisits && selectedVisits.length > 0) {
                    console.log('Устанавливаем выбранные приемы:', selectedVisits);
                    selectedVisits.forEach(visitId => {
                        this.setValue(visitId);
                    });
                }
                
                // Помечаем, что приемы инициализированы
                this.visitsInitialized = true;
            },
            placeholder: 'Выберите приемы...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: false,
            load: function(query, callback) {
                const clientId = clientTomSelect.getValue();
                
                // Если клиент не выбран, не загружаем приемы
                if (!clientId) {
                    callback([]);
                    return;
                }
                
                let url = this.input.dataset.url + '?q=' + encodeURIComponent(query) + '&filter=false&client_id=' + clientId;
                
                // Если выбран питомец, фильтруем по нему
                const petId = petTomSelect.getValue();
                if (petId) {
                    url += '&pet_id=' + petId;
                }
                
                // Добавляем текущую дату для фильтрации (приемы должны быть до даты заказа)
                const currentDate = new Date().toISOString().split('T')[0];
                url += '&order_date=' + currentDate;
                
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
        
        // Обновляем список приемов при изменении клиента или питомца
        function updateVisitOptions() {
            const clientId = clientTomSelect.getValue();
            
            // Если приемы еще не инициализированы, не делаем ничего
            if (!visitsTomSelect.visitsInitialized) {
                return;
            }
            
            // Если это инициализация, не сбрасываем приемы
            if (clientTomSelect.isInitializing || petTomSelect.isInitializing) {
                return;
            }
            
            // Проверяем, есть ли уже загруженные приемы
            const currentVisits = visitsTomSelect.getValue();
            const hasCurrentVisits = currentVisits && currentVisits.length > 0;
            
            // Если есть текущие приемы, проверяем, соответствуют ли они новому клиенту
            if (hasCurrentVisits) {
                // Получаем информацию о текущих приемах
                const currentVisitOptions = visitsTomSelect.options;
                let shouldKeepVisits = true;
                
                // Проверяем, принадлежат ли текущие приемы новому клиенту
                currentVisits.forEach(visitId => {
                    const visitOption = currentVisitOptions[visitId];
                    if (visitOption && visitOption.client_id && visitOption.client_id != clientId) {
                        shouldKeepVisits = false;
                    }
                });
                
                // Если приемы не соответствуют новому клиенту, очищаем их
                if (!shouldKeepVisits) {
                    visitsTomSelect.clear();
                    visitsTomSelect.clearOptions();
                }
            } else {
                // Если приемов нет, очищаем опции
                visitsTomSelect.clear();
                visitsTomSelect.clearOptions();
            }
            
            if (clientId) {
                // Если клиент выбран, загружаем приемы
                visitsTomSelect.load('');
            }
        }
        
        // Слушатели изменения клиента и питомца для обновления приемов (только при изменении пользователем)
        clientTomSelect.on('change', function(value) {
            // Очищаем приемы только при ручном изменении клиента 
            if (this.isInitializing !== true) {
                updateVisitOptions();
            }
        });
        
        petTomSelect.on('change', function(value) {
            // Очищаем приемы только при ручном изменении питомца
            if (this.isInitializing !== true) {
                updateVisitOptions();
            }
        });

        
        // Инициализируем lastValue для petTomSelect
        petTomSelect.lastValue = petTomSelect.getValue();
        
        // Восстанавливаем приемы после установки питомца, но НЕ очищаем их
        setTimeout(() => {
            console.log('Восстановление приемов...');
            // Восстанавливаем выбранные приемы при ошибках валидации
            if (selectedVisits && selectedVisits.length > 0) {
                console.log('Выбранные приемы:', selectedVisits);
                // Приемы уже есть в DOM в option-ах, просто устанавливаем их в TomSelect
                selectedVisits.forEach(visitId => {
                    visitsTomSelect.setValue(visitId);
                });
            }
            
            // Убираем флаг инициализации после восстановления всех значений
            clientTomSelect.isInitializing = false;
            petTomSelect.isInitializing = false;
            
            console.log('Инициализация завершена, приемы сохранены');
        }, 1000); // Увеличиваем задержку для полной загрузки питомца и приемов
        
        // Дополнительная проверка через 1.5 секунды для гарантии сохранения приемов
        setTimeout(() => {
            if (visitsTomSelect && visitsTomSelect.visitsInitialized) {
                console.log('Финальная проверка приемов...');
                const currentVisits = visitsTomSelect.getValue();
                console.log('Текущие приемы после инициализации:', currentVisits);
            }
        }, 1500);
        
        // Инициализируем TomSelect для существующих элементов заказа с задержкой
        setTimeout(() => {
            console.log('Инициализация существующих элементов заказа...');
            const existingItems = document.querySelectorAll('.order-item');
            console.log('Найдено элементов:', existingItems.length);
            
            existingItems.forEach((item, index) => {
                const itemSelect = item.querySelector('.item-select');
                const itemType = item.getAttribute('data-item-type');
                
                console.log(`Элемент ${index}:`, { itemType, hasSelect: !!itemSelect, hasUrl: !!itemUrls[itemType] });
                
                if (itemSelect && itemType && itemUrls[itemType]) {
                    itemSelect.dataset.url = itemUrls[itemType];
                    
                    // Проверяем, есть ли уже выбранное значение
                    const hasSelectedValue = itemSelect.querySelector('option[selected]');
                    console.log(`Элемент ${index} имеет выбранное значение:`, !!hasSelectedValue);
                    
                    initItemTomSelect(itemSelect, itemType);
                    
                    // Для анализов и вакцинаций сразу загружаем последние 20 записей
                    if ((itemType === 'lab_test' || itemType === 'vaccination') && itemSelect.tomselect) {
                        setTimeout(() => {
                            itemSelect.tomselect.load('');
                        }, 200);
                    }
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
            
            // Сначала помечаем существующие препараты от вакцинаций с задержкой
            setTimeout(() => {
                markExistingVaccinationDrugs();
            }, 100);
            
            // Обновляем тоталы после инициализации всех элементов
            calculateTotal();
        }, 500);
        
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
        
        // Для анализов и вакцинаций сразу загружаем последние 20 записей
        if ((itemType === 'lab_test' || itemType === 'vaccination') && itemSelect.tomselect) {
            setTimeout(() => {
                itemSelect.tomselect.load('');
            }, 100);
        }
        
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

    function addVaccinationDrugs(vaccinationTypeId, vaccinationItemDiv = null, isChange = false) {
        // Если это изменение типа вакцинации, сначала удаляем старые препараты
        if (isChange && vaccinationItemDiv) {
            removeVaccinationDrugs(vaccinationItemDiv);
        }
        
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
                        
                        // Помечаем препарат как связанный с вакцинацией
                        const vaccinationIndex = vaccinationItemDiv ? vaccinationItemDiv.getAttribute('data-item-index') : null;
                        if (vaccinationIndex) {
                            lastDrugItem.setAttribute('data-vaccination-index', vaccinationIndex);
                        }
                        
                        calculateItemTotal.call(priceInput); // Обновляем тоталы
                    }
                });
            })
            .catch(error => {
                console.error('Ошибка при получении препаратов вакцинации:', error);
            });
    }

    // Функция для удаления препаратов, связанных с вакцинацией
    function removeVaccinationDrugs(vaccinationItemDiv) {
        const vaccinationIndex = vaccinationItemDiv.getAttribute('data-item-index');
        if (!vaccinationIndex) return;
        
        // Находим все препараты, связанные с этой вакцинацией
        const drugItems = document.getElementById('drugItems');
        const relatedDrugs = drugItems.querySelectorAll(`[data-vaccination-index="${vaccinationIndex}"]`);
        
        // Удаляем связанные препараты
        relatedDrugs.forEach(drugItem => {
            drugItem.remove();
        });
        
        // Пересчитываем общую сумму
        calculateTotal();
    }

    function initItemTomSelect(select, type) {
        console.log('Инициализация TomSelect для:', type, select);
        const url = itemUrls[type];
        if (!url) {
            console.error('URL не найден для типа:', type);
            return;
        }
        
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
                
                // Если это анализы или вакцинации и запрос пустой, загружаем последние 20 записей
                if ((type === 'lab_test' || type === 'vaccination') && !query.trim()) {
                    url = this.input.dataset.url + '?recent=20&filter=false';
                    if (type === 'lab_test' || type === 'vaccination') {
                        if (petTomSelect && petTomSelect.getValue) {
                            const petId = petTomSelect.getValue();
                            if (petId) {
                                url += '&pet_id=' + petId;
                            }
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
                    
                    // Проверяем, есть ли уже связанные препараты с этой вакцинацией
                    const vaccinationIndex = itemDiv.getAttribute('data-item-index');
                    const hasRelatedDrugs = document.querySelectorAll(`[data-vaccination-index="${vaccinationIndex}"]`).length > 0;
                    const isChange = hasRelatedDrugs;
                    
                    // Добавляем препараты отдельно
                    addVaccinationDrugs(value, itemDiv, isChange);
                    
                    // Помечаем, что значение установлено
                    this.input.setAttribute('data-has-value', 'true');
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
        const itemType = itemDiv.getAttribute('data-item-type');
        
        // Если удаляется вакцинация, сначала удаляем связанные препараты
        if (itemType === 'vaccination') {
            removeVaccinationDrugs(itemDiv);
        }
        
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

    // Функция для пометки существующих препаратов от вакцинаций
    function markExistingVaccinationDrugs() {
        // Получаем все вакцинации из заказа
        const vaccinationItems = document.querySelectorAll('.order-item[data-item-type="vaccination"]');
        
        vaccinationItems.forEach(vaccinationItem => {
            const vaccinationSelect = vaccinationItem.querySelector('.item-select option[selected]');
            if (!vaccinationSelect) return;
            
            const vaccinationTypeId = vaccinationSelect.value;
            const vaccinationIndex = vaccinationItem.getAttribute('data-item-index');
            
            // Получаем препараты этой вакцинации
            fetch(`{{ route('admin.vaccination-types.drugs', 'VACCINATION_TYPE_ID') }}`.replace('VACCINATION_TYPE_ID', vaccinationTypeId))
                .then(response => response.json())
                .then(drugs => {
                    const drugIds = drugs.map(drug => drug.id.toString());
                    
                    // Находим препараты в заказе, которые соответствуют препаратам вакцинации
                    const drugItems = document.querySelectorAll('.order-item[data-item-type="drug"]');
                    drugItems.forEach(drugItem => {
                        const drugSelect = drugItem.querySelector('.item-select option[selected]');
                        if (!drugSelect) return;
                        
                        const drugId = drugSelect.value;
                        if (drugIds.includes(drugId)) {
                            // Помечаем препарат как препарат вакцинации
                            const quantityInput = drugItem.querySelector('.item-quantity');
                            if (quantityInput) {
                                quantityInput.setAttribute('data-vaccination-drug', 'true');
                                quantityInput.readOnly = true;
                                // Связываем с вакцинацией
                                drugItem.setAttribute('data-vaccination-index', vaccinationIndex);
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Ошибка при получении препаратов вакцинации:', error);
                });
        });
    }

    // Функция для пересчета стоимости вакцинации
    function recalculateVaccinationCost(vaccinationTypeId, vaccinationItem) {
        fetch(`{{ route('admin.vaccination-types.drugs', 'VACCINATION_TYPE_ID') }}`.replace('VACCINATION_TYPE_ID', vaccinationTypeId))
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