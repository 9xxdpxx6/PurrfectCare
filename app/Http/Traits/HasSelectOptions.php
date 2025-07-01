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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

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
        }

        $suppliers = $query->orderBy('name')->limit(19)->get();

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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

        // Если есть выбранный препарат, добавляем его первым (если он не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedDrug = Drug::find($selectedId);
            if ($selectedDrug) {
                $options[] = [
                    'value' => $selectedDrug->id,
                    'text' => $selectedDrug->name
                ];
                // Исключаем выбранный препарат из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $drugs = $query->orderBy('name')->limit(19)->get();

        // Добавляем остальные препараты
        foreach ($drugs as $drug) {
            $options[] = [
                'value' => $drug->id,
                'text' => $drug->name
            ];
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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

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
        }

        $specialties = $query->orderBy('name')->limit(19)->get();

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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

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
        }

        $veterinarians = $query->orderBy('name')->limit(19)->get();

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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

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
        }

        $branches = $query->orderBy('name')->limit(19)->get();

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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

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
        }

        $owners = $query->orderBy('name')->limit(19)->get();

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

        // Всегда добавляем "Все" первым элементом
        $options = [
            ['value' => '', 'text' => 'Все']
        ];

        // Если есть выбранная услуга, добавляем её первой (если она не "Все")
        if ($selectedId && is_numeric($selectedId)) {
            $selectedService = \App\Models\Service::find($selectedId);
            if ($selectedService) {
                $options[] = [
                    'value' => $selectedService->id,
                    'text' => $selectedService->name
                ];
                // Исключаем выбранную услугу из основного запроса
                $query->where('id', '!=', $selectedId);
            }
        }

        // Основной запрос для поиска/загрузки
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $services = $query->orderBy('name')->limit(19)->get();

        // Добавляем остальные услуги
        foreach ($services as $service) {
            $options[] = [
                'value' => $service->id,
                'text' => $service->name
            ];
        }

        return response()->json($options);
    }

    public function clientOptions(Request $request)
    {
        $query = \App\Models\User::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $options = [];
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
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $users = $query->orderBy('name')->limit(19)->get();
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
        $query = \App\Models\Pet::query();
        $search = $request->input('q');
        $selectedId = $request->input('selected');
        $clientId = $request->input('client_id');
        $options = [];
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\Pet::find($selectedId);
            if ($selected) {
                $options[] = [
                    'value' => $selected->id,
                    'text' => $selected->name
                ];
                $query->where('id', '!=', $selectedId);
            }
        }
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $pets = $query->orderBy('name')->limit(19)->get();
        foreach ($pets as $pet) {
            $options[] = [
                'value' => $pet->id,
                'text' => $pet->name
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
        $options = [];
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
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }
        $statuses = $query->orderBy('name')->limit(19)->get();
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
        $selectedId = $request->input('selected');
        $options = [];
        
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\DictionarySymptom::find($selectedId);
            if ($selected) {
                $options[] = [
                    'value' => $selected->id,
                    'text' => $selected->name
                ];
                $query->where('id', '!=', $selectedId);
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
        if ($search && !$symptoms->where('name', $search)->count()) {
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
        $selectedId = $request->input('selected');
        $options = [];
        
        if ($selectedId && is_numeric($selectedId)) {
            $selected = \App\Models\DictionaryDiagnosis::find($selectedId);
            if ($selected) {
                $options[] = [
                    'value' => $selected->id,
                    'text' => $selected->name
                ];
                $query->where('id', '!=', $selectedId);
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
        if ($search && !$diagnoses->where('name', $search)->count()) {
            $options[] = [
                'value' => $search,
                'text' => "Добавить: {$search}"
            ];
        }
        
        return response()->json($options);
    }
} 