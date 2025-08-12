<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Models\DictionarySymptom;
use App\Services\Settings\DictionarySymptomService;
use App\Http\Requests\Settings\DictionarySymptom\StoreRequest;
use App\Http\Requests\Settings\DictionarySymptom\UpdateRequest;
use Illuminate\Http\Request;

class DictionarySymptomController extends SettingsController
{
    protected $service;

    public function __construct(DictionarySymptomService $service)
    {
        $this->service = $service;
    }

    /**
     * Показать список симптомов
     */
    public function index()
    {
        $dictionarySymptoms = $this->service->getAll(request()->all());
        return view('admin.settings.dictionary-symptoms', compact('dictionarySymptoms'));
    }

    /**
     * Создать новый симптом
     */
    public function store(StoreRequest $request)
    {
        try {
            $this->service->create($request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при создании симптома');
        }
    }

    /**
     * Обновить симптом
     */
    public function update(UpdateRequest $request, DictionarySymptom $dictionarySymptom)
    {
        try {
            $this->service->update($dictionarySymptom, $request->validated());
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse('Произошла ошибка при обновлении симптома');
        }
    }

    /**
     * Удалить симптом
     */
    public function destroy(DictionarySymptom $dictionarySymptom)
    {
        try {
            $this->service->delete($dictionarySymptom);
            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->dependencyErrorResponse($e->getMessage());
        }
    }
} 