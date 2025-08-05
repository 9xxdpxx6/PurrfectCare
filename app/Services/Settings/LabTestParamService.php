<?php

namespace App\Services\Settings;

use App\Models\LabTestParam;
use App\Models\LabTestType;
use App\Models\Unit;
use App\Http\Filters\Settings\LabTestParamFilter;

class LabTestParamService
{
    /**
     * Получить все параметры анализов с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return LabTestParam::with(['labTestType', 'unit'])
            ->filter(new LabTestParamFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый параметр анализа
     */
    public function create(array $data)
    {
        return LabTestParam::create($data);
    }

    /**
     * Обновить параметр анализа
     */
    public function update(LabTestParam $labTestParam, array $data)
    {
        return $labTestParam->update($data);
    }

    /**
     * Удалить параметр анализа
     */
    public function delete(LabTestParam $labTestParam)
    {
        if ($errorMessage = $labTestParam->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $labTestParam->delete();
    }

    /**
     * Получить типы анализов для селекта
     */
    public function getLabTestTypesForSelect()
    {
        return LabTestType::orderBy('name')->get();
    }

    /**
     * Получить единицы измерения для селекта
     */
    public function getUnitsForSelect()
    {
        return Unit::orderBy('name')->get();
    }
} 