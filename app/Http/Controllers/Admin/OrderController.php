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
use App\Models\LabTestType;
use App\Models\Vaccination;
use App\Models\VaccinationType;
use App\Http\Requests\Admin\Order\StoreRequest;
use App\Http\Requests\Admin\Order\UpdateRequest;
use App\Http\Filters\OrderFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends AdminController
{
    use HasOptionsMethods;
    public function __construct()
    {
        $this->model = Order::class;
        $this->viewPath = 'orders';
        $this->routePrefix = 'orders';
    }

    public function create(): View
    {
        // Получаем ID клиента и питомца из параметров запроса
        $selectedClientId = request('client');
        $selectedPetId = request('pet');
        
        // Если передан pet_id, но не передан client_id, получаем владельца питомца
        if ($selectedPetId && !$selectedClientId) {
            $pet = Pet::with('client')->find($selectedPetId);
            if ($pet && $pet->client) {
                $selectedClientId = $pet->client->id;
            }
        }
        
        return view("admin.{$this->viewPath}.create", compact('selectedClientId', 'selectedPetId'));
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
            'client', 'pet', 'status', 'branch', 'manager', 'items.item', 'visits'
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
            'items.item', 'visits.status'
        ])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
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
            $this->createOrderItem($order, $item, $validated);
        }
        
        // Списание со склада только если заказ закрыт
        if ($closedAt) {
            $this->processInventoryReduction($order);
        }
        
        // Сохраняем связи с приемами
        if ($request->has('visits') && is_array($request->visits)) {
            $order->visits()->sync($request->visits);
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно создан');
        } catch (\Exception $e) {
            \Log::error('Order store error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
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
            $this->createOrderItem($order, $item, $validated);
        }
        
        // Списание со склада только если заказ закрыт
        if ($closedAt) {
            $this->processInventoryReduction($order);
        }
        
        // Обновляем связи с приемами
        if ($request->has('visits') && is_array($request->visits)) {
            $order->visits()->sync($request->visits);
        } else {
            $order->visits()->detach(); // Удаляем все связи, если приемы не выбраны
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно обновлен');
        } catch (\Exception $e) {
            \Log::error('Order update error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении заказа: ' . $e->getMessage()]);
        }
    }

    protected function getItemType($type)
    {
        return match($type) {
            'service' => Service::class,
            'drug' => Drug::class,
            'lab_test' => LabTestType::class,
            'vaccination' => VaccinationType::class,
            default => throw new \InvalidArgumentException('Неизвестный тип элемента')
        };
    }

    protected function createOrderItem($order, $item, $validated)
    {
        // Для всех типов элементов создаем OrderItem напрямую
        return $order->items()->create([
            'item_type' => $this->getItemType($item['item_type']),
            'item_id' => $item['item_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price']
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        $order = $this->model::with('items')->findOrFail($id);
        
        // Убираем проверку зависимостей - элементы заказа удаляются каскадно
        
        // Возвращаем препараты на склад если заказ был закрыт
        if ($order->closed_at) {
            $this->processInventoryReturn($order);
        }
        
        // Удаляем сам заказ (элементы удалятся каскадно)
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
    public function orderServiceOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        return app(\App\Services\Options\ServiceOptionsService::class)->getOptions($request);
    }

    public function orderDrugOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        return app(\App\Services\Options\DrugOptionsService::class)->getOptions($request);
    }

    public function orderLabTestOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        return app(\App\Services\Options\LabTestOptionsService::class)->getLabTestTypeOptions($request);
    }

    public function orderVaccinationOptions(Request $request)
    {
        $request->merge(['include_price' => true]);
        return app(\App\Services\Options\VaccinationTypeOptionsService::class)->getOptions($request);
    }

    public function orderVisitOptions(Request $request)
    {
        return app(\App\Services\Options\VisitOptionsService::class)->getOptions($request);
    }

    // TomSelect опции для основных полей
    public function clientOptions(Request $request)
    {
        return app(\App\Services\Options\ClientOptionsService::class)->getOptions($request);
    }

    public function petOptions(Request $request)
    {
        return app(\App\Services\Options\PetOptionsService::class)->getOptions($request);
    }

    public function statusOptions(Request $request)
    {
        return app(\App\Services\Options\StatusOptionsService::class)->getOptions($request);
    }

    public function branchOptions(Request $request)
    {
        return app(\App\Services\Options\BranchOptionsService::class)->getOptions($request);
    }

    public function managerOptions(Request $request)
    {
        return app(\App\Services\Options\EmployeeOptionsService::class)->getManagerOptions($request);
    }
} 