<?php

namespace App\Services\Settings;

use App\Models\VaccinationType;
use App\Http\Filters\Settings\VaccinationTypeFilter;

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

        return $vaccinationType;
    }

    /**
     * Обновить тип вакцинации
     */
    public function update(VaccinationType $vaccinationType, array $data)
    {
        try {
            \Log::info('VaccinationTypeService update', [
                'vaccination_type_id' => $vaccinationType->id,
                'data' => $data
            ]);
            
            $vaccinationType->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
            ]);

            // Обновляем связи с препаратами
            $vaccinationType->drugs()->detach();
            
            if (isset($data['drugs']) && is_array($data['drugs'])) {
                foreach ($data['drugs'] as $drugData) {
                    \Log::info('Attaching drug', [
                        'drug_id' => $drugData['drug_id'],
                        'dosage' => $drugData['dosage']
                    ]);
                    
                    $vaccinationType->drugs()->attach($drugData['drug_id'], [
                        'dosage' => $drugData['dosage'],
                        // Batch template отключен по требованию клиники
                        // 'batch_template' => $drugData['batch_template'] ?? null,
                        'batch_template' => null,
                    ]);
                }
            }

            return $vaccinationType;
        } catch (\Exception $e) {
            \Log::error('Error in VaccinationTypeService update', [
                'vaccination_type_id' => $vaccinationType->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Удалить тип вакцинации
     */
    public function delete(VaccinationType $vaccinationType)
    {
        // Убираем проверку зависимостей - связи с препаратами удаляются каскадно
        
        return $vaccinationType->delete();
    }

    /**
     * Получить все типы вакцинаций для селекта
     */
    public function getForSelect()
    {
        return VaccinationType::orderBy('name')->get();
    }
}
