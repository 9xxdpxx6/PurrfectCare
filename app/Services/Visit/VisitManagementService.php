<?php

namespace App\Services\Visit;

use App\Models\Visit;
use App\Models\User;
use App\Models\Pet;
use App\Models\Schedule;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitManagementService
{
    protected $symptomService;
    protected $diagnosisService;
    protected $timeCalculationService;
    protected $dateTimeProcessingService;

    public function __construct(
        VisitSymptomService $symptomService,
        VisitDiagnosisService $diagnosisService,
        VisitTimeCalculationService $timeCalculationService,
        VisitDateTimeProcessingService $dateTimeProcessingService
    ) {
        $this->symptomService = $symptomService;
        $this->diagnosisService = $diagnosisService;
        $this->timeCalculationService = $timeCalculationService;
        $this->dateTimeProcessingService = $dateTimeProcessingService;
    }

    /**
     * Создать прием
     * 
     * @param array $validated Валидированные данные
     * @param Request $request Запрос
     * @return Visit Созданный прием
     */
    public function createVisit(array $validated, Request $request): Visit
    {
        try {
            DB::beginTransaction();

            // Обработка полей даты и времени
            $this->dateTimeProcessingService->processDateTimeFields($request);
            
            // Добавляем starts_at из request в данные для создания
            if ($request->has('starts_at')) {
                $validated['starts_at'] = $request->starts_at;
            }
            
            // Создаем прием
            $visit = Visit::create($validated);

            // Обрабатываем симптомы
            if ($request->has('symptoms') && is_array($request->symptoms)) {
                $this->symptomService->createSymptoms($visit, $request->symptoms);
            }

            // Обрабатываем диагнозы
            if ($request->has('diagnoses') && is_array($request->diagnoses)) {
                $this->diagnosisService->createDiagnoses($visit, $request->diagnoses);
            }

            DB::commit();

            Log::info('Прием успешно создан', [
                'visit_id' => $visit->id,
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id,
                'schedule_id' => $visit->schedule_id
            ]);

            return $visit;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании приема', [
                'validated_data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Обновить прием
     * 
     * @param int $id ID приема
     * @param array $validated Валидированные данные
     * @param Request $request Запрос
     * @return Visit Обновленный прием
     */
    public function updateVisit(int $id, array $validated, Request $request): Visit
    {
        try {
            DB::beginTransaction();

            // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
            $visit = Visit::select([
                    'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id',
                    'complaints', 'notes', 'created_at', 'updated_at'
                ])
                ->findOrFail($id);

            // Обработка полей даты и времени
            $this->dateTimeProcessingService->processDateTimeFields($request);
            
            // Обновляем прием
            $visit->update($validated);

            // Обновляем симптомы
            if ($request->has('symptoms')) {
                $this->symptomService->updateSymptoms($visit, $request->symptoms);
            }

            // Обновляем диагнозы
            if ($request->has('diagnoses')) {
                $this->diagnosisService->updateDiagnoses($visit, $request->diagnoses);
            }

            DB::commit();

            Log::info('Прием успешно обновлен', [
                'visit_id' => $visit->id,
                'client_id' => $visit->client_id,
                'pet_id' => $visit->pet_id
            ]);

            return $visit;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении приема', [
                'visit_id' => $id,
                'validated_data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Удалить прием
     * 
     * @param int $id ID приема
     * @return bool Результат удаления
     */
    public function deleteVisit(int $id): bool
    {
        try {
            DB::beginTransaction();

            // Оптимизация: используем select для выбора только нужных полей
            $visit = Visit::select(['id'])->findOrFail($id);
            
            // Симптомы и диагнозы удалятся каскадно
            $visit->delete();

            DB::commit();

            Log::info('Прием успешно удален', [
                'visit_id' => $id
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении приема', [
                'visit_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Получить полную информацию о приеме
     * 
     * @param int $id ID приема
     * @return Visit Прием с загруженными связями
     */
    public function getVisitWithDetails(int $id): Visit
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        return Visit::select([
                'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id',
                'complaints', 'notes', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone',
                'pet:id,name,breed_id,client_id',
                'schedule:id,veterinarian_id,branch_id,shift_starts_at,shift_ends_at',
                'schedule.veterinarian:id,name,email',
                'status:id,name',
                'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom',
                'symptoms.dictionarySymptom:id,name',
                'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis,treatment_plan',
                'diagnoses.dictionaryDiagnosis:id,name',
                'orders:id,client_id,pet_id,status_id,total,is_paid'
            ])
            ->findOrFail($id);
    }

    /**
     * Получить доступное время для расписания
     * 
     * @param int $scheduleId ID расписания
     * @return array Доступное время
     */
    public function getAvailableTime(int $scheduleId): array
    {
        return $this->timeCalculationService->getAvailableTime($scheduleId);
    }

    /**
     * Получить статистику по приему
     * 
     * @param Visit $visit Прием
     * @return array Статистика
     */
    public function getVisitStatistics(Visit $visit): array
    {
        return [
            'symptoms' => $this->symptomService->getSymptomsStatistics($visit),
            'diagnoses' => $this->diagnosisService->getDiagnosesStatistics($visit),
            'time' => [
                'start_time' => $visit->starts_at ? Carbon::parse($visit->starts_at)->format('H:i') : null,
                'duration' => 30, // Стандартная длительность
                'is_within_schedule' => $visit->schedule ? 
                    $this->dateTimeProcessingService->isTimeWithinSchedule(
                        $visit->schedule, 
                        Carbon::parse($visit->starts_at)->format('H:i')
                    ) : false
            ]
        ];
    }

    /**
     * Получить форматированные данные для отображения
     * 
     * @param Visit $visit Прием
     * @return array Форматированные данные
     */
    public function getFormattedDisplayData(Visit $visit): array
    {
        return [
            'symptoms' => $this->symptomService->getFormattedSymptoms($visit, 3),
            'diagnoses' => $this->diagnosisService->getFormattedDiagnoses($visit, 3)
        ];
    }

    /**
     * Получить детальную информацию о симптомах и диагнозах
     * 
     * @param Visit $visit Прием
     * @return array Детальная информация
     */
    public function getDetailedInformation(Visit $visit): array
    {
        return [
            'symptoms' => $this->symptomService->getSymptomsDetails($visit),
            'diagnoses' => $this->diagnosisService->getDiagnosesDetails($visit)
        ];
    }

    /**
     * Проверить конфликты времени
     * 
     * @param int $scheduleId ID расписания
     * @param string $startTime Время начала
     * @param int $duration Длительность в минутах
     * @param int|null $excludeVisitId ID приема для исключения
     * @return array Конфликты времени
     */
    public function checkTimeConflicts(int $scheduleId, string $startTime, int $duration, ?int $excludeVisitId = null): array
    {
        return $this->dateTimeProcessingService->checkTimeConflicts($scheduleId, $startTime, $duration, $excludeVisitId);
    }

    /**
     * Получить рекомендуемое время для приема
     * 
     * @param int $scheduleId ID расписания
     * @param int $duration Длительность приема в минутах
     * @return array Рекомендуемое время
     */
    public function getRecommendedTime(int $scheduleId, int $duration = 30): array
    {
        return $this->timeCalculationService->getRecommendedTime($scheduleId, $duration);
    }

    /**
     * Поиск симптомов
     * 
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchSymptoms(string $query, int $limit = 10)
    {
        return $this->symptomService->searchSymptoms($query, $limit);
    }

    /**
     * Поиск диагнозов
     * 
     * @param string $query Поисковый запрос
     * @param int $limit Лимит результатов
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchDiagnoses(string $query, int $limit = 10)
    {
        return $this->diagnosisService->searchDiagnoses($query, $limit);
    }

    /**
     * Обновить план лечения для диагноза
     * 
     * @param int $diagnosisId ID диагноза
     * @param string $treatmentPlan План лечения
     * @return \App\Models\Diagnosis Обновленный диагноз
     */
    public function updateTreatmentPlan(int $diagnosisId, string $treatmentPlan)
    {
        return $this->diagnosisService->updateTreatmentPlan($diagnosisId, $treatmentPlan);
    }

    /**
     * Получить статистику по времени приемов
     * 
     * @param int $scheduleId ID расписания
     * @return array Статистика
     */
    public function getTimeStatistics(int $scheduleId): array
    {
        return $this->timeCalculationService->getTimeStatistics($scheduleId);
    }
}
