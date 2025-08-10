<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\VaccinationType;
use App\Services\Settings\VaccinationTypeService;
use App\Http\Requests\Settings\VaccinationType\StoreVaccinationTypeRequest;
use App\Http\Requests\Settings\VaccinationType\UpdateVaccinationTypeRequest;
use Illuminate\Http\Request;

class VaccinationTypeController extends SettingsController
{
    protected $service;

    public function __construct(VaccinationTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список типов вакцинаций
     */
    public function index()
    {
        $vaccinationTypes = $this->service->getAll(request()->all());
        return view('admin.settings.vaccination-types', compact('vaccinationTypes'));
    }

    /**
     * Создать новый тип вакцинации
     */
    public function store(StoreVaccinationTypeRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании типа вакцинации');
        }
    }

    /**
     * Обновить тип вакцинации
     */
    public function update(UpdateVaccinationTypeRequest $request, VaccinationType $vaccinationType)
    {
        try {
            \Log::info('Updating vaccination type', [
                'id' => $vaccinationType->id,
                'data' => $request->validated()
            ]);
            
            $this->service->update($vaccinationType, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            \Log::error('Error updating vaccination type', [
                'id' => $vaccinationType->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Произошла ошибка при обновлении типа вакцинации: ' . $e->getMessage());
        }
    }

    /**
     * Удалить тип вакцинации
     */
    public function destroy(VaccinationType $vaccinationType)
    {
        try {
            $this->service->delete($vaccinationType);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
}