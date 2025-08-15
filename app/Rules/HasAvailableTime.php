<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Schedule;
use App\Models\Visit;

class HasAvailableTime implements Rule
{
    private $visitId;

    public function __construct($visitId = null)
    {
        $this->visitId = $visitId;
    }

    public function passes($attribute, $value)
    {
        $scheduleId = request()->input('schedule_id');
        
        if (!$scheduleId) {
            return false;
        }
        
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            return false;
        }
        
        // Получаем все занятые времена для этого расписания
        $query = Visit::where('schedule_id', $scheduleId);
        
        // Исключаем текущий визит при обновлении
        if ($this->visitId) {
            $query->where('id', '!=', $this->visitId);
        }
        
        $occupiedTimes = $query->pluck('starts_at')
            ->map(function($time) {
                return \Carbon\Carbon::parse($time)->format('H:i');
            })
            ->toArray();
        
        // Генерируем доступные времена (кратно получасу: 00 и 30 минут)
        $availableTimes = [];
        $startTime = \Carbon\Carbon::parse($schedule->shift_starts_at);
        $endTime = \Carbon\Carbon::parse($schedule->shift_ends_at);
        
        // Начинаем с ближайшего получаса после начала смены
        $currentTime = $startTime->copy();
        
        // Если время не кратно получасу, округляем вверх до следующего получаса
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
        
        // Генерируем времена с интервалом 30 минут
        while ($currentTime < $endTime) {
            $timeString = $currentTime->format('H:i');
            if (!in_array($timeString, $occupiedTimes)) {
                $availableTimes[] = $timeString;
            }
            $currentTime->addMinutes(30);
        }
        
        // Проверяем, есть ли доступное время
        return !empty($availableTimes);
    }

    public function message()
    {
        return 'Для выбранного расписания нет свободного времени. Все слоты заняты.';
    }
}
