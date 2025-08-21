<?php

namespace App\Services\Visit;

use App\Models\Visit;
use App\Models\Symptom;
use App\Models\DictionarySymptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitSymptomService
{
    /**
     * Создать симптомы для приема
     * 
     * @param Visit $visit Прием
     * @param array $symptoms Массив симптомов
     * @return void
     */
    public function createSymptoms(Visit $visit, array $symptoms): void
    {
        try {
            DB::beginTransaction();
            
            foreach ($symptoms as $symptomData) {
                if (!empty(trim($symptomData))) {
                    $this->createSymptom($visit, $symptomData);
                }
            }

            DB::commit();

            Log::info('Симптомы созданы для приема', [
                'visit_id' => $visit->id,
                'symptoms_count' => count(array_filter($symptoms))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании симптомов', [
                'visit_id' => $visit->id,
                'symptoms' => $symptoms,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Обновить симптомы для приема
     * 
     * @param Visit $visit Прием
     * @param array $symptoms Массив симптомов
     * @return void
     */
    public function updateSymptoms(Visit $visit, array $symptoms): void
    {
        try {
            DB::beginTransaction();
            
            // Удаляем все старые симптомы
            $visit->symptoms()->delete();
            
            // Создаем новые симптомы
            $this->createSymptoms($visit, $symptoms);

            DB::commit();

            Log::info('Симптомы обновлены для приема', [
                'visit_id' => $visit->id,
                'symptoms_count' => count(array_filter($symptoms))
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении симптомов', [
                'visit_id' => $visit->id,
                'symptoms' => $symptoms,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Создать конкретный симптом
     * 
     * @param Visit $visit Прием
     * @param string|int $symptomData Данные симптома
     * @return Symptom Созданный симптом
     */
    protected function createSymptom(Visit $visit, $symptomData): Symptom
    {
        if (is_numeric($symptomData)) {
            // Оптимизация: используем select для выбора только нужных полей
            $dictionarySymptom = DictionarySymptom::select(['id', 'name'])->find($symptomData);
            if ($dictionarySymptom) {
                return Symptom::create([
                    'visit_id' => $visit->id,
                    'dictionary_symptom_id' => $symptomData,
                    'custom_symptom' => null,
                    'notes' => null
                ]);
            }
        } else {
            // Создаем кастомный симптом
            return Symptom::create([
                'visit_id' => $visit->id,
                'dictionary_symptom_id' => null,
                'custom_symptom' => $symptomData,
                'notes' => null
            ]);
        }

        throw new \InvalidArgumentException('Некорректные данные симптома: ' . $symptomData);
    }

    /**
     * Получить симптомы приема с форматированием для отображения
     * 
     * @param Visit $visit Прием
     * @param int $limit Лимит для отображения
     * @return array Форматированные симптомы
     */
    public function getFormattedSymptoms(Visit $visit, int $limit = 3): array
    {
        $symptoms = $visit->symptoms;
        $limitedSymptoms = $symptoms->take($limit);
        $symptomsCount = $symptoms->count();
        
        $symptomNames = $limitedSymptoms->map(function($symptom) {
            return $symptom->getName();
        })->toArray();
        
        if ($symptomsCount > $limit) {
            $symptomNames[] = '...';
        }
        
        return [
            'display' => $symptomNames,
            'total_count' => $symptomsCount,
            'limited_count' => $limitedSymptoms->count()
        ];
    }

    /**
     * Получить детальную информацию о симптомах приема
     * 
     * @param Visit $visit Прием
     * @return array Детальная информация
     */
    public function getSymptomsDetails(Visit $visit): array
    {
        // Оптимизация: используем индексы на visit_id и select для выбора нужных полей
        $symptoms = $visit->symptoms()
            ->select(['id', 'visit_id', 'dictionary_symptom_id', 'custom_symptom', 'notes'])
            ->with(['dictionarySymptom:id,name'])
            ->get();
        
        $details = [
            'dictionary_symptoms' => [],
            'custom_symptoms' => [],
            'total_count' => $symptoms->count()
        ];

        foreach ($symptoms as $symptom) {
            if ($symptom->dictionary_symptom_id) {
                $details['dictionary_symptoms'][] = [
                    'id' => $symptom->dictionary_symptom_id,
                    'name' => $symptom->dictionarySymptom->name ?? 'Неизвестный симптом',
                    'notes' => $symptom->notes
                ];
            } else {
                $details['custom_symptoms'][] = [
                    'id' => $symptom->custom_symptom,
                    'name' => $symptom->custom_symptom,
                    'notes' => $symptom->notes
                ];
            }
        }

        return $details;
    }

    /**
     * Проверить существование симптома в словаре
     * 
     * @param int $symptomId ID симптома
     * @return bool
     */
    public function symptomExistsInDictionary(int $symptomId): bool
    {
        // Оптимизация: используем select для выбора только нужных полей
        return DictionarySymptom::select(['id'])->where('id', $symptomId)->exists();
    }

    /**
     * Получить статистику по симптомам приема
     * 
     * @param Visit $visit Прием
     * @return array Статистика
     */
    public function getSymptomsStatistics(Visit $visit): array
    {
        $symptoms = $visit->symptoms;
        
        return [
            'total_symptoms' => $symptoms->count(),
            'dictionary_symptoms' => $symptoms->whereNotNull('dictionary_symptom_id')->count(),
            'custom_symptoms' => $symptoms->whereNotNull('custom_symptom')->count(),
            'symptoms_with_notes' => $symptoms->whereNotNull('notes')->count()
        ];
    }

    /**
     * Поиск симптомов по названию
     * 
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchSymptoms(string $query, int $limit = 10)
    {
        // Оптимизация: используем select для выбора только нужных полей
        return DictionarySymptom::select(['id', 'name'])
            ->where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Получить все симптомы для приема
     * 
     * @param Visit $visit Прием
     * @return \Illuminate\Database\Eloquent\Collection Коллекция симптомов
     */
    public function getVisitSymptoms(Visit $visit)
    {
        // Оптимизация: используем индексы на visit_id и select для выбора нужных полей
        return $visit->symptoms()
            ->select(['id', 'visit_id', 'dictionary_symptom_id', 'custom_symptom', 'notes'])
            ->with(['dictionarySymptom:id,name'])
            ->get();
    }

    /**
     * Удалить все симптомы для приема
     * 
     * @param Visit $visit Прием
     * @return void
     */
    public function deleteVisitSymptoms(Visit $visit): void
    {
        try {
            DB::beginTransaction();
            
            $symptomsCount = $visit->symptoms()->count();
            $visit->symptoms()->delete();
            
            DB::commit();
            
            Log::info('Симптомы удалены для приема', [
                'visit_id' => $visit->id,
                'deleted_count' => $symptomsCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении симптомов', [
                'visit_id' => $visit->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить статистику по симптомам
     * 
     * @param Visit $visit Прием
     * @return array Статистика
     */
    public function getSymptomStatistics(Visit $visit): array
    {
        $symptoms = $this->getVisitSymptoms($visit);
        
        $dictionarySymptoms = $symptoms->whereNotNull('dictionary_symptom_id');
        $customSymptoms = $symptoms->whereNotNull('custom_symptom');
        
        return [
            'total' => $symptoms->count(),
            'dictionary' => $dictionarySymptoms->count(),
            'custom' => $customSymptoms->count(),
            'with_notes' => $symptoms->whereNotNull('notes')->count()
        ];
    }
}
