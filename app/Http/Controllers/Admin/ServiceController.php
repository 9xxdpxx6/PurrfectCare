<?php

namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Models\Branch;
use Illuminate\Http\Request;

class ServiceController extends AdminController
{
    public function __construct()
    {
        $this->model = Service::class;
        $this->viewPath = 'services';
        $this->routePrefix = 'services';
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'branches' => 'required|array',
            'branches.*' => 'exists:branches,id'
        ];
    }

    public function create()
    {
        $branches = Branch::all();
        return view("admin.{$this->viewPath}.create", compact('branches'));
    }

    public function edit($id)
    {
        $item = $this->model::with('branches')->findOrFail($id);
        $branches = Branch::all();
        return view("admin.{$this->viewPath}.edit", compact('item', 'branches'));
    }

    public function index()
    {
        $items = $this->model::with('branches')->paginate(10);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);
        $branches = $validated['branches'];
        unset($validated['branches']);
        
        $service = $this->model::create($validated);
        $service->branches()->sync($branches);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Услуга успешно создана');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->validationRules);
        $branches = $validated['branches'];
        unset($validated['branches']);
        
        $service = $this->model::findOrFail($id);
        $service->update($validated);
        $service->branches()->sync($branches);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Услуга успешно обновлена');
    }
} 