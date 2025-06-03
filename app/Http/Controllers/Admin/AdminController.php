<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

abstract class AdminController extends Controller
{
    protected $model;
    protected $viewPath;
    protected $routePrefix;
    protected $validationRules = [];

    public function index(Request $request): View
    {
        $items = $this->model::paginate(100);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        return view("admin.{$this->viewPath}.create");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        $this->model::create($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись успешно создана');
    }

    public function edit($id): View
    {
        $item = $this->model::findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules);
        $item->update($validated);

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись успешно обновлена');
    }

    public function destroy($id): RedirectResponse
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись успешно удалена');
    }
}
