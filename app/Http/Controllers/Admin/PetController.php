<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pet;
use App\Models\User;
use App\Models\Breed;
use App\Http\Requests\Admin\Pet\StoreRequest;
use App\Http\Requests\Admin\Pet\UpdateRequest;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Filters\PetFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PetController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = Pet::class;
        $this->viewPath = 'pets';
        $this->routePrefix = 'pets';
    }

    public function create() : View
    {
        // Получаем ID клиента из параметра запроса
        $selectedClientId = request('owner');
        
        // Загружаем список клиентов для отображения информации о выбранном клиенте
        $clients = User::select(['id', 'name', 'email'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.create", compact('selectedClientId', 'clients'));
    }

    public function edit($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at', 'updated_at'
            ])
            ->findOrFail($id);
            
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $pet = $this->model::create($validated);
            
            DB::commit();
            
            Log::info('Питомец успешно создан', [
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'client_id' => $pet->client_id,
                'breed_id' => $pet->breed_id
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Питомец успешно создан');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании питомца', [
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании питомца: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $item = $this->model::findOrFail($id);
            $oldName = $item->name;
            $oldClientId = $item->client_id;
            $oldBreedId = $item->breed_id;
            
            $item->update($validated);
            
            DB::commit();
            
            Log::info('Данные питомца успешно обновлены', [
                'pet_id' => $item->id,
                'old_name' => $oldName,
                'new_name' => $item->name,
                'old_client_id' => $oldClientId,
                'new_client_id' => $item->client_id,
                'old_breed_id' => $oldBreedId,
                'new_breed_id' => $item->breed_id
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Данные питомца успешно обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении питомца', [
                'pet_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении питомца: ' . $e->getMessage()]);
        }
    }

    public function index(Request $request) : View
    {
        $filter = app()->make(PetFilter::class, ['queryParams' => array_filter($request->all())]);
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at'
            ])
            ->with([
                'client:id,name,email,phone',
                'breed:id,name',
                'visits:id,pet_id,starts_at,status_id',
                'orders:id,pet_id,total,is_paid,closed_at',
                'vaccinations:id,pet_id,administered_at,next_due',
                'labTests:id,pet_id,received_at,completed_at'
            ]);
            
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        
        // Подсчитаем статистику для каждого питомца
        foreach ($items as $pet) {
            $pet->visits_count = $pet->visits->count();
            $pet->orders_count = $pet->orders->count();
            $pet->vaccinations_count = $pet->vaccinations->count();
            $pet->lab_tests_count = $pet->labTests->count();
        }
        
        // Оптимизация: используем select для выбора только нужных полей
        $clients = User::select(['id', 'name', 'email'])->orderBy('name')->get();
        $breeds = Breed::select(['id', 'name'])
            ->orderBy('name')
            ->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'clients', 'breeds'));
    }

    public function show($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $pet = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone,address',
                'breed:id,name,species_id',
                'breed.species:id,name'
            ])
            ->findOrFail($id);
        
        // Загружаем связанные данные с оптимизацией
        $visits = $pet->visits()
            ->select(['id', 'pet_id', 'schedule_id', 'starts_at', 'status_id', 'complaints'])
            ->with([
                'schedule:id,veterinarian_id,shift_starts_at',
                'schedule.veterinarian:id,name,email',
                'schedule.veterinarian.specialties:id,name',
                'status:id,name,color'
            ])
            ->latest()
            ->limit(10)
            ->get();
            
        $vaccinations = $pet->vaccinations()
            ->select(['id', 'pet_id', 'veterinarian_id', 'vaccination_type_id', 'administered_at', 'next_due'])
            ->with([
                'veterinarian:id,name,email',
                'veterinarian.specialties:id,name',
                'vaccinationType:id,name'
            ])
            ->latest()
            ->limit(10)
            ->get();
            
        $labTests = $pet->labTests()
            ->select(['id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at'])
            ->with([
                'veterinarian:id,name,email',
                'veterinarian.specialties:id,name',
                'labTestType:id,name']
            )
            ->latest()
            ->limit(10)
            ->get();
            
        $orders = $pet->orders()
            ->select(['id', 'pet_id', 'branch_id', 'status_id', 'total', 'is_paid', 'closed_at', 'created_at'])
            ->with([
                'branch:id,name,address',
                'status:id,name,color'
            ])
            ->latest()
            ->limit(10)
            ->get();
        
        // Подсчитываем общие количества с оптимизацией
        $visitsTotal = $pet->visits()->count();
        $vaccinationsTotal = $pet->vaccinations()->count();
        $labTestsTotal = $pet->labTests()->count();
        $ordersTotal = $pet->orders()->count();
        
        return view("admin.{$this->viewPath}.show", compact(
            'pet', 'visits', 'vaccinations', 'labTests', 'orders',
            'visitsTotal', 'vaccinationsTotal', 'labTestsTotal', 'ordersTotal'
        ));
    }

    public function destroy($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Оптимизация: используем select для выбора только нужных полей
            $item = $this->model::select(['id', 'name'])->findOrFail($id);
            
            // Проверяем наличие зависимых записей
            if ($errorMessage = $item->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $petName = $item->name;
            $item->delete();
            
            DB::commit();
            
            Log::info('Питомец успешно удален', [
                'pet_id' => $id,
                'pet_name' => $petName
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Питомец успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении питомца', [
                'pet_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении питомца: ' . $e->getMessage()]);
        }
    }
} 