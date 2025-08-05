<?php

namespace App\Services\Settings;

use App\Models\Unit;
use App\Http\Filters\Settings\UnitFilter;

class UnitService
{
    /**
     * Получить все единицы измерения с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Unit::filter(new UnitFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новую единицу измерения
     */
    public function create(array $data)
    {
        return Unit::create($data);
    }

    /**
     * Обновить единицу измерения
     */
    public function update(Unit $unit, array $data)
    {
        return $unit->update($data);
    }

    /**
     * Удалить единицу измерения
     */
    public function delete(Unit $unit)
    {
        if ($errorMessage = $unit->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $unit->delete();
    }

    /**
     * Получить все единицы измерения для селекта
     */
    public function getForSelect()
    {
        return Unit::orderBy('name')->get();
    }
} 