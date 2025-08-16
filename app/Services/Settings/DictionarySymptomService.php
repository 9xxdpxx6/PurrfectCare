<?php

namespace App\Services\Settings;

use App\Models\DictionarySymptom;
use App\Http\Filters\Settings\DictionarySymptomFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DictionarySymptomService
{
    /**
     * Получить все симптомы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return DictionarySymptom::filter(new DictionarySymptomFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый симптом
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            
            $symptom = DictionarySymptom::create($data);
            
            DB::commit();
            
            Log::info('Симптом успешно создан', [
                'symptom_id' => $symptom->id,
                'symptom_name' => $symptom->name
            ]);
            
            return $symptom;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании симптома', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить симптом
     */
    public function update(DictionarySymptom $dictionarySymptom, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $dictionarySymptom->name;
            $result = $dictionarySymptom->update($data);
            
            DB::commit();
            
            Log::info('Симптом успешно обновлен', [
                'symptom_id' => $dictionarySymptom->id,
                'old_name' => $oldName,
                'new_name' => $dictionarySymptom->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении симптома', [
                'symptom_id' => $dictionarySymptom->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить симптом
     */
    public function delete(DictionarySymptom $dictionarySymptom)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $dictionarySymptom->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $symptomName = $dictionarySymptom->name;
            $result = $dictionarySymptom->delete();
            
            DB::commit();
            
            Log::info('Симптом успешно удален', [
                'symptom_id' => $dictionarySymptom->id,
                'symptom_name' => $symptomName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении симптома', [
                'symptom_id' => $dictionarySymptom->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все симптомы для селекта
     */
    public function getForSelect()
    {
        return DictionarySymptom::orderBy('name')->get();
    }
} 