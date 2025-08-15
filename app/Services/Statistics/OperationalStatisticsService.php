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
        $visits = Visit::whereBetween('starts_at', [$startDate, $endDate])->get();
        
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
        $totalVisits = Visit::whereBetween('starts_at', [$startDate, $endDate])->count();
        
        $employeeData = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->with('schedule.veterinarian')
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
            
            $maxLoad = max(20, $allAvgLoads->max() * 1.2);
        } else {
            // Fallback на реалистичные значения для ветеринарной практики
            $lowThreshold = 8;
            $mediumThreshold = 12;
            $maxLoad = 20;
        }
        
        // Применяем рассчитанные пороги к каждому сотруднику
        return $employeeData->map(function($data) use ($lowThreshold, $mediumThreshold, $maxLoad) {
            $avgVisitsPerDay = $data['avg_visits_per_day'];
            
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
            
            // Расчет процентов для прогресс-бара
            $progressWidth = min(100, ($avgVisitsPerDay / $maxLoad) * 100);
            $progressPercentage = round($progressWidth);
            
            return array_merge($data, [
                'load_level' => $loadLevel,
                'load_color' => $loadColor,
                'progress_width' => $progressWidth,
                'progress_percentage' => $progressPercentage,
                'thresholds' => [
                    'low' => round($lowThreshold, 1),
                    'medium' => round($mediumThreshold, 1),
                    'max' => round($maxLoad, 1),
                ],
            ]);
        })->sortByDesc('visits_count');
    }

    public function getStatusStats($startDate, $endDate)
    {
        $statusStats = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->with('status')
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
        $totalSchedules = Schedule::whereBetween('shift_starts_at', [$startDate, $endDate])->count();
        $schedulesWithVisits = Schedule::whereBetween('shift_starts_at', [$startDate, $endDate])
            ->whereHas('visits')
            ->count();
        
        return [
            'total_schedules' => $totalSchedules,
            'schedules_with_visits' => $schedulesWithVisits,
            'utilization_percentage' => $totalSchedules > 0 ? round(($schedulesWithVisits / $totalSchedules) * 100, 1) : 0,
        ];
    }
}
