<?php

namespace App\Http\Controllers\Admin;

use App\Models\DrugProcurement;
use App\Models\Drug;
use App\Models\Supplier;
use App\Http\Filters\DrugProcurementFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DrugProcurementController extends AdminController
{
    public function __construct()
    {
        $this->model = DrugProcurement::class;
        $this->viewPath = 'drug-procurements';
        $this->routePrefix = 'drug-procurements';
        $this->validationRules = [
            'supplier_id' => 'required|exists:suppliers,id',
            'drug_id' => 'required|exists:drugs,id',
            'delivery_date' => 'required|date',
            'expiry_date' => 'required|date|after:delivery_date',
            'manufacture_date' => 'required|date|before_or_equal:delivery_date',
            'packaging_date' => 'required|date|after_or_equal:manufacture_date|before_or_equal:delivery_date',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function index(Request $request): View
    {
        $filter = app()->make(DrugProcurementFilter::class, ['queryParams' => array_filter($request->all())]);

        $query = $this->model::with(['drug', 'supplier']);
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        $drugs = Drug::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'drugs', 'suppliers'));
    }

    public function create(): View
    {
        $drugs = Drug::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view("admin.{$this->viewPath}.create", compact('drugs', 'suppliers'));
    }

    public function edit($id): View
    {
        $item = $this->model::findOrFail($id);
        $drugs = Drug::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view("admin.{$this->viewPath}.edit", compact('item', 'drugs', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules);

        $this->model::create($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставка успешно создана');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate($this->validationRules);

        $item = $this->model::findOrFail($id);
        $item->update($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставка успешно обновлена');
    }
} 