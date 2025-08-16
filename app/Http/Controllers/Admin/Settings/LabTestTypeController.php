<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\LabTestType;
use App\Services\Settings\LabTestTypeService;
use App\Http\Requests\Admin\Settings\LabTestType\StoreRequest;
use App\Http\Requests\Admin\Settings\LabTestType\UpdateRequest;
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
    public function store(StoreRequest $request)
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
    public function update(UpdateRequest $request, LabTestType $type)
    {
        try {
            $this->service->update($type, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении типа анализа');
        }
    }

    /**
     * Удалить тип анализа
     */
    public function destroy(LabTestType $type)
    {
        try {
            $this->service->delete($type);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 