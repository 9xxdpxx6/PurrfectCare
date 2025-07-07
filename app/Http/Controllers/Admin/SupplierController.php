<?php

namespace App\Http\Controllers\Admin;

use App\Http\Filters\SupplierFilter;
use App\Http\Requests\Admin\Supplier\StoreRequest;
use App\Http\Requests\Admin\Supplier\UpdateRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends AdminController
{
    public function __construct()
    {
        $this->model = Supplier::class;
        $this->viewPath = 'suppliers';
        $this->routePrefix = 'suppliers';
    }

    public function index(Request $request) : View
    {
        $filter = app(SupplierFilter::class, ['queryParams' => $request->query()]);
        $query = Supplier::query()->with('procurements')->filter($filter);
        $items = $query->paginate(25)->withQueryString();

        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function show($id) : View
    {
        $supplier = $this->model::with(['procurements.drug', 'procurements' => function($query) {
            $query->orderBy('delivery_date', 'desc')->limit(10);
        }])->findOrFail($id);
        
        // Получаем общее количество закупок для отображения в заголовке
        $procurementsTotal = $supplier->procurements()->count();
        
        // Вычисляем статистику поставок
        $totalProcurements = $procurementsTotal;
        $totalDrugs = $supplier->procurements->unique('drug_id')->count();
        $totalQuantity = $supplier->procurements->sum('quantity');
        $totalValue = $supplier->procurements->sum(function($procurement) {
            return $procurement->price * $procurement->quantity;
        });
        $lastDelivery = $supplier->procurements->sortByDesc('delivery_date')->first();
        
        return view("admin.{$this->viewPath}.show", compact(
            'supplier', 
            'procurementsTotal', 
            'totalProcurements', 
            'totalDrugs', 
            'totalQuantity', 
            'totalValue', 
            'lastDelivery'
        ));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        $validated = $request->validated();
        
        $this->model::create($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставщик успешно создан');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставщик успешно обновлен');
    }
}
