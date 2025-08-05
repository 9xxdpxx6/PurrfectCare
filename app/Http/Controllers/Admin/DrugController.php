<?php

namespace App\Http\Controllers\Admin;

use App\Models\Drug;
use App\Models\Unit;
use App\Models\Supplier;
use App\Http\Requests\Admin\Drug\StoreRequest;
use App\Http\Requests\Admin\Drug\UpdateRequest;
use App\Http\Filters\DrugFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DrugController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = Drug::class;
        $this->viewPath = 'drugs';
        $this->routePrefix = 'drugs';
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

    public function show($id): View
    {
        $item = $this->model::with([
            'unit',
            'procurements.supplier',
            'procurements' => function($query) {
                $query->orderBy('delivery_date', 'desc')->limit(10);
            }
        ])->findOrFail($id);
        
        // Получаем общее количество закупок для отображения в заголовке
        $procurementsTotal = $item->procurements()->count();
        
        // Получаем дату первой поставки
        $firstProcurement = $item->procurements()
            ->orderBy('delivery_date', 'asc')
            ->first();
        
        return view("admin.{$this->viewPath}.show", compact('item', 'procurementsTotal', 'firstProcurement'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['prescription_required'] = $request->has('prescription_required');

        $this->model::create($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Препарат успешно создан');
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        $validated['prescription_required'] = $request->has('prescription_required');

        $item = $this->model::findOrFail($id);
        $item->update($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Препарат успешно обновлен');
    }

    // Метод supplierOptions теперь наследуется из трейта HasOptionsMethods
}
