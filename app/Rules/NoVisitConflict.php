<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Visit;
use App\Models\Schedule;

class NoVisitConflict implements Rule
{
    private $visitId;

    public function __construct($visitId = null)
    {
        $this->visitId = $visitId;
    }

    public function passes($attribute, $value)
    {
        $scheduleId = request()->input('schedule_id');
        $visitTime = \Carbon\Carbon::parse($value);
        
        if (!$scheduleId) {
            return false;
        }
        
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            return false;
        }
        
        // Ищем конфликтующие визиты
        $query = Visit::where('schedule_id', $scheduleId)
            ->where('starts_at', $visitTime);
        
        // Исключаем текущий визит при обновлении
        if ($this->visitId) {
            $query->where('id', '!=', $this->visitId);
        }
        
        $conflictingVisit = $query->first();
        
        return !$conflictingVisit;
    }

    public function message()
    {
        return 'На это время уже записан другой приём к данному врачу.';
    }
} 