<?php

namespace App\Services\Settings;

use App\Models\DictionaryDiagnosis;
use App\Http\Filters\Settings\DictionaryDiagnosisFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DictionaryDiagnosisService
{
    /**
     * Получить все диагнозы с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return DictionaryDiagnosis::filter(new DictionaryDiagnosisFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый диагноз
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            
            $diagnosis = DictionaryDiagnosis::create($data);
            
            DB::commit();
            
            Log::info('Диагноз успешно создан', [
                'diagnosis_id' => $diagnosis->id,
                'diagnosis_name' => $diagnosis->name
            ]);
            
            return $diagnosis;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании диагноза', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить диагноз
     */
    public function update(DictionaryDiagnosis $dictionaryDiagnosis, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $dictionaryDiagnosis->name;
            $result = $dictionaryDiagnosis->update($data);
            
            DB::commit();
            
            Log::info('Диагноз успешно обновлен', [
                'diagnosis_id' => $dictionaryDiagnosis->id,
                'old_name' => $oldName,
                'new_name' => $dictionaryDiagnosis->name
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении диагноза', [
                'diagnosis_id' => $dictionaryDiagnosis->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить диагноз
     */
    public function delete(DictionaryDiagnosis $dictionaryDiagnosis)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $dictionaryDiagnosis->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $diagnosisName = $dictionaryDiagnosis->name;
            $result = $dictionaryDiagnosis->delete();
            
            DB::commit();
            
            Log::info('Диагноз успешно удален', [
                'diagnosis_id' => $dictionaryDiagnosis->id,
                'diagnosis_name' => $diagnosisName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении диагноза', [
                'diagnosis_id' => $dictionaryDiagnosis->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все диагнозы для селекта
     */
    public function getForSelect()
    {
        return DictionaryDiagnosis::orderBy('name')->get();
    }
} 