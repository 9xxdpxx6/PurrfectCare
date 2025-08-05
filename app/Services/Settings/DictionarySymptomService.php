<?php

namespace App\Services\Settings;

use App\Models\DictionarySymptom;
use App\Http\Filters\Settings\DictionarySymptomFilter;

class DictionarySymptomService
{
    /**
     * Получить все симптомы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return DictionarySymptom::filter(new DictionarySymptomFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый симптом
     */
    public function create(array $data)
    {
        return DictionarySymptom::create($data);
    }

    /**
     * Обновить симптом
     */
    public function update(DictionarySymptom $dictionarySymptom, array $data)
    {
        return $dictionarySymptom->update($data);
    }

    /**
     * Удалить симптом
     */
    public function delete(DictionarySymptom $dictionarySymptom)
    {
        if ($errorMessage = $dictionarySymptom->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $dictionarySymptom->delete();
    }

    /**
     * Получить все симптомы для селекта
     */
    public function getForSelect()
    {
        return DictionarySymptom::orderBy('name')->get();
    }
} 