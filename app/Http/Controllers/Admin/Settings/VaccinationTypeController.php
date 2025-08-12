<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\VaccinationType;
use App\Services\Settings\VaccinationTypeService;
use App\Http\Requests\Settings\VaccinationType\StoreRequest;
use App\Http\Requests\Settings\VaccinationType\UpdateRequest;
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
     * Показать информацию о типе вакцинации (для AJAX)
     */
    public function show(VaccinationType $vaccinationType)
    {
        return response()->json([
            'id' => $vaccinationType->id,
            'name' => $vaccinationType->name,
            'price' => $vaccinationType->price,
            'description' => $vaccinationType->description,
            'drugs' => $vaccinationType->drugs->map(function($drug) {
                return [
                    'id' => $drug->id,
                    'name' => $drug->name,
                    'pivot' => [
                        'dosage' => $drug->pivot->dosage,
                    ],
                    'unit' => $drug->unit ? [
                        'id' => $drug->unit->id,
                        'symbol' => $drug->unit->symbol,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * Создать новый тип вакцинации
     */
    public function store(StoreRequest $request)
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
    public function update(UpdateRequest $request, VaccinationType $vaccinationType)
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
     * Получить препараты типа вакцинации
     */
    public function getDrugs(VaccinationType $vaccinationType)
    {
        $drugs = $vaccinationType->drugs->map(function($drug) {
            return [
                'id' => $drug->id,
                'name' => $drug->name,
                'dosage' => $drug->pivot->dosage,
                'price' => $drug->price,
                'unit' => $drug->unit ? $drug->unit->symbol : null,
            ];
        });
        
        return response()->json($drugs);
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