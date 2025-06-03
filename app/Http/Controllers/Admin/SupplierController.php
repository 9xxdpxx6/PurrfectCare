<?php

namespace App\Http\Controllers\Admin;

use App\Http\Filters\SupplierFilter;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends AdminController
{
    public function __construct()
    {
        $this->model = Supplier::class;
        $this->viewPath = 'suppliers';
        $this->routePrefix = 'suppliers';
        $this->validationRules = [
            'name' => 'required|string|max:255'
        ];
    }

    public function index(Request $request) : View
    {
        $filter = app(SupplierFilter::class, ['queryParams' => $request->query()]);
        $query = Supplier::query()->with('procurements')->filter($filter);
        $items = $query->paginate(25)->withQueryString();

        return view("admin.{$this->viewPath}.index", compact('items'));
    }
}
