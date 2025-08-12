<?php

namespace App\Http\Controllers\Admin;

use App\Models\DrugProcurement;
use App\Models\Drug;
use App\Models\Supplier;
use App\Http\Requests\Admin\DrugProcurement\StoreRequest;
use App\Http\Requests\Admin\DrugProcurement\UpdateRequest;
use App\Http\Filters\DrugProcurementFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DrugProcurementController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        $this->model = DrugProcurement::class;
        $this->viewPath = 'drug-procurements';
        $this->routePrefix = 'drug-procurements';
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
        return view("admin.{$this->viewPath}.create");
    }

    public function edit($id): View
    {
        $item = $this->model::with(['drug.unit', 'supplier'])->findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function show($id): View
    {
        $item = $this->model::with(['drug.unit', 'supplier'])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            // Создаем поставку
            $procurement = $this->model::create($validated);
            
            // Увеличиваем количество на складе
            $drug = Drug::find($validated['drug_id']);
            $drug->increment('quantity', $validated['quantity']);
        });

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставка успешно создана');
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $id) {
            // Получаем старую поставку
            $oldProcurement = $this->model::findOrFail($id);
            $oldQuantity = $oldProcurement->quantity;
            $oldDrugId = $oldProcurement->drug_id;
            
            // Обновляем поставку
            $oldProcurement->update($validated);
            
            // Если изменился препарат или количество
            if ($oldDrugId != $validated['drug_id'] || $oldQuantity != $validated['quantity']) {
                // Откатываем старое изменение (уменьшаем количество старого препарата)
                $oldDrug = Drug::find($oldDrugId);
                $oldDrug->decrement('quantity', $oldQuantity);
                
                // Применяем новое изменение (увеличиваем количество нового препарата)
                $newDrug = Drug::find($validated['drug_id']);
                $newDrug->increment('quantity', $validated['quantity']);
            } else {
                // Если препарат не изменился, но изменилось количество
                $quantityDiff = $validated['quantity'] - $oldQuantity;
                if ($quantityDiff != 0) {
                    $drug = Drug::find($validated['drug_id']);
                    $drug->increment('quantity', $quantityDiff);
                }
            }
        });

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставка успешно обновлена');
    }

    public function destroy($id): RedirectResponse
    {
        $procurement = $this->model::findOrFail($id);
        
        // Убираем проверку зависимостей - поставка не имеет зависимостей для проверки
        
        DB::transaction(function () use ($procurement) {
            $quantity = $procurement->quantity;
            $drugId = $procurement->drug_id;
            
            // Удаляем поставку
            $procurement->delete();
            
            // Уменьшаем количество на складе
            $drug = Drug::find($drugId);
            $drug->decrement('quantity', $quantity);
        });

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Поставка успешно удалена');
    }
} 