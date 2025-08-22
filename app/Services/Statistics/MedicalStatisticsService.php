<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Vaccination;
use App\Models\LabTest;
use Carbon\Carbon;

class MedicalStatisticsService
{
    public function getDiagnosesData($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $diagnosesData = Visit::select(['id', 'starts_at'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['diagnoses:id,visit_id,dictionary_diagnosis_id', 'diagnoses.dictionaryDiagnosis:id,name'])
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->filter(function($diagnosis) {
                // Исключаем диагнозы без названия
                return $diagnosis->getName() && trim($diagnosis->getName()) !== '';
            })
            ->groupBy(function($diagnosis) {
                return $diagnosis->getName();
            })
            ->map->count()
            ->sortByDesc(function($count) {
                return $count;
            })
            ->take(10);
        
        $totalDiagnoses = $diagnosesData->sum();
        
        return $diagnosesData->map(function($count) use ($totalDiagnoses) {
            return [
                'count' => $count,
                'percentage' => $totalDiagnoses > 0 ? round(($count / $totalDiagnoses) * 100, 1) : 0,
            ];
        });
    }

    public function getVaccinationsData($startDate, $endDate)
    {
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return Vaccination::select(['id', 'pet_id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['pet:id,breed_id', 'pet.breed:id,name,species_id', 'pet.breed.species:id,name'])
            ->get()
            ->groupBy(function($vaccination) {
                if ($vaccination->pet && $vaccination->pet->breed && $vaccination->pet->breed->species) {
                    return $vaccination->pet->breed->species->name;
                }
                return 'Неизвестный вид';
            })
            ->map->count()
            ->sortByDesc(function($count) {
                return $count;
            });
    }

    public function getLabTestsData($startDate, $endDate)
    {
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return LabTest::select(['id', 'lab_test_type_id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['labTestType:id,name'])
            ->get()
            ->groupBy(function($labTest) {
                return $labTest->labTestType ? $labTest->labTestType->id : 'unknown';
            })
            ->map(function($labTests, $labTestTypeId) {
                $labTestType = $labTests->first()->labTestType;
                return [
                    'labTestType' => $labTestType,
                    'name' => $labTestType ? $labTestType->name : 'Неизвестный анализ',
                    'count' => $labTests->count()
                ];
            })
            ->sortByDesc('count')
            ->take(10);
    }

    public function getLabTestsTypesCount($startDate, $endDate)
    {
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return LabTest::select(['id', 'lab_test_type_id'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['labTestType:id,name'])
            ->get()
            ->groupBy(function($labTest) {
                return $labTest->labTestType ? $labTest->labTestType->id : 'unknown';
            })
            ->count();
    }

    public function getDiagnosesCount($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        return Visit::select(['id', 'starts_at'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['diagnoses:id,visit_id,dictionary_diagnosis_id', 'diagnoses.dictionaryDiagnosis:id,name'])
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->filter(function($diagnosis) {
                // Исключаем диагнозы без названия
                return $diagnosis->getName() && trim($diagnosis->getName()) !== '';
            })
            ->groupBy(function($diagnosis) {
                return $diagnosis->getName();
            })
            ->count();
    }

    public function getTotalDiagnosesCount($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        return Visit::select(['id', 'starts_at'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['diagnoses:id,visit_id'])
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->count();
    }
}
