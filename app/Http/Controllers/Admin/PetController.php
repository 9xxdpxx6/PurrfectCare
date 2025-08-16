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
        $clients = User::all();
        $breeds = Breed::all();
        
        // Получаем ID клиента из параметра запроса
        $selectedClientId = request('owner');
        
        return view("admin.{$this->viewPath}.create", compact('clients', 'breeds', 'selectedClientId'));
    }

    public function edit($id) : View
    {
        $item = $this->model::findOrFail($id);
        $clients = User::all();
        $breeds = Breed::all();
        return view("admin.{$this->viewPath}.edit", compact('item', 'clients', 'breeds'));
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
                'owner_id' => $pet->owner_id,
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
            $oldOwnerId = $item->owner_id;
            $oldBreedId = $item->breed_id;
            
            $item->update($validated);
            
            DB::commit();
            
            Log::info('Данные питомца успешно обновлены', [
                'pet_id' => $item->id,
                'old_name' => $oldName,
                'new_name' => $item->name,
                'old_owner_id' => $oldOwnerId,
                'new_owner_id' => $item->owner_id,
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
        
        $query = $this->model::with(['owner', 'breed.species']);
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        $clients = User::orderBy('name')->get();
        $breeds = Breed::with('species')->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'clients', 'breeds'));
    }

    public function show($id) : View
    {
        $item = $this->model::with(['owner', 'breed.species'])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function destroy($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $item = $this->model::findOrFail($id);
            
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