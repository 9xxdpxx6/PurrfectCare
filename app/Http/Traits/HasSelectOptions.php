<?php

namespace App\Http\Traits;

use App\Models\Supplier;
use App\Models\Drug;
use App\Models\Specialty;
use Illuminate\Http\Request;

trait HasSelectOptions
{
    /**
     * Получить опции для селекта поставщиков
     */
    public function supplierOptions(Request $request)
    {
        $query = Supplier::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранный поставщик, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedSupplier = Supplier::find($selectedId);
            if ($selectedSupplier) {
                $options[] = [
                    'value' => $selectedSupplier->id,
                    'text' => $selectedSupplier->name
                ];
                // Исключаем выбранного поставщика из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $suppliers = $query->limit(20)->get();

        // Добавляем остальных поставщиков
        foreach ($suppliers as $supplier) {
            $options[] = [
                'value' => $supplier->id,
                'text' => $supplier->name
            ];
        }

        return response()->json($options);
    }

    /**
     * Получить опции для селекта препаратов
     */
    public function drugOptions(Request $request)
    {
        $query = Drug::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        $includePrice = $request->input('include_price', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранный препарат, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedDrug = Drug::with('unit')->find($selectedId);
            if ($selectedDrug) {
                $unit = $selectedDrug->unit ? $selectedDrug->unit->symbol : null;
                $option = [
                    'value' => $selectedDrug->id,
                    'text' => $selectedDrug->name . ($unit ? ' (' . $unit . ')' : '')
                ];
                if ($includePrice) {
                    $option['price'] = $selectedDrug->price;
                }
                $options[] = $option;
                // Исключаем выбранный препарат из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $drugs = $query->with('unit')->limit(20)->get();

        // Добавляем остальные препараты
        foreach ($drugs as $drug) {
            $unit = $drug->unit ? $drug->unit->symbol : null;
            $option = [
                'value' => $drug->id,
                'text' => $drug->name . ($unit ? ' (' . $unit . ')' : '')
            ];
            if ($includePrice) {
                $option['price'] = $drug->price;
            }
            $options[] = $option;
        }

        return response()->json($options);
    }

    /**
     * Получить опции для селекта специальностей
     */
    public function specialtyOptions(Request $request)
    {
        $query = Specialty::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранная специальность, добавляем её первой (если она не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedSpecialty = Specialty::find($selectedId);
            if ($selectedSpecialty) {
                $options[] = [
                    'value' => $selectedSpecialty->id,
                    'text' => $selectedSpecialty->name
                ];
                // Исключаем выбранную специальность из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $specialties = $query->limit(20)->get();

        // Добавляем остальные специальности
        foreach ($specialties as $specialty) {
            $options[] = [
                'value' => $specialty->id,
                'text' => $specialty->name
            ];
        }

        return response()->json($options);
    }

    /**
     * Получить опции для селекта ветеринаров
     */
    public function veterinarianOptions(Request $request)
    {
        $query = \App\Models\Employee::query()
            ->whereHas('specialties', function($q) {
                $q->where('is_veterinarian', true);
            });
            
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранный ветеринар, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedVeterinarian = \App\Models\Employee::find($selectedId);
            if ($selectedVeterinarian) {
                $options[] = [
                    'value' => $selectedVeterinarian->id,
                    'text' => $selectedVeterinarian->name
                ];
                // Исключаем выбранного ветеринара из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $veterinarians = $query->limit(20)->get();

        // Добавляем остальных ветеринаров
        foreach ($veterinarians as $veterinarian) {
            $options[] = [
                'value' => $veterinarian->id,
                'text' => $veterinarian->name
            ];
        }

        return response()->json($options);
    }



    /**
     * Получить опции для селекта филиалов
     */
    public function branchOptions(Request $request)
    {
        $query = \App\Models\Branch::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранный филиал, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedBranch = \App\Models\Branch::find($selectedId);
            if ($selectedBranch) {
                $options[] = [
                    'value' => $selectedBranch->id,
                    'text' => $selectedBranch->name
                ];
                // Исключаем выбранный филиал из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $branches = $query->limit(20)->get();

        // Добавляем остальные филиалы
        foreach ($branches as $branch) {
            $options[] = [
                'value' => $branch->id,
                'text' => $branch->name
            ];
        }

        return response()->json($options);
    }

    /**
     * Получить опции для селекта владельцев (клиентов)
     */
    public function ownerOptions(Request $request)
    {
        $query = \App\Models\User::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранный владелец, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedOwner = \App\Models\User::find($selectedId);
            if ($selectedOwner) {
                $options[] = [
                    'value' => $selectedOwner->id,
                    'text' => $selectedOwner->name
                ];
                // Исключаем выбранного владельца из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $owners = $query->limit(20)->get();

        // Добавляем остальных владельцев
        foreach ($owners as $owner) {
            $options[] = [
                'value' => $owner->id,
                'text' => $owner->name
            ];
        }

        return response()->json($options);
    }

    /**
     * Получить опции для селекта услуг
     */
    public function serviceOptions(Request $request)
    {
        $query = \App\Models\Service::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        $includePrice = $request->input('include_price', false);

        $options = [];

        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }

        // Если есть выбранная услуга, добавляем её первой (если она не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedService = \App\Models\Service::find($selectedId);
            if ($selectedService) {
                $option = [
                    'value' => $selectedService->id,
                    'text' => $selectedService->name
                ];
                if ($includePrice) {
                    $option['price'] = $selectedService->price;
                }
                $options[] = $option;
                // Исключаем выбранную услугу из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $services = $query->limit(20)->get();

        // Добавляем остальные услуги
        foreach ($services as $service) {
            $option = [
                'value' => $service->id,
                'text' => $service->name
            ];
            if ($includePrice) {
                $option['price'] = $service->price;
            }
            $options[] = $option;
        }

        return response()->json($options);
    }

    public function clientOptions(Request $request)
    {
        $query = \App\Models\User::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }
        
        // Если есть выбранный клиент, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\User::find($selectedId);
            if ($selected) {
                $options[] = [
                    'value' => $selected->id,
                    'text' => $selected->name
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }
        
        $users = $query->limit(20)->get();
        
        // Добавляем остальных клиентов
        foreach ($users as $user) {
            $options[] = [
                'value' => $user->id,
                'text' => $user->name
            ];
        }
        
        return response()->json($options);
    }

    public function petOptions(Request $request)
    {
        $query = \App\Models\Pet::with('client');
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $clientId = $request->input('client_id');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все питомцы'];
        }
        
        // Если есть выбранный питомец, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedPet = \App\Models\Pet::with('client')->find($selectedId);
            if ($selectedPet) {
                $options[] = [
                    'value' => $selectedPet->id,
                    'text' => $selectedPet->name . ' (' . ($selectedPet->client->name ?? 'Без владельца') . ')'
                ];
                // Исключаем выбранного питомца из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }
        
        // Фильтр по клиенту (если указан)
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        
        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhereHas('client', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%");
                  });
            });
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }
        
        $pets = $query->limit(20)->get();
        
        // Добавляем остальных питомцев
        foreach ($pets as $pet) {
            $options[] = [
                'value' => $pet->id,
                'text' => $pet->name . ' (' . ($pet->client->name ?? 'Без владельца') . ')'
            ];
        }
        
        return response()->json($options);
    }

    public function scheduleOptions(Request $request)
    {
        $query = \App\Models\Schedule::with('veterinarian');
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $options = [];
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\Schedule::with('veterinarian')->find($selectedId);
            if ($selected) {
                $text = ($selected->veterinarian ? $selected->veterinarian->name . ' - ' : '') .
                    $selected->shift_starts_at->format('d.m.Y H:i') . ' - ' . $selected->shift_ends_at->format('H:i');
                $options[] = [
                    'value' => $selected->id,
                    'text' => $text
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        if ($search) {
            $query->whereHas('veterinarian', function($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }
        $schedules = $query->orderBy('shift_starts_at')->limit(19)->get();
        foreach ($schedules as $schedule) {
            $text = ($schedule->veterinarian ? $schedule->veterinarian->name . ' - ' : '') .
                $schedule->shift_starts_at->format('d.m.Y H:i') . ' - ' . $schedule->shift_ends_at->format('H:i');
            $options[] = [
                'value' => $schedule->id,
                'text' => $text
            ];
        }
        return response()->json($options);
    }

    public function statusOptions(Request $request)
    {
        $query = \App\Models\Status::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все'];
        }
        
        // Если есть выбранный статус, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\Status::find($selectedId);
            if ($selected) {
                $options[] = [
                    'value' => $selected->id,
                    'text' => $selected->name
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }
        
        $statuses = $query->limit(20)->get();
        
        // Добавляем остальные статусы
        foreach ($statuses as $status) {
            $options[] = [
                'value' => $status->id,
                'text' => $status->name
            ];
        }
        
        return response()->json($options);
    }

    public function symptomOptions(Request $request)
    {
        $query = \App\Models\DictionarySymptom::query();
        $search = $request->input('q');
        $selectedIds = $request->input('selected');
        $options = [];
        
        // Обрабатываем выбранные значения
        if ($selectedIds) {
            $selectedArray = is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds);
            foreach ($selectedArray as $selectedId) {
                if (is_numeric($selectedId)) {
                    $selected = \App\Models\DictionarySymptom::find($selectedId);
                    if ($selected) {
                        $options[] = [
                            'value' => $selected->id,
                            'text' => $selected->name
                        ];
                        $query->where('id', '!=', $selectedId);
                    }
                } else {
                    // Кастомный симптом
                    $options[] = [
                        'value' => $selectedId,
                        'text' => $selectedId
                    ];
                }
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        
        $symptoms = $query->orderBy('name')->limit(15)->get();
        foreach ($symptoms as $symptom) {
            $options[] = [
                'value' => $symptom->id,
                'text' => $symptom->name
            ];
        }
        
        // Если есть поиск и не найдено точного совпадения, добавляем возможность создать кастомный
        if ($search && !$symptoms->where('name', $search)->count() && !empty(trim($search))) {
            $options[] = [
                'value' => $search,
                'text' => "Добавить: {$search}"
            ];
        }
        
        return response()->json($options);
    }

    public function diagnosisOptions(Request $request)
    {
        $query = \App\Models\DictionaryDiagnosis::query();
        $search = $request->input('q');
        $selectedIds = $request->input('selected');
        $options = [];
        
        // Обрабатываем выбранные значения
        if ($selectedIds) {
            $selectedArray = is_array($selectedIds) ? $selectedIds : explode(',', $selectedIds);
            foreach ($selectedArray as $selectedId) {
                if (is_numeric($selectedId)) {
                    $selected = \App\Models\DictionaryDiagnosis::find($selectedId);
                    if ($selected) {
                        $options[] = [
                            'value' => $selected->id,
                            'text' => $selected->name
                        ];
                        $query->where('id', '!=', $selectedId);
                    }
                } else {
                    // Кастомный диагноз
                    $options[] = [
                        'value' => $selectedId,
                        'text' => $selectedId
                    ];
                }
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        
        $diagnoses = $query->orderBy('name')->limit(15)->get();
        foreach ($diagnoses as $diagnosis) {
            $options[] = [
                'value' => $diagnosis->id,
                'text' => $diagnosis->name
            ];
        }
        
        // Если есть поиск и не найдено точного совпадения, добавляем возможность создать кастомный
        if ($search && !$diagnoses->where('name', $search)->count() && !empty(trim($search))) {
            $options[] = [
                'value' => $search,
                'text' => "Добавить: {$search}"
            ];
        }

        return response()->json($options);
    }

    public function labTestTypeOptions(Request $request)
    {
        $query = \App\Models\LabTestType::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все типы анализов'];
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedType = \App\Models\LabTestType::find($selectedId);
            if ($selectedType) {
                $options[] = [
                    'value' => $selectedType->id,
                    'text' => $selectedType->name
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $types = $query->limit(20)->get();
        
        foreach ($types as $type) {
            $options[] = [
                'value' => $type->id,
                'text' => $type->name
            ];
        }
        
        return response()->json($options);
    }

    public function labTestParamOptions(Request $request)
    {
        $query = \App\Models\LabTestParam::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все параметры'];
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedParam = \App\Models\LabTestParam::find($selectedId);
            if ($selectedParam) {
                $unit = $selectedParam->unit ? $selectedParam->unit->symbol : null;
                $options[] = [
                    'value' => $selectedParam->id,
                    'text' => $selectedParam->name . ($unit ? ' (' . $unit . ')' : '')
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $params = $query->with('unit')->limit(20)->get();
        
        foreach ($params as $param) {
            $unit = $param->unit ? $param->unit->symbol : null;
            $options[] = [
                'value' => $param->id,
                'text' => $param->name . ($unit ? ' (' . $unit . ')' : '')
            ];
        }
        
        return response()->json($options);
    }

    /**
     * Получить опции для селекта заказов
     */
    public function orderOptions(Request $request)
    {
        $query = \App\Models\Order::with(['client', 'pet']);
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все заказы'];
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedOrder = \App\Models\Order::with(['client', 'pet'])->find($selectedId);
            if ($selectedOrder) {
                $options[] = [
                    'value' => $selectedOrder->id,
                    'text' => "Заказ #{$selectedOrder->id} - {$selectedOrder->client->name} ({$selectedOrder->pet->name})"
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%");
                })
                ->orWhereHas('pet', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%");
                })
                ->orWhere('id', 'like', "%$search%");
            });
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $orders = $query->limit(20)->get();
        
        foreach ($orders as $order) {
            $options[] = [
                'value' => $order->id,
                'text' => "Заказ #{$order->id} - {$order->client->name} ({$order->pet->name})"
            ];
        }
        
        return response()->json($options);
    }

    /**
     * Получить опции для селекта менеджеров
     */
    public function managerOptions(Request $request)
    {
        $query = \App\Models\Employee::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все менеджеры'];
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedManager = \App\Models\Employee::find($selectedId);
            if ($selectedManager) {
                $options[] = [
                    'value' => $selectedManager->id,
                    'text' => $selectedManager->name
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $managers = $query->limit(20)->get();
        
        foreach ($managers as $manager) {
            $options[] = [
                'value' => $manager->id,
                'text' => $manager->name
            ];
        }
        
        return response()->json($options);
    }



    /**
     * Получить опции для селекта анализов в заказах
     */
    public function labTestOptions(Request $request)
    {
        $query = \App\Models\LabTest::with(['pet', 'veterinarian', 'labTestType']);
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        $petId = $request->input('pet_id');
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все анализы'];
        }
        
        // Фильтруем по питомцу если указан
        if ($petId) {
            $query->where('pet_id', $petId);
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedLabTest = \App\Models\LabTest::with(['pet', 'veterinarian', 'labTestType'])->find($selectedId);
            if ($selectedLabTest) {
                $date = $selectedLabTest->received_at ? \Carbon\Carbon::parse($selectedLabTest->received_at)->format('d.m.Y') : 'без даты';
                $options[] = [
                    'value' => $selectedLabTest->id,
                    'text' => "Анализ от {$date} - {$selectedLabTest->pet->name}",
                    'price' => $selectedLabTest->labTestType->price ?? 0
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%");
                })
                ->orWhere('id', 'like', "%$search%");
            });
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $labTests = $query->limit(20)->get();
        
        foreach ($labTests as $labTest) {
            $date = $labTest->received_at ? \Carbon\Carbon::parse($labTest->received_at)->format('d.m.Y') : 'без даты';
            $options[] = [
                'value' => $labTest->id,
                'text' => "Анализ от {$date} - {$labTest->pet->name}",
                'price' => $labTest->labTestType->price ?? 0
            ];
        }
        
        return response()->json($options);
    }

    /**
     * Получить опции для селекта вакцинаций в заказах
     */
    public function vaccinationOptions(Request $request)
    {
        $query = \App\Models\Vaccination::with(['pet', 'veterinarian']);
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $isFilter = $request->input('filter', false);
        $petId = $request->input('pet_id');
        
        $options = [];
        
        // Добавляем "Все" только для фильтров
        if ($isFilter) {
            $options[] = ['value' => '', 'text' => 'Все вакцинации'];
        }
        
        // Фильтруем по питомцу если указан
        if ($petId) {
            $query->where('pet_id', $petId);
        }
        
        if ($selectedId && is_numeric($selectedId)) {
            $selectedVaccination = \App\Models\Vaccination::with(['pet', 'veterinarian'])->find($selectedId);
            if ($selectedVaccination) {
                $date = $selectedVaccination->administered_at ? \Carbon\Carbon::parse($selectedVaccination->administered_at)->format('d.m.Y') : 'без даты';
                $options[] = [
                    'value' => $selectedVaccination->id,
                    'text' => "Вакцинация от {$date} - {$selectedVaccination->pet->name}",
                    'price' => 0
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pet', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%");
                })
                ->orWhere('id', 'like', "%$search%");
            });
        } else {
            // Если нет поиска, загружаем только последние 20 записей
            $query->orderBy('id', 'desc');
        }

        $vaccinations = $query->limit(20)->get();
        
        foreach ($vaccinations as $vaccination) {
            $date = $vaccination->administered_at ? \Carbon\Carbon::parse($vaccination->administered_at)->format('d.m.Y') : 'без даты';
            $options[] = [
                'value' => $vaccination->id,
                'text' => "Вакцинация от {$date} - {$vaccination->pet->name}",
                'price' => 0
            ];
        }

        return response()->json($options);
    }
} 