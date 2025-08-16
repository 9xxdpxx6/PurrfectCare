<?php

namespace App\Services\Visit;

use App\Models\Visit;
use App\Models\Diagnosis;
use App\Models\DictionaryDiagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisitDiagnosisService
{
    /**
     * Создать диагнозы для приема
     * 
     * @param Visit $visit Прием
     * @param array $diagnoses Массив диагнозов
     * @return void
     */
    public function createDiagnoses(Visit $visit, array $diagnoses): void
    {
        try {
            foreach ($diagnoses as $diagnosisData) {
                if (!empty(trim($diagnosisData))) {
                    $this->createDiagnosis($visit, $diagnosisData);
                }
            }

            Log::info('Диагнозы созданы для приема', [
                'visit_id' => $visit->id,
                'diagnoses_count' => count(array_filter($diagnoses))
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при создании диагнозов', [
                'visit_id' => $visit->id,
                'diagnoses' => $diagnoses,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Обновить диагнозы для приема
     * 
     * @param Visit $visit Прием
     * @param array $diagnoses Массив диагнозов
     * @return void
     */
    public function updateDiagnoses(Visit $visit, array $diagnoses): void
    {
        try {
            // Удаляем все старые диагнозы
            $visit->diagnoses()->delete();
            
            // Создаем новые диагнозы
            $this->createDiagnoses($visit, $diagnoses);

            Log::info('Диагнозы обновлены для приема', [
                'visit_id' => $visit->id,
                'diagnoses_count' => count(array_filter($diagnoses))
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении диагнозов', [
                'visit_id' => $visit->id,
                'diagnoses' => $diagnoses,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Создать конкретный диагноз
     * 
     * @param Visit $visit Прием
     * @param string|int $diagnosisData Данные диагноза
     * @return Diagnosis Созданный диагноз
     */
    protected function createDiagnosis(Visit $visit, $diagnosisData): Diagnosis
    {
        if (is_numeric($diagnosisData)) {
            // Проверяем существование диагноза в словаре
            $dictionaryDiagnosis = DictionaryDiagnosis::find($diagnosisData);
            if ($dictionaryDiagnosis) {
                return Diagnosis::create([
                    'visit_id' => $visit->id,
                    'dictionary_diagnosis_id' => $diagnosisData,
                    'custom_diagnosis' => null,
                    'treatment_plan' => null
                ]);
            }
        } else {
            // Создаем кастомный диагноз
            return Diagnosis::create([
                'visit_id' => $visit->id,
                'dictionary_diagnosis_id' => null,
                'custom_diagnosis' => $diagnosisData,
                'treatment_plan' => null
            ]);
        }

        throw new \InvalidArgumentException('Некорректные данные диагноза: ' . $diagnosisData);
    }

    /**
     * Получить диагнозы приема с форматированием для отображения
     * 
     * @param Visit $visit Прием
     * @param int $limit Лимит для отображения
     * @return array Форматированные диагнозы
     */
    public function getFormattedDiagnoses(Visit $visit, int $limit = 3): array
    {
        $diagnoses = $visit->diagnoses;
        $limitedDiagnoses = $diagnoses->take($limit);
        $diagnosesCount = $diagnoses->count();
        
        $diagnosisNames = $limitedDiagnoses->map(function($diagnosis) {
            return $diagnosis->getName();
        })->toArray();
        
        if ($diagnosesCount > $limit) {
            $diagnosisNames[] = '...';
        }
        
        return [
            'display' => $diagnosisNames,
            'total_count' => $diagnosesCount,
            'limited_count' => $limitedDiagnoses->count()
        ];
    }

    /**
     * Получить детальную информацию о диагнозах приема
     * 
     * @param Visit $visit Прием
     * @return array Детальная информация
     */
    public function getDiagnosesDetails(Visit $visit): array
    {
        $diagnoses = $visit->diagnoses()->with('dictionaryDiagnosis')->get();
        
        $details = [
            'dictionary_diagnoses' => [],
            'custom_diagnoses' => [],
            'total_count' => $diagnoses->count()
        ];

        foreach ($diagnoses as $diagnosis) {
            if ($diagnosis->dictionary_diagnosis_id) {
                $details['dictionary_diagnoses'][] = [
                    'id' => $diagnosis->dictionary_diagnosis_id,
                    'name' => $diagnosis->dictionaryDiagnosis->name ?? 'Неизвестный диагноз',
                    'treatment_plan' => $diagnosis->treatment_plan
                ];
            } else {
                $details['custom_diagnoses'][] = [
                    'id' => $diagnosis->custom_diagnosis,
                    'name' => $diagnosis->custom_diagnosis,
                    'treatment_plan' => $diagnosis->treatment_plan
                ];
            }
        }

        return $details;
    }

    /**
     * Проверить существование диагноза в словаре
     * 
     * @param int $diagnosisId ID диагноза
     * @return bool
     */
    public function diagnosisExistsInDictionary(int $diagnosisId): bool
    {
        return DictionaryDiagnosis::where('id', $diagnosisId)->exists();
    }

    /**
     * Получить статистику по диагнозам приема
     * 
     * @param Visit $visit Прием
     * @return array Статистика
     */
    public function getDiagnosesStatistics(Visit $visit): array
    {
        $diagnoses = $visit->diagnoses;
        
        return [
            'total_diagnoses' => $diagnoses->count(),
            'dictionary_diagnoses' => $diagnoses->whereNotNull('dictionary_diagnosis_id')->count(),
            'custom_diagnoses' => $diagnoses->whereNotNull('custom_diagnosis')->count(),
            'diagnoses_with_treatment_plan' => $diagnoses->whereNotNull('treatment_plan')->count()
        ];
    }

    /**
     * Поиск диагнозов по названию
     * 
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchDiagnoses(string $query, int $limit = 10)
    {
        return DictionaryDiagnosis::where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Обновить план лечения для диагноза
     * 
     * @param int $diagnosisId ID диагноза
     * @param string $treatmentPlan План лечения
     * @return Diagnosis Обновленный диагноз
     */
    public function updateTreatmentPlan(int $diagnosisId, string $treatmentPlan): Diagnosis
    {
        $diagnosis = Diagnosis::findOrFail($diagnosisId);
        $diagnosis->update(['treatment_plan' => $treatmentPlan]);

        Log::info('План лечения обновлен', [
            'diagnosis_id' => $diagnosisId,
            'visit_id' => $diagnosis->visit_id
        ]);

        return $diagnosis;
    }

    /**
     * Получить все диагнозы с планами лечения
     * 
     * @param Visit $visit Прием
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDiagnosesWithTreatmentPlans(Visit $visit)
    {
        return $visit->diagnoses()
            ->whereNotNull('treatment_plan')
            ->with('dictionaryDiagnosis')
            ->get();
    }
}
