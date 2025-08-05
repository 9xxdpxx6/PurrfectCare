<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Schedule;

class VisitTimeWithinSchedule implements Rule
{
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
        
        $visitTime = \Carbon\Carbon::parse($value);
        $shiftStart = \Carbon\Carbon::parse($schedule->shift_starts_at);
        $shiftEnd = \Carbon\Carbon::parse($schedule->shift_ends_at);
        
        // Проверяем, что время приема находится в рамках смены
        // Добавляем небольшой буфер для округления (30 минут)
        $shiftStartWithBuffer = $shiftStart->copy()->subMinutes(30);
        $shiftEndWithBuffer = $shiftEnd->copy()->addMinutes(30);
        
        $isValid = $visitTime->between($shiftStartWithBuffer, $shiftEndWithBuffer);
        
        // Отладочная информация
        if (!$isValid) {
            \Log::info('VisitTimeWithinSchedule validation failed', [
                'visit_time' => $value,
                'shift_start' => $schedule->shift_starts_at,
                'shift_end' => $schedule->shift_ends_at,
                'shift_start_with_buffer' => $shiftStartWithBuffer->format('Y-m-d H:i:s'),
                'shift_end_with_buffer' => $shiftEndWithBuffer->format('Y-m-d H:i:s'),
                'visit_time_parsed' => $visitTime->format('Y-m-d H:i:s')
            ]);
        }
        
        return $isValid;
    }

    public function message()
    {
        return 'Время приема должно находиться в рамках выбранного расписания.';
    }
} 