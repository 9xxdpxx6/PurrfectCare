<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Filters\LabTestFilter;
use App\Http\Requests\Admin\LabTest\StoreRequest;
use App\Http\Requests\Admin\LabTest\UpdateRequest;
use App\Models\LabTest;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\LabTestType;
use App\Models\LabTestParam;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Traits\HasOptionsMethods;

class LabTestController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = LabTest::class;
        $this->viewPath = 'lab-tests';
        $this->routePrefix = 'lab-tests';
    }

    public function index(Request $request): View
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
        $queryParams = $request->query();
        if (isset($queryParams['received_at_from']) && $queryParams['received_at_from']) {
            try {
                $queryParams['received_at_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['received_at_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['received_at_to']) && $queryParams['received_at_to']) {
            try {
                $queryParams['received_at_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['received_at_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['completed_at_from']) && $queryParams['completed_at_from']) {
            try {
                $queryParams['completed_at_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['completed_at_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['completed_at_to']) && $queryParams['completed_at_to']) {
            try {
                $queryParams['completed_at_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['completed_at_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(LabTestFilter::class, ['queryParams' => $queryParams]);
        $query = $this->model::with([
            'pet.client', 'veterinarian', 'labTestType', 'results.labTestParam'
        ])->filter($filter);
        $items = $query->paginate(25)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        $labTestTypes = LabTestType::with('params')->get();
        
        return view("admin.{$this->viewPath}.create", compact('labTestTypes'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $labTest = $this->model::create([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'lab_test_type_id' => $validated['lab_test_type_id'],
            'received_at' => $validated['received_at'],
            'completed_at' => $validated['completed_at'] ?? null,
        ]);

        // Создаем результаты анализов
        if (isset($validated['results'])) {
            foreach ($validated['results'] as $resultData) {
                $labTest->results()->create([
                    'lab_test_param_id' => $resultData['lab_test_param_id'],
                    'value' => $resultData['value'],
                    'notes' => $resultData['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Анализ успешно создан');
    }

    public function show($id): View
    {
        $item = $this->model::with([
            'pet.client', 'veterinarian', 'labTestType', 'results.labTestParam'
        ])->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id): View
    {
        $item = $this->model::with([
            'pet.client', 'veterinarian', 'labTestType', 'results.labTestParam'
        ])->findOrFail($id);
        
        $labTestTypes = LabTestType::with('params')->get();
        
        return view("admin.{$this->viewPath}.edit", compact('item', 'labTestTypes'));
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $labTest = $this->model::findOrFail($id);
        $validated = $request->validated();
        
        $labTest->update([
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $validated['veterinarian_id'],
            'lab_test_type_id' => $validated['lab_test_type_id'],
            'received_at' => $validated['received_at'],
            'completed_at' => $validated['completed_at'] ?? null,
        ]);

        // Обновляем результаты анализов
        if (isset($validated['results'])) {
            // Удаляем старые результаты
            $labTest->results()->delete();
            
            // Создаем новые результаты
            foreach ($validated['results'] as $resultData) {
                $labTest->results()->create([
                    'lab_test_param_id' => $resultData['lab_test_param_id'],
                    'value' => $resultData['value'],
                    'notes' => $resultData['notes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Анализ успешно обновлен');
    }

    public function destroy($id): RedirectResponse
    {
        $labTest = $this->model::findOrFail($id);
        
        // Проверяем наличие зависимых записей
        if ($errorMessage = $labTest->hasDependencies()) {
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('error', $errorMessage);
        }
        
        // Удаляем результаты анализов
        $labTest->results()->delete();
        
        // Удаляем сам анализ
        $labTest->delete();

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Анализ успешно удален');
    }
} 