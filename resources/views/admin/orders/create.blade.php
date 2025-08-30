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
    
    @if(isset($selectedVisitId) && $selectedVisitId)
        <input type="hidden" name="visit_id" value="{{ $selectedVisitId }}">
    @endif
    
    @if ($errors->any())
        @php
            // Отладочная информация для анализов и вакцинаций
            $debugOldItems = old('items', []);
            $debugLabTests = [];
            $debugVaccinations = [];
            
            foreach ($debugOldItems as $index => $item) {
                if (isset($item['item_type'])) {
                    if ($item['item_type'] === 'lab_test') {
                        $labTestType = null;
                        if (isset($item['item_id']) && $item['item_id']) {
                            $labTestType = \App\Models\LabTestType::find($item['item_id']);
                        }
                        $debugLabTests[] = [
                            'index' => $index,
                            'item_id' => $item['item_id'] ?? 'не задан',
                            'quantity' => $item['quantity'] ?? 'не задан',
                            'unit_price' => $item['unit_price'] ?? 'не задан',
                            'name' => $labTestType ? $labTestType->name : 'не найдено'
                        ];
                    } elseif ($item['item_type'] === 'vaccination') {
                        $vaccinationType = null;
                        if (isset($item['item_id']) && $item['item_id']) {
                            $vaccinationType = \App\Models\VaccinationType::find($item['item_id']);
                        }
                        $debugVaccinations[] = [
                            'index' => $index,
                            'item_id' => $item['item_id'] ?? 'не задан',
                            'quantity' => $item['quantity'] ?? 'не задан',
                            'unit_price' => $item['unit_price'] ?? 'не задан',
                            'name' => $vaccinationType ? $vaccinationType->name : 'не найдено'
                        ];
                    }
                }
            }
            
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
        
        {{-- Отладочная информация (только при наличии ошибок) --}}
        @if (count($debugLabTests) > 0 || count($debugVaccinations) > 0)
            <div class="alert alert-info">
                <h6>Отладочная информация:</h6>
                @if (count($debugLabTests) > 0)
                    <p><strong>Анализы в old():</strong></p>
                    <ul class="mb-2">
                        @foreach ($debugLabTests as $labTest)
                            <li>Индекс {{ $labTest['index'] }}: ID={{ $labTest['item_id'] }}, Название="{{ $labTest['name'] }}", Цена={{ $labTest['unit_price'] }}</li>
                        @endforeach
                    </ul>
                @endif
                @if (count($debugVaccinations) > 0)
                    <p><strong>Вакцинации в old():</strong></p>
                    <ul class="mb-0">
                        @foreach ($debugVaccinations as $vaccination)
                            <li>Индекс {{ $vaccination['index'] }}: ID={{ $vaccination['item_id'] }}, Название="{{ $vaccination['name'] }}", Цена={{ $vaccination['unit_price'] }}</li>
                        @endforeach
                    </ul>
                @endif
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
                        
                        <div class="col-md-6">
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
                        
                        <div class="col-md-6">
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
                        
                        <div class="col-lg-6">
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
                            <label for="visits" class="form-label">Связанные приемы</label>
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
                                @endif
                            </select>
                            @error('visits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Выберите приемы, связанные с этим заказом</small>
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
                                <button type="button" class="btn btn-success btn-sm w-100" onclick="addDrugItem()" id="addDrugBtn">
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
                                                            // Отладочная информация
                                                            $debugItemId = $item['item_id'] ?? 'не найден';
                                                            $labTestType = null;
                                                            if (isset($item['item_id']) && $item['item_id']) {
                                                                $labTestType = \App\Models\LabTestType::find($item['item_id']);
                                                            }
                                                        @endphp
                                                        @if($labTestType)
                                                            <option value="{{ $labTestType->id }}" selected>{{ $labTestType->name }}</option>
                                                        @else
                                                            {{-- Отладка: показываем что не найдено --}}
                                                            <option value="" disabled selected>Тип анализа не найден (ID: {{ $debugItemId }})</option>
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
                                                            // Отладочная информация
                                                            $debugVaccinationItemId = $item['item_id'] ?? 'не найден';
                                                            $vaccinationType = null;
                                                            if (isset($item['item_id']) && $item['item_id']) {
                                                                $vaccinationType = \App\Models\VaccinationType::find($item['item_id']);
                                                            }
                                                        @endphp
                                                        @if($vaccinationType)
                                                            <option value="{{ $vaccinationType->id }}" selected>{{ $vaccinationType->name }}</option>
                                                        @else
                                                            {{-- Отладка: показываем что не найдено --}}
                                                            <option value="" disabled selected>Тип вакцинации не найден (ID: {{ $debugVaccinationItemId }})</option>
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
    let branchTomSelect; // Глобальная переменная для доступа из других функций
    
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
        const selectedClientId = '{{ old("client_id", $selectedClientId ?? "") }}';
        const selectedPetId = '{{ old("pet_id", $selectedPetId ?? "") }}';
        const selectedVisitId = '{{ $selectedVisitId ?? "" }}';
        const selectedVisits = JSON.parse('@json(old("visits", isset($selectedVisitId) && $selectedVisitId ? [$selectedVisitId] : []))');
        
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
        
        // Устанавливаем флаг инициализации для TomSelect элементов
        clientTomSelect.isInitializing = true;
        petTomSelect.isInitializing = true;
        
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
                
                // И с еще большей задержкой устанавливаем приемы
                setTimeout(() => {
                    if (selectedVisits && selectedVisits.length > 0 && selectedClientId) {
                        // Загружаем опции для выбранных приемов
                        const visitsUrl = document.getElementById('visits').dataset.url + '?selected=' + selectedVisits.join(',') + '&filter=false&client_id=' + selectedClientId;
                        
                        fetch(visitsUrl)
                            .then(response => response.json())
                            .then(options => {
                                // Добавляем опции в TomSelect
                                options.forEach(option => {
                                    visitsTomSelect.addOption(option);
                                });
                                
                                // Устанавливаем выбранные значения
                                selectedVisits.forEach(visitId => {
                                    visitsTomSelect.setValue(visitId, true); // silent = true, чтобы не вызывать событие
                                });
                            })
                            .catch(error => {
                                console.error('Error loading selected visits:', error);
                            });
                    }
                    
                    // Убираем флаг инициализации после восстановления всех значений
                    clientTomSelect.isInitializing = false;
                    petTomSelect.isInitializing = false;
                    
                    console.log('Инициализация завершена, приемы сохранены');
                }, 300);
            }, 200);
        }, 100);
        
        // Дополнительная проверка через 1 секунду для гарантии сохранения приемов
        setTimeout(() => {
            console.log('Финальная проверка приемов...');
            const currentVisits = visitsTomSelect.getValue();
            console.log('Текущие приемы после инициализации:', currentVisits);
        }, 1000);
        
        // Обработчик изменения клиента
        clientTomSelect.on('change', function(value) {
            // Очищаем выбранного питомца при смене клиента
            petTomSelect.clear();
            // Обновляем список питомцев
            petTomSelect.refreshOptions();
            
            // Обновляем приемы только если это не инициализация
            if (!this.isInitializing) {
                updateVisitOptions();
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

        branchTomSelect = new createTomSelect('#branch_id', {
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
                        const currentPetIdFromServer = '{{ old("pet_id", $selectedPetId ?? "") }}';
                        const currentClientIdFromServer = '{{ old("client_id", $selectedClientId ?? "") }}';
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
            
            // Обновляем приемы только если это не инициализация
            if (!this.isInitializing) {
                updateVisitOptions();
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
        
        // TomSelect для приемов
        const visitsTomSelect = new createTomSelect('#visits', {
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
                    .then(response => {
                        return response.json();
                    })
                    .then(json => {
                        callback(json);
                    })
                    .catch(error => {
                        console.error('Error loading visits:', error);
                        callback([]);
                    });
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
            
            // Если это инициализация, не сбрасываем приемы
            if (clientTomSelect.isInitializing || petTomSelect.isInitializing) {
                return;
            }
            
            // Очищаем текущие опции
            visitsTomSelect.clear();
            visitsTomSelect.clearOptions();
            
            if (clientId) {
                // Если клиент выбран, принудительно загружаем приемы
                visitsTomSelect.load('');
            }
        }
        
        clientTomSelect.on('change', function(value) {
            updateVisitOptions();
        });
        
        petTomSelect.on('change', function(value) {
            updateVisitOptions();
        });

        
        // Инициализируем lastValue для petTomSelect
        petTomSelect.lastValue = petTomSelect.getValue();
        
        // Инициализируем TomSelect для существующих элементов заказа
        const existingItems = document.querySelectorAll('.order-item');
        existingItems.forEach((item, index) => {
            const itemSelect = item.querySelector('.item-select');
            const itemType = item.getAttribute('data-item-type');
            
            if (itemType && itemUrls[itemType]) {
                itemSelect.dataset.url = itemUrls[itemType];
                
                // Сохраняем текущее значение и текст option перед инициализацией TomSelect
                const selectedOption = itemSelect.querySelector('option[selected]');
                let selectedValue = null;
                let selectedText = null;
                
                if (selectedOption) {
                    selectedValue = selectedOption.value;
                    selectedText = selectedOption.textContent;
                    console.log(`Сохраняем значение для ${itemType}:`, selectedValue, selectedText);
                }
                
                initItemTomSelect(itemSelect, itemType);
                
                // Восстанавливаем значение после инициализации TomSelect
                if (selectedValue && selectedText) {
                    setTimeout(() => {
                        if (itemSelect.tomselect) {
                            // Добавляем опцию, если она не была загружена
                            const existingOption = itemSelect.tomselect.options[selectedValue];
                            if (!existingOption) {
                                itemSelect.tomselect.addOption({
                                    value: selectedValue,
                                    text: selectedText
                                });
                            }
                            // Устанавливаем значение
                            itemSelect.tomselect.setValue(selectedValue);
                            console.log(`Восстановлено значение для ${itemType}:`, selectedValue, selectedText);
                        }
                    }, 300); // Увеличиваем задержку
                }
                
                // Для анализов и вакцинаций загружаем данные только если нет выбранного значения
                if ((itemType === 'lab_test' || itemType === 'vaccination') && itemSelect.tomselect) {
                    setTimeout(() => {
                        // Загружаем данные только если нет выбранного значения
                        if (!selectedValue) {
                            itemSelect.tomselect.load('');
                        }
                    }, 500);
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
        
        // Для анализов и вакцинаций загружаем последние 20 записей при добавлении новых элементов
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
        fetch(`{{ route('admin.settings.vaccination-types.drugs', 'VACCINATION_TYPE_ID') }}`.replace('VACCINATION_TYPE_ID', vaccinationTypeId))
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
        const url = itemUrls[type];
        if (!url) return;
        
        // Для анализов и вакцинаций не используем preload, чтобы сохранить исходные option
        const shouldPreload = !(type === 'lab_test' || type === 'vaccination');
        
        new createTomSelect(select, {
            placeholder: 'Выберите элемент...',
            valueField: 'value',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: false,
            preload: shouldPreload,
            onInitialize: function() {
                // Для анализов и вакцинаций сохраняем исходные option элементы
                if (type === 'lab_test' || type === 'vaccination') {
                    const selectedOption = this.input.querySelector('option[selected]');
                    if (selectedOption) {
                        console.log(`Найден выбранный элемент ${type}:`, selectedOption.value, selectedOption.textContent);
                        // Убеждаемся, что опция добавлена в TomSelect
                        this.addOption({
                            value: selectedOption.value,
                            text: selectedOption.textContent
                        });
                        // Устанавливаем значение
                        this.setValue(selectedOption.value);
                    }
                }
            },
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
                    
                    // Проверяем, есть ли уже выбранное значение (это значит, что происходит изменение)
                    const isChange = this.input.hasAttribute('data-has-value');
                    
                    // Добавляем препараты отдельно
                    addVaccinationDrugs(value, itemDiv, isChange);
                    
                    // Помечаем, что значение установлено
                    this.input.setAttribute('data-has-value', 'true');
                } else if (itemType === 'drug') {
                    // Для препаратов устанавливаем цену
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
                                console.error('Ошибка при получении данных препарата:', error);
                            });
                    }
                } else {
                    // Для услуг устанавливаем цену по умолчанию
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