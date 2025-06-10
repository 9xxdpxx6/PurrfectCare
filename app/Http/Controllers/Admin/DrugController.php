<?php

namespace App\Http\Controllers\Admin;

use App\Models\Drug;
use App\Models\Unit;
use App\Models\Supplier;
use App\Http\Filters\DrugFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DrugController extends AdminController
{
    public function __construct()
    {
        $this->model = Drug::class;
        $this->viewPath = 'drugs';
        $this->routePrefix = 'drugs';
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'unit_id' => 'nullable|exists:units,id',
            'prescription_required' => 'boolean'
        ];
    }

    public function index(Request $request): View
    {
        $filter = app()->make(DrugFilter::class, ['queryParams' => array_filter($request->all())]);
        
        $query = $this->model::with(['unit', 'procurements.supplier']);
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        
        // Подготавливаем данные для каждого препарата
        foreach ($items as $drug) {
            // Получаем уникальных поставщиков для препарата
            $uniqueSuppliers = $drug->procurements
                ->pluck('supplier')
                ->filter()
                ->unique('id')
                ->take(2);
            
            $supplierCount = $drug->procurements
                ->pluck('supplier')
                ->filter()
                ->unique('id')
                ->count();
            
            // Формируем строку с поставщиками
            $supplierNames = $uniqueSuppliers->pluck('name')->toArray();
            if ($supplierCount > 2) {
                $supplierNames[] = '...';
            }
            
            $drug->suppliers_display = $supplierNames;
            
            // Получаем последнюю поставку для даты информации
            $drug->latest_procurement = $drug->procurements
                ->sortByDesc('delivery_date')
                ->first();
        }
        
        $units = Unit::all();
        $suppliers = Supplier::all();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'units', 'suppliers'));
    }

    public function create(): View
    {
        $units = Unit::all();
        return view("admin.{$this->viewPath}.create", compact('units'));
    }

    public function edit($id): View
    {
        $item = $this->model::findOrFail($id);
        $units = Unit::all();
        return view("admin.{$this->viewPath}.edit", compact('item', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        $validated['prescription_required'] = $request->has('prescription_required');

        $this->model::create($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Препарат успешно создан');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        $validated['prescription_required'] = $request->has('prescription_required');

        $item = $this->model::findOrFail($id);
        $item->update($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Препарат успешно обновлен');
    }
}
