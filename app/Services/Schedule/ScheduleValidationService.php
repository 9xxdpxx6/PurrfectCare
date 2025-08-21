<?php

namespace App\Services\Schedule;

use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleValidationService
{
    /**
     * Проверка логических противоречий в расписании
     * 
     * @param int $veterinarianId ID ветеринара
     * @param string $shiftStartsAt Время начала смены
     * @param string $shiftEndsAt Время окончания смены
     * @param int|null $excludeScheduleId ID расписания для исключения (при обновлении)
     * @return array Массив с ошибками или пустой массив, если ошибок нет
     */
    public function validateScheduleConflicts(
        int $veterinarianId,
        string $shiftStartsAt,
        string $shiftEndsAt,
        ?int $excludeScheduleId = null
    ): array {
        // Оптимизация: используем индексы на veterinarian_id, shift_starts_at, shift_ends_at и select для выбора нужных полей
        $query = Schedule::select(['id', 'veterinarian_id', 'branch_id', 'shift_starts_at', 'shift_ends_at'])
                ->where('veterinarian_id', $veterinarianId)
                ->where('shift_ends_at', '>', $shiftStartsAt)
                ->where('shift_starts_at', '<', $shiftEndsAt);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $conflictingSchedules = $query->with(['branch:id,name'])->get();

        if ($conflictingSchedules->isEmpty()) {
            return [];
        }

        return $conflictingSchedules->map(function($schedule) {
            return sprintf(
                'У ветеринара уже есть смена %s - %s в филиале "%s"',
                Carbon::parse($schedule->shift_starts_at)->format('d.m.Y H:i'),
                Carbon::parse($schedule->shift_ends_at)->format('H:i'),
                $schedule->branch->name
            );
        })->toArray();
    }

    /**
     * Проверить существование расписания на конкретную дату
     * 
     * @param int $veterinarianId ID ветеринара
     * @param string $date Дата в формате Y-m-d
     * @return Schedule|null Существующее расписание или null
     */
    public function checkExistingSchedule(int $veterinarianId, string $date): ?Schedule
    {
        // Оптимизация: используем индексы на veterinarian_id и shift_starts_at, select для выбора нужных полей
        return Schedule::select(['id', 'veterinarian_id', 'branch_id', 'shift_starts_at', 'shift_ends_at'])
            ->where('veterinarian_id', $veterinarianId)
            ->whereDate('shift_starts_at', $date)
            ->first();
    }

    /**
     * Проверить конфликты для недельного расписания
     * 
     * @param int $veterinarianId ID ветеринара
     * @param array $days Массив дней недели
     * @param string $weekStart Начало недели
     * @param array $timeSlots Массив временных слотов для каждого дня
     * @return array Массив конфликтов по дням
     */
    public function validateWeekScheduleConflicts(
        int $veterinarianId,
        array $days,
        string $weekStart,
        array $timeSlots
    ): array {
        $conflicts = [];
        $weekStartDate = Carbon::parse($weekStart)->startOfWeek();
        
        $dayMap = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        foreach ($days as $day) {
            $dayOffset = $dayMap[$day];
            $shiftDate = $weekStartDate->copy()->addDays($dayOffset);
            
            $startTime = $timeSlots["start_time_{$day}"] ?? null;
            $endTime = $timeSlots["end_time_{$day}"] ?? null;
            
            if (!$startTime || !$endTime) {
                continue;
            }
            
            $shiftStartsAt = $shiftDate->copy()->format('Y-m-d') . ' ' . $startTime;
            $shiftEndsAt = $shiftDate->copy()->format('Y-m-d') . ' ' . $endTime;

            // Проверяем конфликты с другими расписаниями
            $conflictErrors = $this->validateScheduleConflicts(
                $veterinarianId,
                $shiftStartsAt,
                $shiftEndsAt
            );

            if (!empty($conflictErrors)) {
                $conflicts[$day] = [
                    'errors' => $conflictErrors,
                    'date' => $shiftDate->format('d.m.Y'),
                    'time' => "{$startTime} - {$endTime}"
                ];
            }
        }

        return $conflicts;
    }
}
