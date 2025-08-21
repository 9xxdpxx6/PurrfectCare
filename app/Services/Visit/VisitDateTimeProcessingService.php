<?php

namespace App\Services\Visit;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisitDateTimeProcessingService
{
    /**
     * Обработка полей даты и времени для создания datetime полей
     * 
     * @param Request $request Запрос
     * @return void
     */
    public function processDateTimeFields(Request $request): void
    {
        if ($request->has('schedule_id') && $request->has('visit_time')) {
            try {
                // Оптимизация: используем select для выбора только нужных полей и индексы на id
                $schedule = Schedule::select(['id', 'shift_starts_at', 'shift_ends_at'])->find($request->schedule_id);
                if ($schedule) {
                    $fullDateTime = $this->createFullDateTime($schedule, $request->visit_time);
                    
                    // Отладочная информация
                    Log::info('Processing datetime fields', [
                        'original_visit_time' => $request->visit_time,
                        'rounded_time' => $this->roundTimeToHalfHour($request->visit_time),
                        'schedule_date' => Carbon::parse($schedule->shift_starts_at)->format('Y-m-d'),
                        'full_datetime' => $fullDateTime,
                        'schedule_shift_start' => $schedule->shift_starts_at,
                        'schedule_shift_end' => $schedule->shift_ends_at
                    ]);
                    
                    $request->merge([
                        'starts_at' => $fullDateTime
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing datetime fields', [
                    'error' => $e->getMessage(),
                    'visit_time' => $request->visit_time ?? 'not set',
                    'schedule_id' => $request->schedule_id ?? 'not set'
                ]);
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }
    }

    /**
     * Создать полную дату и время для приема
     * 
     * @param Schedule $schedule Расписание
     * @param string $visitTime Время приема
     * @return string Полная дата и время
     */
    protected function createFullDateTime(Schedule $schedule, string $visitTime): string
    {
        $scheduleDate = Carbon::parse($schedule->shift_starts_at);
        $roundedTime = $this->roundTimeToHalfHour($visitTime);
        
        // Объединяем дату из расписания с округленным временем приёма
        return $scheduleDate->format('Y-m-d') . ' ' . $roundedTime;
    }

    /**
     * Округлить время до начала получасового интервала
     * 
     * @param string $time Время для округления (формат H:i)
     * @return string Округленное время
     */
    protected function roundTimeToHalfHour(string $time): string
    {
        $timeParts = explode(':', $time);
        if (count($timeParts) !== 2) {
            throw new \InvalidArgumentException('Неверный формат времени: ' . $time);
        }

        $hour = (int)$timeParts[0];
        $minute = (int)$timeParts[1];
        
        // Округляем до начала получасового интервала
        if ($minute >= 30) {
            $roundedMinute = 30;
        } else {
            $roundedMinute = 0;
        }
        
        // Форматируем округленное время
        return sprintf('%02d:%02d', $hour, $roundedMinute);
    }

    /**
     * Проверить, что время приема находится в пределах смены
     * 
     * @param Schedule $schedule Расписание
     * @param string $visitTime Время приема
     * @return bool Валидно ли время
     */
    public function isTimeWithinSchedule(Schedule $schedule, string $visitTime): bool
    {
        try {
            $scheduleStart = Carbon::parse($schedule->shift_starts_at);
            $scheduleEnd = Carbon::parse($schedule->shift_ends_at);
            $visitDateTime = Carbon::createFromFormat('H:i', $visitTime);
            
            // Создаем полную дату для времени приема
            $fullVisitDateTime = $scheduleStart->copy()->setTime(
                $visitDateTime->hour,
                $visitDateTime->minute,
                0
            );
            
            return $fullVisitDateTime >= $scheduleStart && $fullVisitDateTime < $scheduleEnd;
            
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке времени в пределах смены', [
                'schedule_id' => $schedule->id,
                'visit_time' => $visitTime,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Получить оптимальное время для приема
     * 
     * @param Schedule $schedule Расписание
     * @param int $duration Длительность приема в минутах
     * @return string Оптимальное время
     */
    public function getOptimalTime(Schedule $schedule, int $duration = 30): string
    {
        $scheduleStart = Carbon::parse($schedule->shift_starts_at);
        $scheduleEnd = Carbon::parse($schedule->shift_ends_at);
        
        // Начинаем с начала смены, округленного до получаса
        $optimalTime = $this->roundToNextHalfHour($scheduleStart);
        
        // Проверяем, что прием поместится в смену
        $endTime = $optimalTime->copy()->addMinutes($duration);
        if ($endTime > $scheduleEnd) {
            // Если не помещается, ищем другое время
            $optimalTime = $scheduleStart->copy()->addMinutes(30);
        }
        
        return $optimalTime->format('H:i');
    }

    /**
     * Округлить время до следующего получаса
     * 
     * @param Carbon $time Время для округления
     * @return Carbon Округленное время
     */
    protected function roundToNextHalfHour(Carbon $time): Carbon
    {
        $currentTime = $time->copy();
        $minutes = $currentTime->minute;
        
        if ($minutes > 0 && $minutes < 30) {
            $currentTime->setMinute(30);
            $currentTime->setSecond(0);
        } elseif ($minutes > 30) {
            $currentTime->addHour();
            $currentTime->setMinute(0);
            $currentTime->setSecond(0);
        } else {
            // Если уже кратно получасу (0 или 30), оставляем как есть
            $currentTime->setSecond(0);
        }
        
        return $currentTime;
    }

    /**
     * Форматировать время для отображения
     * 
     * @param string $time Время (формат H:i)
     * @return string Отформатированное время
     */
    public function formatTimeForDisplay(string $time): string
    {
        try {
            $carbonTime = Carbon::createFromFormat('H:i', $time);
            return $carbonTime->format('H:i');
        } catch (\Exception $e) {
            Log::error('Ошибка при форматировании времени', [
                'time' => $time,
                'error' => $e->getMessage()
            ]);
            return $time;
        }
    }

    /**
     * Получить интервал времени для приема
     * 
     * @param string $startTime Время начала
     * @param int $duration Длительность в минутах
     * @return array Интервал времени
     */
    public function getTimeInterval(string $startTime, int $duration): array
    {
        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = $start->copy()->addMinutes($duration);
            
            return [
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'duration' => $duration,
                'formatted' => $start->format('H:i') . ' - ' . $end->format('H:i')
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при получении интервала времени', [
                'start_time' => $startTime,
                'duration' => $duration,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Проверить конфликты времени
     * 
     * @param int $scheduleId ID расписания
     * @param string $startTime Время начала
     * @param int $duration Длительность в минутах
     * @param int|null $excludeVisitId ID приема для исключения (при обновлении)
     * @return array Конфликты времени
     */
    public function checkTimeConflicts(int $scheduleId, string $startTime, int $duration, ?int $excludeVisitId = null): array
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->copy()->addMinutes($duration);
        
        // Оптимизация: используем индексы на schedule_id и starts_at, select для выбора нужных полей
        $query = \App\Models\Visit::select(['id', 'starts_at'])
            ->where('schedule_id', $scheduleId)
            ->where(function($q) use ($start, $end) {
                $q->where(function($subQ) use ($start, $end) {
                    $subQ->where('starts_at', '<', $end)
                         ->whereRaw('DATE_ADD(starts_at, INTERVAL 30 MINUTE)', '>', $start);
                });
            });
        
        if ($excludeVisitId) {
            $query->where('id', '!=', $excludeVisitId);
        }
        
        $conflictingVisits = $query->get();
        
        $conflicts = [];
        foreach ($conflictingVisits as $visit) {
            $visitStart = Carbon::parse($visit->starts_at);
            $visitEnd = $visitStart->copy()->addMinutes(30); // Предполагаем стандартную длительность
            
            if ($start < $visitEnd && $end > $visitStart) {
                $conflicts[] = [
                    'visit_id' => $visit->id,
                    'visit_start' => $visitStart->format('H:i'),
                    'visit_end' => $visitEnd->format('H:i'),
                    'conflict_type' => 'overlap'
                ];
            }
        }
        
        return $conflicts;
    }
}
