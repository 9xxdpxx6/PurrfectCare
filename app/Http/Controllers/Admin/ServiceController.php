<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\Branch;
use App\Http\Requests\Admin\Service\StoreRequest;
use App\Http\Requests\Admin\Service\UpdateRequest;
use App\Http\Filters\ServiceFilter;
use App\Http\Traits\HasSelectOptions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceController extends AdminController
{
    use HasSelectOptions;

    public function __construct()
    {
        $this->model = Service::class;
        $this->viewPath = 'services';
        $this->routePrefix = 'services';
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
        $validated = $request->validated();
        $branches = $validated['branches'];
        unset($validated['branches']);
        
        $service = $this->model::create($validated);
        $service->branches()->sync($branches);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Услуга успешно создана');
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        $branches = $validated['branches'];
        unset($validated['branches']);
        
        $service = $this->model::findOrFail($id);
        $service->update($validated);
        $service->branches()->sync($branches);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Услуга успешно обновлена');
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
        $item = $this->model::findOrFail($id);
        
        // Проверяем наличие зависимых записей
        if ($errorMessage = $item->hasDependencies()) {
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('error', $errorMessage);
        }
        
        $item->branches()->detach();
        $item->delete();

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Услуга успешно удалена');
    }
} 