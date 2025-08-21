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
use Illuminate\Support\Facades\Log;

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

        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'drug_id', 'supplier_id', 'quantity', 'unit_price', 'delivery_date', 'expiry_date',
                'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,description,unit_id',
                'supplier:id,name,contact_person,phone'
            ]);
            
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        
        // Оптимизация: используем select для выбора только нужных полей
        $drugs = Drug::select(['id', 'name', 'description'])->orderBy('name')->get();
        $suppliers = Supplier::select(['id', 'name', 'contact_person', 'phone'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'drugs', 'suppliers'));
    }

    public function create(): View
    {
        // Получаем ID препарата из параметра запроса
        $selectedDrugId = request('drug');
        
        return view("admin.{$this->viewPath}.create", compact('selectedDrugId'));
    }

    public function edit($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'drug_id', 'supplier_id', 'quantity', 'unit_price', 'delivery_date', 'expiry_date',
                'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,description,unit_id',
                'drug.unit:id,name,abbreviation',
                'supplier:id,name,contact_person,phone,email'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function show($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'drug_id', 'supplier_id', 'quantity', 'unit_price', 'delivery_date', 'expiry_date',
                'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,description,unit_id,manufacturer',
                'drug.unit:id,name,abbreviation',
                'supplier:id,name,contact_person,phone,email,address'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated) {
                // Создаем поставку
                $procurement = $this->model::create($validated);
                
                // Увеличиваем количество на складе
                // Оптимизация: используем select для выбора только нужных полей
                $drug = Drug::select(['id', 'quantity'])->find($validated['drug_id']);
                $drug->increment('quantity', $validated['quantity']);
            });

            Log::info('Поставка успешно создана', [
                'drug_id' => $validated['drug_id'],
                'quantity' => $validated['quantity'],
                'supplier_id' => $validated['supplier_id'] ?? null
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Поставка успешно создана');

        } catch (\Exception $e) {
            Log::error('Ошибка при создании поставки', [
                'data' => $validated ?? $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании поставки: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            $validated = $request->validated();

            DB::transaction(function () use ($validated, $id) {
                // Получаем старую поставку
                // Оптимизация: используем select для выбора только нужных полей
                $oldProcurement = $this->model::select(['id', 'drug_id', 'quantity'])
                    ->findOrFail($id);
                $oldQuantity = $oldProcurement->quantity;
                $oldDrugId = $oldProcurement->drug_id;
                
                // Обновляем поставку
                $oldProcurement->update($validated);
                
                // Если изменился препарат или количество
                if ($oldDrugId != $validated['drug_id'] || $oldQuantity != $validated['quantity']) {
                    // Откатываем старое изменение (уменьшаем количество старого препарата)
                    // Оптимизация: используем select для выбора только нужных полей
                    $oldDrug = Drug::select(['id', 'quantity'])->find($oldDrugId);
                    $oldDrug->decrement('quantity', $oldQuantity);
                    
                    // Применяем новое изменение (увеличиваем количество нового препарата)
                    // Оптимизация: используем select для выбора только нужных полей
                    $newDrug = Drug::select(['id', 'quantity'])->find($validated['drug_id']);
                    $newDrug->increment('quantity', $validated['quantity']);
                } else {
                    // Если препарат не изменился, но изменилось количество
                    $quantityDiff = $validated['quantity'] - $oldQuantity;
                    if ($quantityDiff != 0) {
                        // Оптимизация: используем select для выбора только нужных полей
                        $drug = Drug::select(['id', 'quantity'])->find($validated['drug_id']);
                        $drug->increment('quantity', $quantityDiff);
                    }
                }
            });

            Log::info('Поставка успешно обновлена', [
                'procurement_id' => $id,
                'drug_id' => $validated['drug_id'],
                'quantity' => $validated['quantity'],
                'supplier_id' => $validated['supplier_id'] ?? null
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Поставка успешно обновлена');

        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении поставки', [
                'procurement_id' => $id,
                'data' => $validated ?? $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении поставки: ' . $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            // Оптимизация: используем select для выбора только нужных полей
            $procurement = $this->model::select(['id', 'drug_id', 'quantity'])->findOrFail($id);
            
            // Убираем проверку зависимостей - поставка не имеет зависимостей для проверки
            
            DB::transaction(function () use ($procurement) {
                $quantity = $procurement->quantity;
                $drugId = $procurement->drug_id;
                
                // Удаляем поставку
                $procurement->delete();
                
                // Уменьшаем количество на складе
                // Оптимизация: используем select для выбора только нужных полей
                $drug = Drug::select(['id', 'quantity'])->find($drugId);
                $drug->decrement('quantity', $quantity);
            });

            Log::info('Поставка успешно удалена', [
                'procurement_id' => $id,
                'drug_id' => $procurement->drug_id ?? null,
                'quantity' => $procurement->quantity ?? null
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Поставка успешно удалена');

        } catch (\Exception $e) {
            Log::error('Ошибка при удалении поставки', [
                'procurement_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['error' => 'Ошибка при удалении поставки: ' . $e->getMessage()]);
        }
    }
} 