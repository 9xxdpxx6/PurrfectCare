<?php

namespace App\Services\Schedule;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateTimeProcessingService
{
    /**
     * Обработка полей даты и времени для создания datetime полей
     * 
     * @param Request $request
     * @return void
     */
    public function processScheduleDateTimeFields(Request $request): void
    {
        if ($request->has('shift_date') && $request->has('start_time')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->shift_date);
                $startTime = $request->start_time;
                $request->merge([
                    'shift_starts_at' => $date->format('Y-m-d') . ' ' . $startTime
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }

        if ($request->has('shift_date') && $request->has('end_time')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->shift_date);
                $endTime = $request->end_time;
                $request->merge([
                    'shift_ends_at' => $date->format('Y-m-d') . ' ' . $endTime
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }
    }

    /**
     * Обработка поля начала недели
     * 
     * @param Request $request
     * @return void
     */
    public function processWeekStartField(Request $request): void
    {
        if ($request->has('week_start')) {
            try {
                $date = Carbon::createFromFormat('d.m.Y', $request->week_start);
                $request->merge([
                    'week_start' => $date->format('Y-m-d')
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибки парсинга, валидация их поймает
            }
        }
    }

    /**
     * Получить маппинг дней недели
     * 
     * @return array
     */
    public function getDayMap(): array
    {
        return [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];
    }

    /**
     * Получить названия дней недели на русском
     * 
     * @return array
     */
    public function getDayNames(): array
    {
        return [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье'
        ];
    }

    /**
     * Создать datetime для конкретного дня недели
     * 
     * @param string $weekStart Начало недели
     * @param string $day День недели (monday, tuesday, etc.)
     * @param string $startTime Время начала
     * @param string $endTime Время окончания
     * @return array Массив с shift_starts_at и shift_ends_at
     */
    public function createWeekDayDateTime(string $weekStart, string $day, string $startTime, string $endTime): array
    {
        $dayMap = $this->getDayMap();
        $weekStartDate = Carbon::parse($weekStart)->startOfWeek();
        $dayOffset = $dayMap[$day];
        $shiftDate = $weekStartDate->copy()->addDays($dayOffset);
        
        return [
            'shift_starts_at' => $shiftDate->copy()->format('Y-m-d') . ' ' . $startTime,
            'shift_ends_at' => $shiftDate->copy()->format('Y-m-d') . ' ' . $endTime,
            'date' => $shiftDate->format('d.m.Y')
        ];
    }
}
