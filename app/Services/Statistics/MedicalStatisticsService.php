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
        $diagnosesData = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->with('diagnoses.dictionaryDiagnosis')
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->groupBy(function($diagnosis) {
                return $diagnosis->getName() ?: 'Неизвестный диагноз';
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
        return Vaccination::whereBetween('created_at', [$startDate, $endDate])
            ->with('pet.breed.species')
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
        return LabTest::whereBetween('created_at', [$startDate, $endDate])
            ->with('labTestType')
            ->get()
            ->groupBy(function($labTest) {
                return $labTest->labTestType ? $labTest->labTestType->name : 'Неизвестный анализ';
            })
            ->map->count()
            ->sortByDesc(function($count) {
                return $count;
            })
            ->take(10);
    }

    public function getDiagnosesCount($startDate, $endDate)
    {
        return Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->with('diagnoses.dictionaryDiagnosis')
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->groupBy(function($diagnosis) {
                return $diagnosis->getName() ?: 'Неизвестный диагноз';
            })
            ->count();
    }

    public function getTotalDiagnosesCount($startDate, $endDate)
    {
        return Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->with('diagnoses')
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->count();
    }
}
