<?php

namespace App\Services\Settings;

use App\Models\Status;
use App\Http\Filters\Settings\StatusFilter;

class StatusService
{
    /**
     * Получить все статусы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return Status::filter(new StatusFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый статус
     */
    public function create(array $data)
    {
        return Status::create($data);
    }

    /**
     * Обновить статус
     */
    public function update(Status $status, array $data)
    {
        return $status->update($data);
    }

    /**
     * Удалить статус
     */
    public function delete(Status $status)
    {
        if ($errorMessage = $status->hasDependencies()) {
            throw new \Exception($errorMessage);
        }
        
        return $status->delete();
    }

    /**
     * Получить все статусы для селекта
     */
    public function getForSelect()
    {
        return Status::orderBy('name')->get();
    }
} 