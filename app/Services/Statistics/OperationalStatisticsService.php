<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Schedule;
use App\Models\Status;
use Carbon\Carbon;

class OperationalStatisticsService
{
    public function getVisitsData($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $visits = Visit::select(['id', 'starts_at', 'status_id'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['status:id,name'])
            ->get();
        
        $byDay = $visits->groupBy(function($visit) {
            return $visit->starts_at->format('Y-m-d');
        })->map->count();
        
        return [
            'total' => $visits->count(),
            'completed' => $visits->filter(function($visit) {
                return $visit->status && $visit->status->name === 'Завершён';
            })->count(),
            'cancelled' => $visits->filter(function($visit) {
                return $visit->status && $visit->status->name === 'Отменён';
            })->count(),
            'by_day' => $byDay,
        ];
    }

    public function getEmployeeLoad($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $totalVisits = Visit::select(['id'])->whereBetween('starts_at', [$startDate, $endDate])->count();
        
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $employeeData = Visit::select(['id', 'starts_at', 'schedule_id'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['schedule:id,veterinarian_id', 'schedule.veterinarian:id,name,email'])
            ->get()
            ->groupBy('schedule.veterinarian_id')
            ->map(function($visits, $employeeId) use ($startDate, $endDate, $totalVisits) {
                $employee = $visits->first()->schedule->veterinarian;
                
                // Считаем дни когда ветеринар работал (были приёмы)
                $workingDays = $visits->groupBy(function($visit) {
                    return $visit->starts_at->format('Y-m-d');
                })->count();
                
                $visitsCount = $visits->count();
                $avgVisitsPerDay = $workingDays > 0 ? $visitsCount / $workingDays : 0;
                
                // Процент от общего количества приемов
                $visitsPercentage = $totalVisits > 0 ? round(($visitsCount / $totalVisits) * 100, 1) : 0;
                
                return [
                    'employee' => $employee,
                    'visits_count' => $visitsCount,
                    'working_days' => $workingDays,
                    'avg_visits_per_day' => $avgVisitsPerDay,
                    'visits_percentage' => $visitsPercentage,
                ];
            });
        
        // Рассчитываем динамические пороги на основе фактических данных
        $allAvgLoads = $employeeData->pluck('avg_visits_per_day')->filter()->values();
        
        if ($allAvgLoads->count() > 0) {
            // Сортируем для расчёта перцентилей
            $sortedLoads = $allAvgLoads->sort()->values();
            $count = $sortedLoads->count();
            
            if ($count > 2) {
                // Рассчитываем 33-й и 66-й перцентили
                $p33Index = (int) ceil($count * 0.33) - 1;
                $p66Index = (int) ceil($count * 0.66) - 1;
                
                $lowThreshold = max(8, $sortedLoads[$p33Index]);
                $mediumThreshold = max(12, $sortedLoads[$p66Index]);
            } else {
                // Если данных мало, используем минимум и медиану
                $lowThreshold = max(8, $sortedLoads->min());
                $mediumThreshold = max(12, $sortedLoads->median());
            }
            
            $maxLoad = $allAvgLoads->max();
        } else {
            // Fallback на реалистичные значения для ветеринарной практики
            $lowThreshold = 8;
            $mediumThreshold = 12;
            $maxLoad = 20;
        }
        
        // Применяем рассчитанные пороги к каждому сотруднику
        return $employeeData->map(function($data) use ($lowThreshold, $mediumThreshold, $startDate, $endDate) {
            $avgVisitsPerDay = $data['avg_visits_per_day'];
            $employee = $data['employee'];
            
            // Рассчитываем теоретический максимум на основе расписания ветеринара
            // Оптимизация: используем индексы на veterinarian_id и shift_starts_at, select для выбора только нужных полей
            $schedules = Schedule::select(['id', 'shift_starts_at', 'shift_ends_at'])
                ->where('veterinarian_id', $employee->id)
                ->whereBetween('shift_starts_at', [$startDate, $endDate])
                ->get();
            
            $theoreticalMaxPerDay = 0;
            if ($schedules->count() > 0) {
                // Рассчитываем среднее количество рабочих часов в день
                $totalWorkingHours = 0;
                foreach ($schedules as $schedule) {
                    $shiftStart = Carbon::parse($schedule->shift_starts_at);
                    $shiftEnd = Carbon::parse($schedule->shift_ends_at);
                    $workingHours = $shiftEnd->diffInHours($shiftStart);
                    $totalWorkingHours += $workingHours;
                }
                $avgWorkingHoursPerDay = $totalWorkingHours / $schedules->count();
                
                // Длительность приёма = 30 минут = 2 приёма в час
                $theoreticalMaxPerDay = $avgWorkingHoursPerDay * 2;
            }
            
            // Если не можем рассчитать теоретический максимум, используем фиксированное значение
            $individualMaxLoad = $theoreticalMaxPerDay > 0 ? $theoreticalMaxPerDay : 18;
            
            // Определяем уровень загруженности на основе динамических порогов
            if ($avgVisitsPerDay <= $lowThreshold) {
                $loadLevel = 'Низкая';
                $loadColor = 'success';
            } elseif ($avgVisitsPerDay <= $mediumThreshold) {
                $loadLevel = 'Средняя';
                $loadColor = 'warning';
            } else {
                $loadLevel = 'Высокая';
                $loadColor = 'danger';
            }
            
            // Расчет процентов для прогресс-бара (может быть больше 100%)
            $progressWidth = ($avgVisitsPerDay / $individualMaxLoad) * 100;
            $progressPercentage = round($progressWidth);
            
            return array_merge($data, [
                'load_level' => $loadLevel,
                'load_color' => $loadColor,
                'progress_width' => $progressWidth,
                'progress_percentage' => $progressPercentage,
                'theoretical_max' => round($individualMaxLoad, 1),
                'thresholds' => [
                    'low' => round($lowThreshold, 1),
                    'medium' => round($mediumThreshold, 1),
                    'max' => round($individualMaxLoad, 1),
                ],
            ]);
        })->sortByDesc('visits_count');
    }

    public function getStatusStats($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $statusStats = Visit::select(['id', 'status_id'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->with(['status:id,name'])
            ->get()
            ->groupBy(function($visit) {
                return $visit->status ? $visit->status->name : 'Без статуса';
            })
            ->map->count();
        
        $totalVisits = $statusStats->sum();
        
        return $statusStats->map(function($count) use ($totalVisits) {
            return [
                'count' => $count,
                'percentage' => $totalVisits > 0 ? round(($count / $totalVisits) * 100, 1) : 0,
            ];
        });
    }

    public function getScheduleStats($startDate, $endDate)
    {
        // Оптимизация: используем индекс на shift_starts_at и select для выбора только нужных полей
        $totalSchedules = Schedule::select(['id'])->whereBetween('shift_starts_at', [$startDate, $endDate])->count();
        
        // Оптимизация: используем индекс на shift_starts_at и select для выбора только нужных полей
        $schedulesWithVisits = Schedule::select(['id'])
            ->whereBetween('shift_starts_at', [$startDate, $endDate])
            ->whereHas('visits', function($query) {
                $query->select(['id', 'schedule_id']);
            })
            ->count();
        
        return [
            'total_schedules' => $totalSchedules,
            'schedules_with_visits' => $schedulesWithVisits,
            'utilization_percentage' => $totalSchedules > 0 ? round(($schedulesWithVisits / $totalSchedules) * 100, 1) : 0,
        ];
    }
}
