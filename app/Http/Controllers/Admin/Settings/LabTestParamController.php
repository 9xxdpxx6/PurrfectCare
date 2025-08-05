<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\LabTestParam;
use App\Services\Settings\LabTestParamService;
use App\Http\Requests\Settings\LabTestParam\StoreLabTestParamRequest;
use App\Http\Requests\Settings\LabTestParam\UpdateLabTestParamRequest;
use Illuminate\Http\Request;

class LabTestParamController extends SettingsController
{
    protected $service;

    public function __construct(LabTestParamService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список параметров анализов
     */
    public function index()
    {
        $labTestParams = $this->service->getAll(request()->all());
        $labTestTypes = $this->service->getLabTestTypesForSelect();
        $units = $this->service->getUnitsForSelect();
        
        return view('admin.settings.lab-test-params', compact('labTestParams', 'labTestTypes', 'units'));
    }

    /**
     * Создать новый параметр анализа
     */
    public function store(StoreLabTestParamRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании параметра анализа');
        }
    }

    /**
     * Обновить параметр анализа
     */
    public function update(UpdateLabTestParamRequest $request, LabTestParam $labTestParam)
    {
        try {
            $this->service->update($labTestParam, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении параметра анализа');
        }
    }

    /**
     * Удалить параметр анализа
     */
    public function destroy(LabTestParam $labTestParam)
    {
        try {
            $this->service->delete($labTestParam);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 