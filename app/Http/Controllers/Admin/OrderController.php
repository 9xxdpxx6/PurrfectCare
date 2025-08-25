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
use App\Services\Order\OrderManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends AdminController
{
    use HasOptionsMethods;
    
    protected $orderService;
    
    public function __construct(OrderManagementService $orderService)
    {
        $this->orderService = $orderService;
        $this->model = Order::class;
        $this->viewPath = 'orders';
        $this->routePrefix = 'orders';
    }

    public function create(): View
    {
        // Получаем ID клиента, питомца и приема из параметров запроса
        $selectedClientId = request('client');
        $selectedPetId = request('pet');
        $selectedVisitId = request('visit');
        
        // Если передан visit_id, получаем данные из приема
        if ($selectedVisitId && !$selectedClientId && !$selectedPetId) {
            // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
            $visit = \App\Models\Visit::select(['id', 'client_id', 'pet_id'])
                ->with([
                    'client:id,name,email',
                    'pet:id,name,breed_id'
                ])
                ->find($selectedVisitId);
            if ($visit) {
                $selectedClientId = $visit->client_id;
                $selectedPetId = $visit->pet_id;
            }
        }
        
        // Если передан pet_id, но не передан client_id, получаем владельца питомца
        if ($selectedPetId && !$selectedClientId) {
            // Оптимизация: используем индекс на client_id и select для выбора нужных полей
            $pet = Pet::select(['id', 'client_id'])
                ->with(['client:id,name,email'])
                ->find($selectedPetId);
            if ($pet && $pet->client) {
                $selectedClientId = $pet->client->id;
            }
        }
        
        return view("admin.{$this->viewPath}.create", compact('selectedClientId', 'selectedPetId', 'selectedVisitId'));
    }

    public function edit($id): View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                'notes', 'total', 'is_paid', 'closed_at', 'created_at', 'updated_at'
            ])
            ->with([
                'items:id,order_id,item_type,item_id,quantity,unit_price',
                'visits:id,client_id,pet_id,starts_at,status_id'
            ])
            ->findOrFail($id);
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
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                'total', 'is_paid', 'closed_at', 'created_at'
            ])
            ->with([
                'client:id,name,email',
                'pet:id,name,breed_id',
                'status:id,name',
                'branch:id,name',
                'manager:id,name',
                'items:id,order_id,item_type,item_id,quantity,unit_price',
                'visits:id,client_id,pet_id,starts_at,status_id'
            ])
            ->filter($filter);
        
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
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'client_id', 'pet_id', 'status_id', 'branch_id', 'manager_id',
                'notes', 'total', 'is_paid', 'closed_at', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone,address',
                'pet:id,name,breed_id,client_id,birthdate,gender',
                'status:id,name',
                'branch:id,name,address',
                'manager:id,name,email',
                'items:id,order_id,item_type,item_id,quantity,unit_price',
                'visits:id,client_id,pet_id,starts_at,status_id,is_completed'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            // Добавляем manager_id в валидированные данные
            // Используем правильный guard 'admin' для получения ID сотрудника
            $validated['manager_id'] = auth('admin')->id();
            
            $this->orderService->createOrder($validated, $request);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Заказ успешно создан');
        } catch (\Exception $e) {
            \Log::error('Order store error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании заказа. Попробуйте еще раз.']);
        }
    }

    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            $this->orderService->updateOrder($id, $validated, $request);
            
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



    public function destroy($id): RedirectResponse
    {
        $this->orderService->deleteOrder($id);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно удален');
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