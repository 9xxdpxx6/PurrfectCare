<?php

namespace App\Http\Controllers\Admin;

use App\Models\Drug;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DrugController extends AdminController
{
    public function __construct()
    {
        $this->model = Drug::class;
        $this->viewPath = 'drugs';
        $this->routePrefix = 'drugs';
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manufacturer' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'expiry_date' => 'required|date|after:today',
            'storage_conditions' => 'nullable|string|max:255',
            'prescription_required' => 'required|boolean'
        ];
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view("admin.{$this->viewPath}.create", compact('suppliers'));
    }

    public function edit($id)
    {
        $item = $this->model::findOrFail($id);
        $suppliers = Supplier::all();
        return view("admin.{$this->viewPath}.edit", compact('item', 'suppliers'));
    }

    public function index()
    {
        $items = $this->model::with('supplier')->paginate(10);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules);
        $validated['prescription_required'] = $request->has('prescription_required');
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Лекарство успешно обновлено');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);
        $validated['prescription_required'] = $request->has('prescription_required');
        
        $this->model::create($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Лекарство успешно создано');
    }
} 