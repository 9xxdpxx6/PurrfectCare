<?php

namespace App\Services\Settings;

use App\Models\Specialty;
use App\Http\Filters\Settings\SpecialtyFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();
            
            $specialty = Specialty::create($data);
            
            DB::commit();
            
            Log::info('Специальность успешно создана', [
                'specialty_id' => $specialty->id,
                'specialty_name' => $specialty->name
            ]);
            
            return $specialty;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании специальности', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить специальность
     */
    public function update(Specialty $specialty, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $specialty->name;
            $result = $specialty->update($data);
            
            DB::commit();
            
            Log::info('Специальность успешно обновлена', [
                'specialty_id' => $specialty->id,
                'old_name' => $oldName,
                'new_name' => $specialty->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении специальности', [
                'specialty_id' => $specialty->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить специальность
     */
    public function delete(Specialty $specialty)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $specialty->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $specialtyName = $specialty->name;
            $result = $specialty->delete();
            
            DB::commit();
            
            Log::info('Специальность успешно удалена', [
                'specialty_id' => $specialty->id,
                'specialty_name' => $specialtyName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении специальности', [
                'specialty_id' => $specialty->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все специальности для селекта
     */
    public function getForSelect()
    {
        return Specialty::orderBy('name')->get();
    }
} 