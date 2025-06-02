<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;

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

    public function index()
    {
        $items = $this->model::with('procurements')->paginate(10);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }
} 