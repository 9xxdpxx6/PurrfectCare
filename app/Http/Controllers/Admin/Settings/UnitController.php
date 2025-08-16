<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\Unit;
use App\Services\Settings\UnitService;
use App\Http\Requests\Admin\Settings\Unit\StoreRequest;
use App\Http\Requests\Admin\Settings\Unit\UpdateRequest;
use Illuminate\Http\Request;

class UnitController extends SettingsController
{
    protected $service;

    public function __construct(UnitService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список единиц измерения
     */
    public function index()
    {
        $units = $this->service->getAll(request()->all());
        return view('admin.settings.units', compact('units'));
    }

    /**
     * Создать новую единицу измерения
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании единицы измерения');
        }
    }

    /**
     * Обновить единицу измерения
     */
    public function update(UpdateRequest $request, Unit $unit)
    {
        try {
            $this->service->update($unit, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении единицы измерения');
        }
    }

    /**
     * Удалить единицу измерения
     */
    public function destroy(Unit $unit)
    {
        try {
            $this->service->delete($unit);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 