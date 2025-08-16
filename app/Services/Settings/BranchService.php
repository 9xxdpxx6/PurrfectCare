<?php

namespace App\Services\Settings;

use App\Models\Branch;
use App\Http\Filters\Settings\BranchFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();
            
            $branch = Branch::create($data);
            
            DB::commit();
            
            Log::info('Филиал успешно создан', [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name
            ]);
            
            return $branch;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании филиала', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить филиал
     */
    public function update(Branch $branch, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $branch->name;
            $result = $branch->update($data);
            
            DB::commit();
            
            Log::info('Филиал успешно обновлен', [
                'branch_id' => $branch->id,
                'old_name' => $oldName,
                'new_name' => $branch->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении филиала', [
                'branch_id' => $branch->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить филиал
     */
    public function delete(Branch $branch)
    {
        try {
            DB::beginTransaction();
            
            // Убираем проверку зависимостей - связи с услугами удаляются каскадно
            $branchName = $branch->name;
            $result = $branch->delete();
            
            DB::commit();
            
            Log::info('Филиал успешно удален', [
                'branch_id' => $branch->id,
                'branch_name' => $branchName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении филиала', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все филиалы для селекта
     */
    public function getForSelect()
    {
        return Branch::orderBy('name')->get();
    }
} 