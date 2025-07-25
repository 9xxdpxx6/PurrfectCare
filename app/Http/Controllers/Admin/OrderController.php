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
        $this->validationRules = [
            'client_id' => 'required|exists:users,id',
            'pet_id' => 'required|exists:pets,id',
            'status_id' => 'required|exists:statuses,id',
            'branch_id' => 'required|exists:branches,id',
            'manager_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.type' => 'required|in:service,drug,lab_test,vaccination',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ];
    }

    public function create(): View
    {
        $clients = User::all();
        $pets = Pet::all();
        $statuses = Status::all();
        $branches = Branch::all();
        $managers = Employee::where('position', 'manager')->get();
        $services = Service::all();
        $drugs = Drug::all();
        $labTests = LabTest::all();
        $vaccinations = Vaccination::all();
        return view("admin.{$this->viewPath}.create", compact(
            'clients', 'pets', 'statuses', 'branches', 'managers',
            'services', 'drugs', 'labTests', 'vaccinations'
        ));
    }

    public function edit($id): View
    {
        $item = $this->model::with(['items', 'client', 'pet', 'status', 'branch', 'manager'])->findOrFail($id);
        $clients = User::all();
        $pets = Pet::all();
        $statuses = Status::all();
        $branches = Branch::all();
        $managers = Employee::where('position', 'manager')->get();
        $services = Service::all();
        $drugs = Drug::all();
        $labTests = LabTest::all();
        $vaccinations = Vaccination::all();
        return view("admin.{$this->viewPath}.edit", compact(
            'item', 'clients', 'pets', 'statuses', 'branches', 'managers',
            'services', 'drugs', 'labTests', 'vaccinations'
        ));
    }

    public function index(Request $request) : View
    {
        $items = $this->model::with(['client', 'pet', 'status', 'branch', 'manager'])->paginate(25);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function show($id) : View
    {
        $order = $this->model::with([
            'client', 'pet', 'status', 'branch', 'manager',
            'items.item'
        ])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('order'));
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $order = $this->model::create([
            'client_id' => $validated['client_id'],
            'pet_id' => $validated['pet_id'],
            'status_id' => $validated['status_id'],
            'branch_id' => $validated['branch_id'],
            'manager_id' => $validated['manager_id'],
            'notes' => $validated['notes'] ?? null,
            'total' => $validated['total']
        ]);

        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'item_type' => $this->getItemType($item['type']),
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Заказ успешно создан');
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        $order = $this->model::findOrFail($id);
        $order->update([
            'client_id' => $validated['client_id'],
            'pet_id' => $validated['pet_id'],
            'status_id' => $validated['status_id'],
            'branch_id' => $validated['branch_id'],
            'manager_id' => $validated['manager_id'],
            'notes' => $validated['notes'] ?? null,
            'total' => $validated['total']
        ]);

        $order->items()->delete();
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'item_type' => $this->getItemType($item['type']),
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
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
} 