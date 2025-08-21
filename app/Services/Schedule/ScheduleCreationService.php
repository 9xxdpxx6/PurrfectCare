<?php

namespace App\Services\Schedule;

use App\Models\Schedule;
use App\Services\Schedule\ScheduleValidationService;
use App\Services\Schedule\DateTimeProcessingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleCreationService
{
    protected $validationService;
    protected $dateTimeService;

    public function __construct(
        ScheduleValidationService $validationService,
        DateTimeProcessingService $dateTimeService
    ) {
        $this->validationService = $validationService;
        $this->dateTimeService = $dateTimeService;
    }

    /**
     * Создать расписание на неделю
     * 
     * @param array $validated Валидированные данные
     * @param array $requestData Данные запроса
     * @return array Результат создания с информацией об успехе/ошибках
     */
    public function createWeekSchedule(array $validated, array $requestData): array
    {
        $weekStart = Carbon::parse($requestData['week_start'])->startOfWeek();
        $veterinarianId = $validated['veterinarian_id'];
        $branchId = $validated['branch_id'];
        $days = $requestData['days'];

        $schedulesToCreate = [];
        $conflicts = [];
        $existingSchedules = [];

        // Проверяем каждый день на конфликты и существующие расписания
        foreach ($days as $day) {
            $startTime = $requestData["start_time_{$day}"] ?? null;
            $endTime = $requestData["end_time_{$day}"] ?? null;
            
            if (!$startTime || !$endTime) {
                continue;
            }

            $dateTimeData = $this->dateTimeService->createWeekDayDateTime(
                $requestData['week_start'], 
                $day, 
                $startTime, 
                $endTime
            );

            // Проверяем существующие расписания на этот день
            $existingSchedule = $this->validationService->checkExistingSchedule(
                $veterinarianId, 
                Carbon::parse($dateTimeData['shift_starts_at'])->format('Y-m-d')
            );

            if ($existingSchedule) {
                $dayNames = $this->dateTimeService->getDayNames();
                $existingSchedules[$day] = [
                    'schedule' => $existingSchedule,
                    'day_name' => $dayNames[$day],
                    'date' => $dateTimeData['date']
                ];
                continue;
            }

            // Проверяем конфликты с другими расписаниями
            $conflictErrors = $this->validationService->validateScheduleConflicts(
                $veterinarianId,
                $dateTimeData['shift_starts_at'],
                $dateTimeData['shift_ends_at']
            );

            if (!empty($conflictErrors)) {
                $dayNames = $this->dateTimeService->getDayNames();
                $conflicts[$day] = [
                    'errors' => $conflictErrors,
                    'day_name' => $dayNames[$day],
                    'date' => $dateTimeData['date'],
                    'time' => "{$startTime} - {$endTime}"
                ];
                continue;
            }

            // Добавляем в список для создания
            $schedulesToCreate[] = [
                'veterinarian_id' => $veterinarianId,
                'branch_id' => $branchId,
                'shift_starts_at' => $dateTimeData['shift_starts_at'],
                'shift_ends_at' => $dateTimeData['shift_ends_at'],
                'day' => $day,
                'date' => $dateTimeData['date']
            ];
        }

        // Если есть конфликты, возвращаем ошибки
        if (!empty($conflicts)) {
            return [
                'success' => false,
                'type' => 'conflicts',
                'data' => $conflicts
            ];
        }

        // Если нет расписаний для создания, возвращаем предупреждение
        if (empty($schedulesToCreate)) {
            return [
                'success' => false,
                'type' => 'existing_schedules',
                'data' => $existingSchedules
            ];
        }

        // Создаём расписания в транзакции
        try {
            DB::beginTransaction();

            $createdSchedules = [];
            foreach ($schedulesToCreate as $scheduleData) {
                $schedule = Schedule::create([
                    'veterinarian_id' => $scheduleData['veterinarian_id'],
                    'branch_id' => $scheduleData['branch_id'],
                    'shift_starts_at' => $scheduleData['shift_starts_at'],
                    'shift_ends_at' => $scheduleData['shift_ends_at'],
                ]);
                
                $createdSchedules[] = $schedule;
            }

            DB::commit();

            return [
                'success' => true,
                'created_schedules' => $createdSchedules,
                'total_days' => count($days),
                'existing_schedules' => $existingSchedules
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании расписания на неделю', [
                'error' => $e->getMessage(),
                'veterinarian_id' => $veterinarianId,
                'branch_id' => $branchId,
                'week_start' => $requestData['week_start'],
                'days' => $days
            ]);

            return [
                'success' => false,
                'type' => 'error',
                'message' => 'Произошла ошибка при создании расписания. Попробуйте еще раз.'
            ];
        }
    }

    /**
     * Создать одиночное расписание
     * 
     * @param array $validated Валидированные данные
     * @return Schedule Созданное расписание
     */
    public function createSingleSchedule(array $validated): Schedule
    {
        return Schedule::create($validated);
    }

    /**
     * Обновить расписание
     * 
     * @param int $id ID расписания
     * @param array $validated Валидированные данные
     * @return Schedule Обновленное расписание
     */
    public function updateSchedule(int $id, array $validated): Schedule
    {
        // Оптимизация: используем select для выбора только нужных полей
        $schedule = Schedule::select([
                'id', 'veterinarian_id', 'branch_id', 'shift_starts_at', 'shift_ends_at',
                'created_at', 'updated_at'
            ])
            ->findOrFail($id);
        $schedule->update($validated);
        return $schedule;
    }

    /**
     * Удалить расписание
     * 
     * @param int $id ID расписания
     * @return bool Результат удаления
     */
    public function deleteSchedule(int $id): bool
    {
        // Оптимизация: используем select для выбора только нужных полей
        $schedule = Schedule::select(['id'])->findOrFail($id);
        return $schedule->delete();
    }
}
