<?php

namespace App\Services\Settings;

use App\Models\Status;
use App\Http\Filters\Settings\StatusFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();
            
            $status = Status::create($data);
            
            DB::commit();
            
            Log::info('Статус успешно создан', [
                'status_id' => $status->id,
                'status_name' => $status->name
            ]);
            
            return $status;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании статуса', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить статус
     */
    public function update(Status $status, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $status->name;
            $result = $status->update($data);
            
            DB::commit();
            
            Log::info('Статус успешно обновлен', [
                'status_id' => $status->id,
                'old_name' => $oldName,
                'new_name' => $status->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении статуса', [
                'status_id' => $status->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить статус
     */
    public function delete(Status $status)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $status->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $statusName = $status->name;
            $result = $status->delete();
            
            DB::commit();
            
            Log::info('Статус успешно удален', [
                'status_id' => $status->id,
                'status_name' => $statusName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении статуса', [
                'status_id' => $status->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все статусы для селекта
     */
    public function getForSelect()
    {
        return Status::orderBy('name')->get();
    }
} 