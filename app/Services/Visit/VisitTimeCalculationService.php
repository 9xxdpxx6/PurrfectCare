<?php

namespace App\Services\Visit;

use App\Models\Schedule;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VisitTimeCalculationService
{
    /**
     * Получить доступное время для выбранного расписания
     * 
     * @param int $scheduleId ID расписания
     * @return array Доступное время
     */
    public function getAvailableTime(int $scheduleId): array
    {
        try {
            $schedule = Schedule::with('veterinarian')->find($scheduleId);
            if (!$schedule) {
                throw new \InvalidArgumentException('Расписание не найдено');
            }

            // Получаем все занятые времена для этого расписания
            $occupiedTimes = $this->getOccupiedTimes($scheduleId);
            
            // Генерируем доступные времена
            $availableTimes = $this->generateAvailableTimes($schedule, $occupiedTimes);
            
            // Получаем следующее доступное время
            $nextAvailableTime = $this->getNextAvailableTime($availableTimes);

            $result = [
                'schedule' => [
                    'veterinarian' => $schedule->veterinarian ? $schedule->veterinarian->name : 'Не указан',
                    'shift_start' => Carbon::parse($schedule->shift_starts_at)->format('d.m.Y H:i'),
                    'shift_end' => Carbon::parse($schedule->shift_ends_at)->format('d.m.Y H:i'),
                    'shift_starts_at' => $schedule->shift_starts_at
                ],
                'available_times' => $availableTimes,
                'occupied_times' => $occupiedTimes,
                'next_available_time' => $nextAvailableTime
            ];

            Log::info('Доступное время получено', [
                'schedule_id' => $scheduleId,
                'available_times_count' => count($availableTimes),
                'occupied_times_count' => count($occupiedTimes)
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Ошибка при получении доступного времени', [
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Получить занятые времена для расписания
     * 
     * @param int $scheduleId ID расписания
     * @return array Массив занятых времен
     */
    protected function getOccupiedTimes(int $scheduleId): array
    {
        return Visit::where('schedule_id', $scheduleId)
            ->pluck('starts_at')
            ->map(function($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();
    }

    /**
     * Сгенерировать доступные времена
     * 
     * @param Schedule $schedule Расписание
     * @param array $occupiedTimes Занятые времена
     * @return array Массив доступных времен
     */
    protected function generateAvailableTimes(Schedule $schedule, array $occupiedTimes): array
    {
        $availableTimes = [];
        $startTime = Carbon::parse($schedule->shift_starts_at);
        $endTime = Carbon::parse($schedule->shift_ends_at);
        
        // Начинаем с ближайшего получаса после начала смены
        $currentTime = $this->roundToNextHalfHour($startTime);
        
        // Генерируем времена с интервалом 30 минут
        while ($currentTime < $endTime) {
            $timeString = $currentTime->format('H:i');
            if (!in_array($timeString, $occupiedTimes)) {
                $availableTimes[] = [
                    'time' => $timeString,
                    'formatted' => $currentTime->format('d.m.Y H:i')
                ];
            }
            $currentTime->addMinutes(30);
        }
        
        return $availableTimes;
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
     * Получить следующее доступное время
     * 
     * @param array $availableTimes Доступные времена
     * @return string|null Следующее доступное время
     */
    protected function getNextAvailableTime(array $availableTimes): ?string
    {
        if (!empty($availableTimes)) {
            return $availableTimes[0]['formatted'];
        }
        return null;
    }

    /**
     * Проверить доступность конкретного времени
     * 
     * @param int $scheduleId ID расписания
     * @param string $time Время для проверки (формат H:i)
     * @return bool Доступно ли время
     */
    public function isTimeAvailable(int $scheduleId, string $time): bool
    {
        $occupiedTimes = $this->getOccupiedTimes($scheduleId);
        return !in_array($time, $occupiedTimes);
    }

    /**
     * Получить ближайшие доступные времена
     * 
     * @param int $scheduleId ID расписания
     * @param int $count Количество времен для получения
     * @return array Ближайшие доступные времена
     */
    public function getNextAvailableTimes(int $scheduleId, int $count = 5): array
    {
        $availableTime = $this->getAvailableTime($scheduleId);
        return array_slice($availableTime['available_times'], 0, $count);
    }

    /**
     * Получить статистику по времени приемов
     * 
     * @param int $scheduleId ID расписания
     * @return array Статистика
     */
    public function getTimeStatistics(int $scheduleId): array
    {
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            return [];
        }

        $occupiedTimes = $this->getOccupiedTimes($scheduleId);
        $startTime = Carbon::parse($schedule->shift_starts_at);
        $endTime = Carbon::parse($schedule->shift_ends_at);
        
        // Общее количество получасовых интервалов
        $totalIntervals = $this->calculateTotalIntervals($startTime, $endTime);
        
        return [
            'total_intervals' => $totalIntervals,
            'occupied_intervals' => count($occupiedTimes),
            'available_intervals' => $totalIntervals - count($occupiedTimes),
            'occupancy_percentage' => $totalIntervals > 0 ? round((count($occupiedTimes) / $totalIntervals) * 100, 2) : 0
        ];
    }

    /**
     * Рассчитать общее количество получасовых интервалов
     * 
     * @param Carbon $startTime Время начала
     * @param Carbon $endTime Время окончания
     * @return int Количество интервалов
     */
    protected function calculateTotalIntervals(Carbon $startTime, Carbon $endTime): int
    {
        $roundedStart = $this->roundToNextHalfHour($startTime);
        $diffInMinutes = $endTime->diffInMinutes($roundedStart);
        return max(0, floor($diffInMinutes / 30));
    }

    /**
     * Получить рекомендуемое время приема
     * 
     * @param int $scheduleId ID расписания
     * @param int $duration Длительность приема в минутах
     * @return array Рекомендуемое время
     */
    public function getRecommendedTime(int $scheduleId, int $duration = 30): array
    {
        $availableTime = $this->getAvailableTime($scheduleId);
        $availableTimes = $availableTime['available_times'];
        
        if (empty($availableTimes)) {
            return [];
        }

        // Ищем время, которое позволит провести прием полностью
        foreach ($availableTimes as $timeSlot) {
            $time = Carbon::createFromFormat('H:i', $timeSlot['time']);
            $endTime = $time->copy()->addMinutes($duration);
            
            // Проверяем, что прием поместится в смену
            $schedule = Schedule::find($scheduleId);
            if ($schedule && $endTime <= Carbon::parse($schedule->shift_ends_at)) {
                return [
                    'start_time' => $timeSlot['time'],
                    'end_time' => $endTime->format('H:i'),
                    'duration' => $duration,
                    'formatted' => $timeSlot['formatted']
                ];
            }
        }

        return [];
    }
}
