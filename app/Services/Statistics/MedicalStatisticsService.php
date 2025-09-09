<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Vaccination;
use App\Models\LabTest;
use Carbon\Carbon;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

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

    /**
     * Экспорт данных по диагнозам
     */
    public function exportDiagnosesData($startDate, $endDate, $format = 'excel')
    {
        try {
            $diagnosesData = $this->getDiagnosesData($startDate, $endDate);
            $diagnosesCount = $this->getDiagnosesCount($startDate, $endDate);
            $totalDiagnosesCount = $this->getTotalDiagnosesCount($startDate, $endDate);
            
            // Форматируем основные показатели
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество диагнозов',
                    'Значение' => $totalDiagnosesCount,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Уникальных диагнозов',
                    'Значение' => $diagnosesCount,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Среднее количество диагнозов на визит',
                    'Значение' => $totalDiagnosesCount > 0 ? number_format($totalDiagnosesCount / $diagnosesCount, 2, ',', ' ') : '0,00',
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по диагнозам
            $formattedDiagnosesData = [];
            foreach ($diagnosesData as $diagnosis => $data) {
                $formattedDiagnosesData[] = [
                    'Диагноз' => $diagnosis,
                    'Количество случаев' => $data['count'],
                    'Процент от общих диагнозов' => $data['percentage'] . '%'
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedDiagnosesData);
            
            $filename = app(ExportService::class)->generateFilename('diagnoses_data', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте данных по диагнозам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт данных по вакцинациям
     */
    public function exportVaccinationsData($startDate, $endDate, $format = 'excel')
    {
        try {
            $vaccinationsData = $this->getVaccinationsData($startDate, $endDate);
            $labTestsData = $this->getLabTestsData($startDate, $endDate);
            $labTestsTypesCount = $this->getLabTestsTypesCount($startDate, $endDate);
            
            // Форматируем основные показатели
            $totalVaccinations = $vaccinationsData->sum();
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество вакцинаций',
                    'Значение' => $totalVaccinations,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество видов животных',
                    'Значение' => $vaccinationsData->count(),
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество типов анализов',
                    'Значение' => $labTestsTypesCount,
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по вакцинациям по видам
            $formattedVaccinationsData = [];
            foreach ($vaccinationsData as $species => $count) {
                $formattedVaccinationsData[] = [
                    'Вид животного' => $species,
                    'Количество вакцинаций' => $count,
                    'Процент от общих вакцинаций' => $totalVaccinations > 0 ? number_format(($count / $totalVaccinations) * 100, 2) . '%' : '0%'
                ];
            }
            
            // Форматируем данные по анализам
            $formattedLabTestsData = [];
            foreach ($labTestsData as $labTest) {
                $formattedLabTestsData[] = [
                    'Тип анализа' => $labTest['name'],
                    'Количество анализов' => $labTest['count']
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedVaccinationsData, $formattedLabTestsData);
            
            $filename = app(ExportService::class)->generateFilename('vaccinations_data', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте данных по вакцинациям', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт медицинских данных (диагнозы + вакцинации + анализы)
     */
    public function exportMedicalData($startDate, $endDate, $format = 'excel')
    {
        try {
            $diagnosesData = $this->getDiagnosesData($startDate, $endDate);
            $vaccinationsData = $this->getVaccinationsData($startDate, $endDate);
            $labTestsData = $this->getLabTestsData($startDate, $endDate);
            $diagnosesCount = $this->getDiagnosesCount($startDate, $endDate);
            $totalDiagnosesCount = $this->getTotalDiagnosesCount($startDate, $endDate);
            
            // Форматируем основные показатели
            $totalVaccinations = $vaccinationsData->sum();
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество диагнозов',
                    'Значение' => $totalDiagnosesCount,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Уникальных диагнозов',
                    'Значение' => $diagnosesCount,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество вакцинаций',
                    'Значение' => $totalVaccinations,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество видов животных',
                    'Значение' => $vaccinationsData->count(),
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по диагнозам
            $formattedDiagnosesData = [];
            foreach ($diagnosesData as $diagnosis => $data) {
                $formattedDiagnosesData[] = [
                    'Диагноз' => $diagnosis,
                    'Количество случаев' => $data['count'],
                    'Процент от общих диагнозов' => $data['percentage'] . '%'
                ];
            }
            
            // Форматируем данные по вакцинациям
            $formattedVaccinationsData = [];
            foreach ($vaccinationsData as $species => $count) {
                $formattedVaccinationsData[] = [
                    'Вид животного' => $species,
                    'Количество вакцинаций' => $count,
                    'Процент от общих вакцинаций' => $totalVaccinations > 0 ? number_format(($count / $totalVaccinations) * 100, 2) . '%' : '0%'
                ];
            }
            
            // Форматируем данные по анализам
            $formattedLabTestsData = [];
            foreach ($labTestsData as $labTest) {
                $formattedLabTestsData[] = [
                    'Тип анализа' => $labTest['name'],
                    'Количество анализов' => $labTest['count']
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedDiagnosesData, $formattedVaccinationsData, $formattedLabTestsData);
            
            $filename = app(ExportService::class)->generateFilename('medical_data', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте медицинских данных', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
