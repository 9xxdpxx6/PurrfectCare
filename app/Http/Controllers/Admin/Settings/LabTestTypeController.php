<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\LabTestType;
use App\Services\Settings\LabTestTypeService;
use App\Http\Requests\Settings\LabTestType\StoreLabTestTypeRequest;
use App\Http\Requests\Settings\LabTestType\UpdateLabTestTypeRequest;
use Illuminate\Http\Request;

class LabTestTypeController extends SettingsController
{
    protected $service;

    public function __construct(LabTestTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список типов анализов
     */
    public function index()
    {
        $labTestTypes = $this->service->getAll(request()->all());
        return view('admin.settings.lab-test-types', compact('labTestTypes'));
    }

    /**
     * Создать новый тип анализа
     */
    public function store(StoreLabTestTypeRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании типа анализа');
        }
    }

    /**
     * Обновить тип анализа
     */
    public function update(UpdateLabTestTypeRequest $request, LabTestType $labTestType)
    {
        try {
            $this->service->update($labTestType, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении типа анализа');
        }
    }

    /**
     * Удалить тип анализа
     */
    public function destroy(LabTestType $labTestType)
    {
        try {
            $this->service->delete($labTestType);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 