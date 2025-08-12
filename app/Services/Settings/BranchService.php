<?php

namespace App\Services\Settings;

use App\Models\Branch;
use App\Http\Filters\Settings\BranchFilter;

class BranchService
{
    /**
     * Получить все филиалы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Branch::filter(new BranchFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый филиал
     */
    public function create(array $data)
    {
        return Branch::create($data);
    }

    /**
     * Обновить филиал
     */
    public function update(Branch $branch, array $data)
    {
        return $branch->update($data);
    }

    /**
     * Удалить филиал
     */
    public function delete(Branch $branch)
    {
        // Убираем проверку зависимостей - связи с услугами удаляются каскадно
        
        return $branch->delete();
    }

    /**
     * Получить все филиалы для селекта
     */
    public function getForSelect()
    {
        return Branch::orderBy('name')->get();
    }
} 