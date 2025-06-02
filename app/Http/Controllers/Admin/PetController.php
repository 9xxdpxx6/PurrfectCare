<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pet;
use App\Models\User;
use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Filters\PetFilter;

class PetController extends AdminController
{
    public function __construct()
    {
        $this->model = Pet::class;
        $this->viewPath = 'pets';
        $this->routePrefix = 'pets';
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:users,id',
            'breed_id' => 'required|exists:breeds,id',
            'birthdate' => 'nullable|date',
            'gender' => 'required|in:male,female,unknown',
            'weight' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric|min:0',
        ];
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

    public function index(Request $request) : View
    {
        $filter = app(PetFilter::class, ['queryParams' => $request->query()]);
        $query = Pet::query()->with(['breed.species', 'client'])->filter($filter);
        $items = $query->paginate(10)->withQueryString();
        $owners = User::orderBy('name')->get();
        return view("admin.{$this->viewPath}.index", compact('items', 'owners'));
    }
} 