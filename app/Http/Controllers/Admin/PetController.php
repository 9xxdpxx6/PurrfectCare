<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pet;
use App\Models\User;
use App\Models\Breed;
use App\Http\Requests\Admin\Pet\StoreRequest;
use App\Http\Requests\Admin\Pet\UpdateRequest;
use App\Http\Traits\HasSelectOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Filters\PetFilter;

class PetController extends AdminController
{
    use HasSelectOptions;

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
        return view("admin.{$this->viewPath}.create", compact('clients', 'breeds'));
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
        $validated = $request->validated();
        
        $pet = $this->model::create($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Питомец успешно создан');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Данные питомца успешно обновлены');
    }

    public function index(Request $request) : View
    {
        $filter = app(PetFilter::class, ['queryParams' => $request->query()]);
        $query = Pet::query()->with(['breed.species', 'client'])->filter($filter);
        $items = $query->paginate(25)->withQueryString();
        $owners = User::orderBy('name')->get();
        return view("admin.{$this->viewPath}.index", compact('items', 'owners'));
    }

    public function show($id) : View
    {
        $pet = $this->model::with([
            'client',
            'breed.species'
        ])->findOrFail($id);
        
        // Получаем общее количество записей
        $visitsTotal = $pet->visits()->count();
        $vaccinationsTotal = $pet->vaccinations()->count();
        $labTestsTotal = $pet->labTests()->count();
        $ordersTotal = $pet->orders()->count();
        
        // Загружаем ограниченные данные для отображения
        $visits = $pet->visits()->with(['schedule.veterinarian'])->latest()->limit(10)->get();
        $vaccinations = $pet->vaccinations()->with(['veterinarian'])->latest()->limit(10)->get();
        $labTests = $pet->labTests()->with(['veterinarian', 'labTestType'])->latest()->limit(10)->get();
        $orders = $pet->orders()->latest()->limit(10)->get();
        
        return view("admin.{$this->viewPath}.show", compact('pet', 'visits', 'vaccinations', 'labTests', 'orders', 'visitsTotal', 'vaccinationsTotal', 'labTestsTotal', 'ordersTotal'));
    }
} 