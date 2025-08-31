<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\LabTestParam;
use App\Services\Settings\LabTestParamService;
use App\Http\Requests\Admin\Settings\LabTestParam\StoreRequest;
use App\Http\Requests\Admin\Settings\LabTestParam\UpdateRequest;
use Illuminate\Http\Request;

class LabTestParamController extends SettingsController
{
    protected $service;
    protected $permissionPrefix = 'lab-test-params';

    public function __construct(LabTestParamService $service)
    {
        parent::__construct();
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
    public function store(StoreRequest $request)
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
    public function update(UpdateRequest $request, LabTestParam $param)
    {
        try {
            $this->service->update($param, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении параметра анализа');
        }
    }

    /**
     * Удалить параметр анализа
     */
    public function destroy(LabTestParam $param)
    {
        try {
            $this->service->delete($param);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 