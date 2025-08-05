<?php

namespace App\Services\Settings;

use App\Models\DictionaryDiagnosis;
use App\Http\Filters\Settings\DictionaryDiagnosisFilter;

class DictionaryDiagnosisService
{
    /**
     * Получить все диагнозы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return DictionaryDiagnosis::filter(new DictionaryDiagnosisFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый диагноз
     */
    public function create(array $data)
    {
        return DictionaryDiagnosis::create($data);
    }

    /**
     * Обновить диагноз
     */
    public function update(DictionaryDiagnosis $dictionaryDiagnosis, array $data)
    {
        return $dictionaryDiagnosis->update($data);
    }

    /**
     * Удалить диагноз
     */
    public function delete(DictionaryDiagnosis $dictionaryDiagnosis)
    {
        if ($errorMessage = $dictionaryDiagnosis->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $dictionaryDiagnosis->delete();
    }

    /**
     * Получить все диагнозы для селекта
     */
    public function getForSelect()
    {
        return DictionaryDiagnosis::orderBy('name')->get();
    }
} 