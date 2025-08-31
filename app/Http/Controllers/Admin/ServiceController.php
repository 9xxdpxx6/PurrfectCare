<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\Branch;
use App\Http\Requests\Admin\Service\StoreRequest;
use App\Http\Requests\Admin\Service\UpdateRequest;
use App\Http\Filters\ServiceFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        parent::__construct();
        $this->model = Service::class;
        $this->viewPath = 'services';
        $this->routePrefix = 'services';
        $this->permissionPrefix = 'services';
    }

    public function create(): View
    {
        $branches = Branch::all();
        return view("admin.{$this->viewPath}.create", compact('branches'));
    }

    public function edit($id): View
    {
        $item = $this->model::with('branches')->findOrFail($id);
        $branches = Branch::all();
        return view("admin.{$this->viewPath}.edit", compact('item', 'branches'));
    }

    public function index(Request $request): View
    {
        $filter = app()->make(ServiceFilter::class, ['queryParams' => array_filter($request->all())]);
        
        $query = $this->model::with('branches');
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        $branches = Branch::orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'branches'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $branches = $validated['branches'];
            unset($validated['branches']);
            
            $service = $this->model::create($validated);
            $service->branches()->sync($branches);
            
            DB::commit();
            
            Log::info('Услуга успешно создана', [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'branches_count' => count($branches)
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Услуга успешно создана');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании услуги', [
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании услуги: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            $branches = $validated['branches'];
            unset($validated['branches']);
            
            $service = $this->model::findOrFail($id);
            $oldName = $service->name;
            
            $service->update($validated);
            $service->branches()->sync($branches);
            
            DB::commit();
            
            Log::info('Услуга успешно обновлена', [
                'service_id' => $service->id,
                'old_name' => $oldName,
                'new_name' => $service->name,
                'branches_count' => count($branches)
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Услуга успешно обновлена');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении услуги', [
                'service_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении услуги: ' . $e->getMessage()]);
        }
    }

    public function show($id): View
    {
        $item = $this->model::with(['branches'])->findOrFail($id);
        
        // Подсчитываем количество раз, когда услуга была оказана (из order_items)
        $ordersCount = $item->orders()->sum('quantity');
        
        // Находим первое и последнее оказание услуги через order_items
        $firstOrderDate = $item->orders()
            ->with('order')
            ->get()
            ->pluck('order.created_at')
            ->filter()
            ->min();
            
        $lastOrderDate = $item->orders()
            ->with('order')
            ->get()
            ->pluck('order.created_at')
            ->filter()
            ->max();
        
        return view("admin.{$this->viewPath}.show", compact('item', 'ordersCount', 'firstOrderDate', 'lastOrderDate'));
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $item = $this->model::findOrFail($id);
            
            // Убираем проверку зависимостей - связи с филиалами удаляются каскадно
            $serviceName = $item->name;
            
            // Удаляем услугу (связи удалятся каскадно)
            $item->delete();
            
            DB::commit();
            
            Log::info('Услуга успешно удалена', [
                'service_id' => $id,
                'service_name' => $serviceName
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Услуга успешно удалена');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении услуги', [
                'service_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении услуги: ' . $e->getMessage()]);
        }
    }
} 