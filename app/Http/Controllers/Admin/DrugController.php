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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Фильтруем только непустые параметры, но сохраняем '0' как валидное значение
        $queryParams = array_filter($request->all(), function($value, $key) {
            // Сохраняем '0' для prescription_required, но удаляем пустые строки
            if ($key === 'prescription_required') {
                return $value !== '' && $value !== null;
            }
            return $value !== '' && $value !== null;
        }, ARRAY_FILTER_USE_BOTH);
        
        $filter = app()->make(DrugFilter::class, ['queryParams' => $queryParams]);
        
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
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $validated['prescription_required'] = $request->has('prescription_required');

            $drug = $this->model::create($validated);
            
            DB::commit();
            
            Log::info('Препарат успешно создан', [
                'drug_id' => $drug->id,
                'drug_name' => $drug->name,
                'unit_id' => $drug->unit_id,
                'prescription_required' => $drug->prescription_required
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Препарат успешно создан');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании препарата', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании препарата: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $validated['prescription_required'] = $request->has('prescription_required');

            $item = $this->model::findOrFail($id);
            $oldName = $item->name;
            $oldPrescriptionRequired = $item->prescription_required;
            
            $item->update($validated);
            
            DB::commit();
            
            Log::info('Препарат успешно обновлен', [
                'drug_id' => $item->id,
                'old_name' => $oldName,
                'new_name' => $item->name,
                'old_prescription_required' => $oldPrescriptionRequired,
                'new_prescription_required' => $item->prescription_required
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Препарат успешно обновлен');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении препарата', [
                'drug_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении препарата: ' . $e->getMessage()]);
        }
    }

    // Метод supplierOptions теперь наследуется из трейта HasOptionsMethods
}
