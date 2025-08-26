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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at',
                'created_at', 'updated_at'
            ])
            ->with([
                'pet:id,name,client_id',
                'pet.client:id,name,email',
                'veterinarian:id,name,email',
                'labTestType:id,name',
                'results:id,lab_test_id,lab_test_param_id,value,notes',
                'results.labTestParam:id,name,unit_id,description'
            ])
            ->filter($filter);
            
        $items = $query->paginate(25)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        // Оптимизация: используем select для выбора только нужных полей
        $labTestTypes = LabTestType::select(['id', 'name', 'description'])
            ->with(['params:id,name,unit_id,description'])
            ->get();
        
        // Получаем ID питомца из параметра запроса
        $selectedPetId = request('pet');
        
        return view("admin.{$this->viewPath}.create", compact('labTestTypes', 'selectedPetId'));
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
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at',
                'created_at', 'updated_at'
            ])
            ->with([
                'pet:id,name,client_id,birthdate,gender',
                'pet.client:id,name,email,phone',
                'veterinarian:id,name,email,phone',
                'labTestType:id,name,description',
                'results:id,lab_test_id,lab_test_param_id,value,notes,created_at',
                'results.labTestParam:id,name,unit_id,description'
            ])
            ->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at',
                'created_at', 'updated_at'
            ])
            ->with([
                'pet:id,name,client_id',
                'pet.client:id,name,email',
                'veterinarian:id,name,email',
                'labTestType:id,name,description',
                'results:id,lab_test_id,lab_test_param_id,value,notes',
                'results.labTestParam:id,name,unit_id,description'
            ])
            ->findOrFail($id);
        
        // Оптимизация: используем select для выбора только нужных полей
        $labTestTypes = LabTestType::select(['id', 'name', 'description'])
            ->with(['params:id,name,unit_id,description'])
            ->get();
        
        return view("admin.{$this->viewPath}.edit", compact('item', 'labTestTypes'));
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Оптимизация: используем select для выбора только нужных полей
            $labTest = $this->model::select([
                    'id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at'
                ])
                ->findOrFail($id);
                
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
            
            DB::commit();
            
            Log::info('Анализ успешно обновлен', [
                'lab_test_id' => $labTest->id,
                'pet_id' => $labTest->pet_id,
                'veterinarian_id' => $labTest->veterinarian_id,
                'results_count' => isset($validated['results']) ? count($validated['results']) : 0
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Анализ успешно обновлен');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении анализа', [
                'lab_test_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении анализа: ' . $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Оптимизация: используем select для выбора только нужных полей
            $labTest = $this->model::select([
                    'id', 'lab_test_type_id', 'pet_id'
                ])
                ->with([
                    'labTestType:id,name',
                    'pet:id,name'
                ])
                ->findOrFail($id);
            
            // Убираем проверку зависимостей - результаты удаляются каскадно
            $labTestName = $labTest->labTestType->name ?? 'Неизвестный анализ';
            $petName = $labTest->pet->name ?? 'Неизвестный питомец';
            
            // Удаляем сам анализ (результаты удалятся каскадно)
            $labTest->delete();
            
            DB::commit();
            
            Log::info('Анализ успешно удален', [
                'lab_test_id' => $id,
                'lab_test_name' => $labTestName,
                'pet_name' => $petName
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Анализ успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении анализа', [
                'lab_test_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении анализа: ' . $e->getMessage()]);
        }
    }
    
    // TomSelect опции для основных полей
    public function petOptions(Request $request)
    {
        return app(\App\Services\Options\PetOptionsService::class)->getOptions($request);
    }
    
    public function veterinarianOptions(Request $request)
    {
        return app(\App\Services\Options\EmployeeOptionsService::class)->getVeterinarianOptions($request);
    }
    
    public function labTestTypeOptions(Request $request)
    {
        return app(\App\Services\Options\LabTestOptionsService::class)->getLabTestTypeOptions($request);
    }
    
    public function labTestParamOptions(Request $request)
    {
        return app(\App\Services\Options\LabTestOptionsService::class)->getLabTestParamOptions($request);
    }
} 