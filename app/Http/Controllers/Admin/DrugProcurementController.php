<?php

namespace App\Http\Controllers\Admin;

use App\Models\DrugProcurement;
use App\Models\Drug;
use App\Models\Supplier;
use App\Models\Branch;
use App\Http\Requests\Admin\DrugProcurement\StoreRequest;
use App\Http\Requests\Admin\DrugProcurement\UpdateRequest;
use App\Http\Filters\DrugProcurementFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
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

        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'drug_id', 'supplier_id', 'branch_id', 'quantity', 'price', 'delivery_date', 'expiry_date',
                'manufacture_date', 'packaging_date', 'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,unit_id',
                'supplier:id,name',
                'branch:id,name'
            ]);
            
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        
        // Оптимизация: используем select для выбора только нужных полей
        $drugs = Drug::select(['id', 'name'])->orderBy('name')->get();
        $suppliers = Supplier::select(['id', 'name'])->orderBy('name')->get();
        $branches = Branch::select(['id', 'name'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'drugs', 'suppliers', 'branches'));
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
                'id', 'drug_id', 'supplier_id', 'branch_id', 'quantity', 'price', 'delivery_date', 'expiry_date',
                'manufacture_date', 'packaging_date', 'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,unit_id',
                'drug.unit:id,name,symbol',
                'supplier:id,name',
                'branch:id,name'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function show($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'drug_id', 'supplier_id', 'branch_id', 'quantity', 'price', 'delivery_date', 'expiry_date',
                'manufacture_date', 'packaging_date', 'created_at', 'updated_at'
            ])
            ->with([
                'drug:id,name,unit_id',
                'drug.unit:id,name,symbol',
                'branch:id,name'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            // Логируем входящие данные для отладки
            Log::info('Создание поставки - входящие данные', [
                'all_data' => $request->all(),
                'validated_data' => $request->validated()
            ]);
            
            $validated = $request->validated();

            DB::transaction(function () use ($validated) {
                // Создаем поставку
                $procurement = $this->model::create($validated);
                
                // Увеличиваем количество на складе филиала
                $branchId = $validated['branch_id'];
                $drugId = $validated['drug_id'];
                $quantity = $validated['quantity'];
                
                // Проверяем, есть ли уже запись в branch_drug
                $branchDrug = Branch::find($branchId)
                    ->drugs()
                    ->where('drug_id', $drugId)
                    ->first();
                
                if ($branchDrug) {
                    // Увеличиваем количество в существующей записи
                    $branchDrug->pivot->increment('quantity', $quantity);
                } else {
                    // Создаем новую запись
                    Branch::find($branchId)->drugs()->attach($drugId, [
                        'quantity' => $quantity,
                    ]);
                }
            });

            Log::info('Поставка успешно создана', [
                'drug_id' => $validated['drug_id'],
                'branch_id' => $validated['branch_id'],
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
                $oldProcurement = $this->model::select(['id', 'drug_id', 'quantity', 'branch_id'])
                    ->findOrFail($id);
                $oldQuantity = $oldProcurement->quantity;
                $oldDrugId = $oldProcurement->drug_id;
                $oldBranchId = $oldProcurement->branch_id;
                
                // Обновляем поставку
                $oldProcurement->update($validated);
                
                $newBranchId = $validated['branch_id'];
                $newDrugId = $validated['drug_id'];
                $newQuantity = $validated['quantity'];
                
                // Если изменился препарат, филиал или количество
                if ($oldDrugId != $newDrugId || $oldBranchId != $newBranchId || $oldQuantity != $newQuantity) {
                    // Откатываем старое изменение (уменьшаем количество старого препарата в старом филиале)
                    $this->decreaseDrugQuantityInBranch($oldDrugId, $oldBranchId, $oldQuantity);
                    
                    // Применяем новое изменение (увеличиваем количество нового препарата в новом филиале)
                    $this->increaseDrugQuantityInBranch($newDrugId, $newBranchId, $newQuantity);
                }
            });

            Log::info('Поставка успешно обновлена', [
                'procurement_id' => $id,
                'drug_id' => $validated['drug_id'],
                'branch_id' => $validated['branch_id'],
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
            $procurement = $this->model::select(['id', 'drug_id', 'quantity', 'branch_id'])->findOrFail($id);
            
            // Убираем проверку зависимостей - поставка не имеет зависимостей для проверки
            
            DB::transaction(function () use ($procurement) {
                $quantity = $procurement->quantity;
                $drugId = $procurement->drug_id;
                $branchId = $procurement->branch_id;
                
                // Удаляем поставку
                $procurement->delete();
                
                // Уменьшаем количество на складе филиала
                $this->decreaseDrugQuantityInBranch($drugId, $branchId, $quantity);
            });

            Log::info('Поставка успешно удалена', [
                'procurement_id' => $id,
                'drug_id' => $procurement->drug_id ?? null,
                'branch_id' => $procurement->branch_id ?? null,
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

    /**
     * Увеличить количество препарата в филиале
     */
    protected function increaseDrugQuantityInBranch(int $drugId, int $branchId, int $quantity): void
    {
        $branch = Branch::find($branchId);
        $branchDrug = $branch->drugs()->where('drug_id', $drugId)->first();

        if ($branchDrug) {
            // Увеличиваем количество в существующей записи
            $branchDrug->pivot->increment('quantity', $quantity);
        } else {
            // Создаем новую запись
            $branch->drugs()->attach($drugId, [
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Уменьшить количество препарата в филиале
     */
    protected function decreaseDrugQuantityInBranch(int $drugId, int $branchId, int $quantity): void
    {
        $branch = Branch::find($branchId);
        $branchDrug = $branch->drugs()->where('drug_id', $drugId)->first();

        if ($branchDrug && $branchDrug->pivot->quantity >= $quantity) {
            $branchDrug->pivot->decrement('quantity', $quantity);
        } else {
            Log::warning('Попытка уменьшить количество препарата ниже 0', [
                'drug_id' => $drugId,
                'branch_id' => $branchId,
                'requested_decrease' => $quantity,
                'available' => $branchDrug ? $branchDrug->pivot->quantity : 0
            ]);
        }
    }

    /**
     * Получить опции филиалов для выпадающего списка
     */
    public function branchOptions(Request $request)
    {
        $query = $request->get('q', '');
        
        $branches = Branch::select(['id', 'name'])
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($branch) {
                return [
                    'value' => $branch->id,
                    'text' => $branch->name
                ];
            });

        return response()->json($branches);
    }
} 