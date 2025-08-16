<?php

namespace App\Services\Settings;

use App\Models\VaccinationType;
use App\Http\Filters\Settings\VaccinationTypeFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VaccinationTypeService
{
    /**
     * Получить все типы вакцинаций с фильтрацией и пагинацией
     */
    public function getAll(array $filters = [])
    {
        return VaccinationType::with('drugs')
            ->filter(new VaccinationTypeFilter($filters))
            ->orderByDesc('id')
            ->paginate(20);
    }

    /**
     * Создать новый тип вакцинации
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            
            $vaccinationType = VaccinationType::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
            ]);

            // Привязываем препараты к типу вакцинации
            if (isset($data['drugs']) && is_array($data['drugs'])) {
                foreach ($data['drugs'] as $drugData) {
                    $vaccinationType->drugs()->attach($drugData['drug_id'], [
                        'dosage' => $drugData['dosage'],
                        // Batch template отключен по требованию клиники
                        // 'batch_template' => $drugData['batch_template'] ?? null,
                        'batch_template' => null,
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Тип вакцинации успешно создан', [
                'vaccination_type_id' => $vaccinationType->id,
                'vaccination_type_name' => $vaccinationType->name,
                'drugs_count' => isset($data['drugs']) ? count($data['drugs']) : 0
            ]);
            
            return $vaccinationType;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании типа вакцинации', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить тип вакцинации
     */
    public function update(VaccinationType $vaccinationType, array $data)
    {
        try {
            DB::beginTransaction();
            
            $oldName = $vaccinationType->name;
            $oldPrice = $vaccinationType->price;
            
            $vaccinationType->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
            ]);

            // Обновляем связи с препаратами
            if (isset($data['drugs']) && is_array($data['drugs'])) {
                // Удаляем старые связи
                $vaccinationType->drugs()->detach();
                
                // Создаем новые связи
                foreach ($data['drugs'] as $drugData) {
                    $vaccinationType->drugs()->attach($drugData['drug_id'], [
                        'dosage' => $drugData['dosage'],
                        'batch_template' => null,
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Тип вакцинации успешно обновлен', [
                'vaccination_type_id' => $vaccinationType->id,
                'old_name' => $oldName,
                'new_name' => $vaccinationType->name,
                'old_price' => $oldPrice,
                'new_price' => $vaccinationType->price,
                'drugs_count' => isset($data['drugs']) ? count($data['drugs']) : 0
            ]);
            
            return $vaccinationType;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении типа вакцинации', [
                'vaccination_type_id' => $vaccinationType->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить тип вакцинации
     */
    public function delete(VaccinationType $vaccinationType)
    {
        try {
            DB::beginTransaction();
            
            if ($errorMessage = $vaccinationType->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $vaccinationTypeName = $vaccinationType->name;
            
            // Удаляем связи с препаратами
            $vaccinationType->drugs()->detach();
            
            $result = $vaccinationType->delete();
            
            DB::commit();
            
            Log::info('Тип вакцинации успешно удален', [
                'vaccination_type_id' => $vaccinationType->id,
                'vaccination_type_name' => $vaccinationTypeName
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении типа вакцинации', [
                'vaccination_type_id' => $vaccinationType->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить все типы вакцинаций для селекта
     */
    public function getForSelect()
    {
        return VaccinationType::orderBy('name')->get();
    }
}
