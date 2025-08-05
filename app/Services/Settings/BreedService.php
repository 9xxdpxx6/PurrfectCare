<?php

namespace App\Services\Settings;

use App\Models\Breed;
use App\Models\Species;
use App\Http\Filters\Settings\BreedFilter;

class BreedService
{
    /**
     * Получить все породы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Breed::with('species')
            ->filter(new BreedFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новую породу
     */
    public function create(array $data)
    {
        return Breed::create($data);
    }

    /**
     * Обновить породу
     */
    public function update(Breed $breed, array $data)
    {
        return $breed->update($data);
    }

    /**
     * Удалить породу
     */
    public function delete(Breed $breed)
    {
        if ($errorMessage = $breed->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $breed->delete();
    }

    /**
     * Получить все виды животных для селекта
     */
    public function getSpeciesForSelect()
    {
        return Species::orderBy('name')->get();
    }
} 