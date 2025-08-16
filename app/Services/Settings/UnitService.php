<?php

namespace App\Services\Settings;

use App\Models\Unit;
use App\Http\Filters\Settings\UnitFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            DB::beginTransaction();
            
            $unit = Unit::create($data);
            
            DB::commit();
            
            Log::info('Единица измерения успешно создана', [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name
            ]);
            
            return $unit;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании единицы измерения', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить единицу измерения
     */
    public function update(Unit $unit, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $unit->name;
            $result = $unit->update($data);
            
            DB::commit();
            
            Log::info('Единица измерения успешно обновлена', [
                'unit_id' => $unit->id,
                'old_name' => $oldName,
                'new_name' => $unit->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении единицы измерения', [
                'unit_id' => $unit->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить единицу измерения
     */
    public function delete(Unit $unit)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $unit->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $unitName = $unit->name;
            $result = $unit->delete();
            
            DB::commit();
            
            Log::info('Единица измерения успешно удалена', [
                'unit_id' => $unit->id,
                'unit_name' => $unitName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении единицы измерения', [
                'unit_id' => $unit->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все единицы измерения для селекта
     */
    public function getForSelect()
    {
        return Unit::orderBy('name')->get();
    }
} 