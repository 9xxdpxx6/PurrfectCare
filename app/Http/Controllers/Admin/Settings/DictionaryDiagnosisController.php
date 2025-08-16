<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\DictionaryDiagnosis;
use App\Services\Settings\DictionaryDiagnosisService;
use App\Http\Requests\Admin\Settings\DictionaryDiagnosis\StoreRequest;
use App\Http\Requests\Admin\Settings\DictionaryDiagnosis\UpdateRequest;
use Illuminate\Http\Request;

class DictionaryDiagnosisController extends SettingsController
{
    protected $service;

    public function __construct(DictionaryDiagnosisService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список диагнозов
     */
    public function index()
    {
        $dictionaryDiagnoses = $this->service->getAll(request()->all());
        return view('admin.settings.dictionary-diagnoses', compact('dictionaryDiagnoses'));
    }

    /**
     * Создать новый диагноз
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании диагноза');
        }
    }

    /**
     * Обновить диагноз
     */
    public function update(UpdateRequest $request, DictionaryDiagnosis $dictionaryDiagnosis)
    {
        try {
            $this->service->update($dictionaryDiagnosis, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении диагноза');
        }
    }

    /**
     * Удалить диагноз
     */
    public function destroy(DictionaryDiagnosis $dictionaryDiagnosis)
    {
        try {
            $this->service->delete($dictionaryDiagnosis);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 