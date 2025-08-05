<?php

namespace App\Services\Settings;

use App\Models\LabTestType;
use App\Http\Filters\Settings\LabTestTypeFilter;

class LabTestTypeService
{
    /**
     * Получить все типы анализов с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return LabTestType::filter(new LabTestTypeFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый тип анализа
     */
    public function create(array $data)
    {
        return LabTestType::create($data);
    }

    /**
     * Обновить тип анализа
     */
    public function update(LabTestType $labTestType, array $data)
    {
        return $labTestType->update($data);
    }

    /**
     * Удалить тип анализа
     */
    public function delete(LabTestType $labTestType)
    {
        if ($errorMessage = $labTestType->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $labTestType->delete();
    }

    /**
     * Получить все типы анализов для селекта
     */
    public function getForSelect()
    {
        return LabTestType::orderBy('name')->get();
    }
} 