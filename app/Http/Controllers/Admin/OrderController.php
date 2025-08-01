<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Status;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTest;
use App\Models\Vaccination;
use App\Http\Requests\Admin\Order\StoreRequest;
use App\Http\Requests\Admin\Order\UpdateRequest;
use App\Http\Filters\OrderFilter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends AdminController
{
    public function __construct()
    {
        $this->model = Order::class;
        $this->viewPath = 'orders';
        $this->routePrefix = 'orders';
    }

    public function create(): View
    {
        return view("admin.{$this->viewPath}.create");
    }

    public function edit($id): View
    {
        $item = $this->model::with(['items.item'])->findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function index(Request $request): View
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
        $queryParams = $request->query();
        if (isset($queryParams['created_at_from']) && $queryParams['created_at_from']) {
            try {
                $queryParams['created_at_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['created_at_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['created_at_to']) && $queryParams['created_at_to']) {
            try {
                $queryParams['created_at_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['created_at_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(OrderFilter::class, ['queryParams' => $queryParams]);
        $query = $this->model::with([
            'client', 'pet', 'status', 'branch', 'manager', 'items.item'
        ])->filter($filter);
        
        // Если есть поиск и это число, сортируем результаты
        $search = $request->input('search');
        if ($search && is_numeric($search)) {
            $query->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$search])
                  ->orderBy('id', 'desc');
        }
        
        $items = $query->paginate(25)->withQueryString();
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function show($id): View
    {
        $item = $this->model::with([
            'client', 'pet', 'status', 'branch', 'manager',
            'items.item'
        ])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Определяем дату закрытия если заказ выполнен
        $closedAt = null;
        if ($request->has('is_closed') && $request->input('is_closed')) {
            $closedAt = now();
        }
        
        $order = $this->model::create([
            'client_id' => $validated['client_id'],
            'pet_id' => $validated['pet_id'],
            'status_id' => $validated['status_id'],
            'branch_id' => $validated['branch_id'],
            'manager_id' => $validated['manager_id'],
            'notes' => $validated['notes'] ?? null,
            'total' => $validated['total'],
            'is_paid' => $request->has('is_paid') && $request->input('is_paid'),
            'closed_at' => $closedAt
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'item_type' => $this->getItemType($item['item_type']),
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ]);
        }
        
        // Списание со склада только если заказ закрыт
        if ($closedAt) {
            $this->processInventoryReduction($order);
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно создан');
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        $validated = $request->validated();
        
        $order = $this->model::with('items')->findOrFail($id);
        
        // Определяем дату закрытия если заказ выполнен
        $closedAt = $order->closed_at;
        if ($request->has('is_closed') && $request->input('is_closed') && !$closedAt) {
            $closedAt = now();
        } elseif (!$request->has('is_closed') || !$request->input('is_closed')) {
            $closedAt = null;
        }
        
        // Возвращаем препараты на склад из старого заказа если он был закрыт
        if ($order->closed_at) {
            $this->processInventoryReturn($order);
        }
        
        $order->update([
            'client_id' => $validated['client_id'],
            'pet_id' => $validated['pet_id'],
            'status_id' => $validated['status_id'],
            'branch_id' => $validated['branch_id'],
            'manager_id' => $validated['manager_id'],
            'notes' => $validated['notes'] ?? null,
            'total' => $validated['total'],
            'is_paid' => $request->has('is_paid') && $request->input('is_paid'),
            'closed_at' => $closedAt
        ]);

        $order->items()->delete();
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'item_type' => $this->getItemType($item['item_type']),
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price']
            ]);
        }
        
        // Списание со склада только если заказ закрыт
        if ($closedAt) {
            $this->processInventoryReduction($order);
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно обновлен');
    }

    protected function getItemType($type)
    {
        return match($type) {
            'service' => Service::class,
            'drug' => Drug::class,
            'lab_test' => LabTest::class,
            'vaccination' => Vaccination::class,
            default => throw new \InvalidArgumentException('Неизвестный тип элемента')
        };
    }

    public function destroy($id): RedirectResponse
    {
        $order = $this->model::with('items')->findOrFail($id);
        
        // Проверяем наличие зависимых записей
        if ($errorMessage = $order->hasDependencies()) {
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('error', $errorMessage);
        }
        
        // Возвращаем препараты на склад если заказ был закрыт
        if ($order->closed_at) {
            $this->processInventoryReturn($order);
        }
        
        // Удаляем элементы заказа
        $order->items()->delete();
        
        // Удаляем сам заказ
        $order->delete();

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно удален');
    }
    
    /**
     * Обработка списания препаратов со склада
     */
    protected function processInventoryReduction($order)
    {
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                // Списание препаратов из заказа
                $drug = Drug::find($item->item_id);
                if ($drug) {
                    $drug->decrement('quantity', $item->quantity);
                }
            }
        }
    }
    
    /**
     * Обработка возврата препаратов на склад
     */
    protected function processInventoryReturn($order)
    {
        foreach ($order->items as $item) {
            if ($item->item_type === 'App\Models\Drug') {
                // Возврат препаратов из заказа
                $drug = Drug::find($item->item_id);
                if ($drug) {
                    $drug->increment('quantity', $item->quantity);
                }
            }
        }
    }

    // TomSelect опции
    public function clientOptions(Request $request)
    {
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->clientOptions($request);
    }

    public function petOptions(Request $request)
    {
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->petOptions($request);
    }

    public function statusOptions(Request $request)
    {
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->statusOptions($request);
    }

    public function branchOptions(Request $request)
    {
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->branchOptions($request);
    }

    public function managerOptions(Request $request)
    {
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->managerOptions($request);
    }

    public function orderServiceOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->serviceOptions($request);
    }

    public function orderDrugOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->drugOptions($request);
    }

    public function labTestOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->labTestOptions($request);
    }

    public function vaccinationOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        $trait = new class {
            use \App\Http\Traits\HasSelectOptions;
        };
        return $trait->vaccinationOptions($request);
    }
} 