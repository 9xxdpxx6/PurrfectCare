<?php

namespace App\Services\Settings;

use App\Models\Specialty;
use App\Http\Filters\Settings\SpecialtyFilter;

class SpecialtyService
{
    /**
     * Получить все специальности с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Specialty::filter(new SpecialtyFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новую специальность
     */
    public function create(array $data)
    {
        return Specialty::create($data);
    }

    /**
     * Обновить специальность
     */
    public function update(Specialty $specialty, array $data)
    {
        return $specialty->update($data);
    }

    /**
     * Удалить специальность
     */
    public function delete(Specialty $specialty)
    {
        if ($errorMessage = $specialty->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $specialty->delete();
    }

    /**
     * Получить все специальности для селекта
     */
    public function getForSelect()
    {
        return Specialty::orderBy('name')->get();
    }
} 