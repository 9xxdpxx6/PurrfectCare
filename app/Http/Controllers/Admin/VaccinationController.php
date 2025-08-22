<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Filters\VaccinationFilter;
use App\Http\Requests\Admin\Vaccination\StoreRequest;
use App\Http\Requests\Admin\Vaccination\UpdateRequest;
use App\Models\Vaccination;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VaccinationController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = Vaccination::class;
        $this->viewPath = 'vaccinations';
        $this->routePrefix = 'vaccinations';
    }

    public function index(Request $request): View
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
        $queryParams = $request->query();
        if (isset($queryParams['date_from']) && $queryParams['date_from']) {
            try {
                $queryParams['date_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['date_to']) && $queryParams['date_to']) {
            try {
                $queryParams['date_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['next_due_from']) && $queryParams['next_due_from']) {
            try {
                $queryParams['next_due_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['next_due_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['next_due_to']) && $queryParams['next_due_to']) {
            try {
                $queryParams['next_due_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['next_due_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(VaccinationFilter::class, ['queryParams' => $queryParams]);
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'vaccination_type_id', 'pet_id', 'veterinarian_id', 'administered_at', 'next_due',
                'created_at', 'updated_at'
            ])
            ->with([
                'pet:id,name,client_id',
                'pet.client:id,name,email',
                'veterinarian:id,name,email',
                'vaccinationType:id,name,description',
                'vaccinationType.drugs:id,name,price'
            ])
            ->filter($filter);
            
        $items = $query->paginate(25)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        $default_administered_at = now()->format('d.m.Y');
        
        // Получаем ID питомца из параметра запроса
        $selectedPetId = request('pet');
        
        return view("admin.{$this->viewPath}.create", compact('default_administered_at', 'selectedPetId'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $vaccination = $this->model::create([
                'vaccination_type_id' => $validated['vaccination_type_id'],
                'pet_id' => $validated['pet_id'],
                'veterinarian_id' => $validated['veterinarian_id'],
                'administered_at' => $validated['administered_at'],
                'next_due' => $validated['next_due'] ?? null,
            ]);
            
            DB::commit();
            
            Log::info('Вакцинация успешно создана', [
                'vaccination_id' => $vaccination->id,
                'vaccination_type_id' => $vaccination->vaccination_type_id,
                'pet_id' => $vaccination->pet_id,
                'veterinarian_id' => $vaccination->veterinarian_id,
                'administered_at' => $vaccination->administered_at,
                'next_due' => $vaccination->next_due
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Вакцинация успешно создана');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании вакцинации', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании вакцинации: ' . $e->getMessage()]);
        }
    }

    public function show($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'vaccination_type_id', 'pet_id', 'veterinarian_id', 'administered_at', 'next_due',
                'created_at', 'updated_at'
            ])
            ->with([
                'pet:id,name,client_id,birthdate,gender',
                'pet.client:id,name,email,phone',
                'pet.breed:id,name',
                'veterinarian:id,name,email,phone',
                'vaccinationType:id,name,description',
                'vaccinationType.drugs:id,name,price'
            ])
            ->findOrFail($id);
        
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function edit($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'vaccination_type_id', 'pet_id', 'veterinarian_id', 'administered_at', 'next_due',
                'created_at', 'updated_at'
            ])
            ->with(['vaccinationType:id,name,description'])
            ->findOrFail($id);
        
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Оптимизация: используем select для выбора только нужных полей
            $vaccination = $this->model::select([
                    'id', 'vaccination_type_id', 'pet_id', 'veterinarian_id', 'administered_at', 'next_due'
                ])
                ->findOrFail($id);
            
            $oldVaccinationTypeId = $vaccination->vaccination_type_id;
            $oldPetId = $vaccination->pet_id;
            $oldVeterinarianId = $vaccination->veterinarian_id;
            $oldAdministeredAt = $vaccination->administered_at;
            $oldNextDue = $vaccination->next_due;
            
            $vaccination->update([
                'vaccination_type_id' => $validated['vaccination_type_id'],
                'pet_id' => $validated['pet_id'],
                'veterinarian_id' => $validated['veterinarian_id'],
                'administered_at' => $validated['administered_at'],
                'next_due' => $validated['next_due'] ?? null,
            ]);
            
            DB::commit();
            
            Log::info('Вакцинация успешно обновлена', [
                'vaccination_id' => $vaccination->id,
                'old_vaccination_type_id' => $oldVaccinationTypeId,
                'new_vaccination_type_id' => $vaccination->vaccination_type_id,
                'old_pet_id' => $oldPetId,
                'new_pet_id' => $vaccination->pet_id,
                'old_veterinarian_id' => $oldVeterinarianId,
                'new_veterinarian_id' => $vaccination->veterinarian_id,
                'old_administered_at' => $oldAdministeredAt,
                'new_administered_at' => $vaccination->administered_at,
                'old_next_due' => $oldNextDue,
                'new_next_due' => $vaccination->next_due
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Вакцинация успешно обновлена');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении вакцинации', [
                'vaccination_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении вакцинации: ' . $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Оптимизация: используем select для выбора только нужных полей
            $vaccination = $this->model::select([
                    'id', 'vaccination_type_id', 'pet_id', 'veterinarian_id'
                ])
                ->findOrFail($id);
            
            // Убираем проверку зависимостей - вакцинация не имеет зависимостей для проверки
            $vaccinationTypeId = $vaccination->vaccination_type_id;
            $petId = $vaccination->pet_id;
            $veterinarianId = $vaccination->veterinarian_id;
            
            $vaccination->delete();
            
            DB::commit();
            
            Log::info('Вакцинация успешно удалена', [
                'vaccination_id' => $id,
                'vaccination_type_id' => $vaccinationTypeId,
                'pet_id' => $petId,
                'veterinarian_id' => $veterinarianId
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Вакцинация успешно удалена');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении вакцинации', [
                'vaccination_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении вакцинации: ' . $e->getMessage()]);
        }
    }

    public function vaccinationOptions(Request $request)
    {
        return app(\App\Services\Options\VaccinationOptionsService::class)->getOptions($request);
    }
    
    public function vaccinationTypeOptions(Request $request)
    {
        return app(\App\Services\Options\VaccinationTypeOptionsService::class)->getOptions($request);
    }
    
    public function getDrugs($id)
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $vaccination = Vaccination::select(['id', 'vaccination_type_id'])
            ->with([
                'vaccinationType:id,name',
                'vaccinationType.drugs:id,name,price'
            ])
            ->findOrFail($id);
        
        $drugs = $vaccination->vaccinationType && $vaccination->vaccinationType->drugs 
            ? $vaccination->vaccinationType->drugs->map(function($drug) {
                return [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'dosage' => $drug->pivot->dosage ?? null,
                    'price' => $drug->price ?? 0
                ];
            })
            : collect();
        
        return response()->json($drugs);
    }
} 