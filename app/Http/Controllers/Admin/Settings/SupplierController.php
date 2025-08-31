<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Supplier;
use App\Services\Settings\SupplierService;
use App\Http\Requests\Admin\Settings\Supplier\StoreRequest;
use App\Http\Requests\Admin\Settings\Supplier\UpdateRequest;
use Illuminate\Http\Request;

class SupplierController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'suppliers';

    public function __construct(SupplierService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Показать список поставщиков
     */
    public function index()
    {
        $suppliers = $this->service->getAll(request()->all());
        return view('admin.settings.suppliers', compact('suppliers'));
    }

    /**
     * Создать нового поставщика
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании поставщика');
        }
    }

    /**
     * Обновить поставщика
     */
    public function update(UpdateRequest $request, Supplier $supplier)
    {
        try {
            $this->service->update($supplier, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении поставщика');
        }
    }

    /**
     * Удалить поставщика
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $this->service->delete($supplier);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 