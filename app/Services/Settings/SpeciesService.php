<?php

namespace App\Services\Settings;

use App\Models\Species;
use App\Http\Filters\Settings\SpeciesFilter;

class SpeciesService
{
    /**
     * Получить все виды животных с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Species::filter(new SpeciesFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый вид животного
     */
    public function create(array $data)
    {
        return Species::create($data);
    }

    /**
     * Обновить вид животного
     */
    public function update(Species $species, array $data)
    {
        return $species->update($data);
    }

    /**
     * Удалить вид животного
     */
    public function delete(Species $species)
    {
        if ($errorMessage = $species->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $species->delete();
    }

    /**
     * Получить все виды животных для селекта
     */
    public function getForSelect()
    {
        return Species::orderBy('name')->get();
    }
} 