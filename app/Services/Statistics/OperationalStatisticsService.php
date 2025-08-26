<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Schedule;
use App\Models\Status;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OperationalStatisticsService
{
    public function getVisitsData($startDate, $endDate)
    {
        $visitsQuery = Visit::whereBetween('starts_at', [$startDate, $endDate]);

        $total = (clone $visitsQuery)->count();
        
        $completed = (clone $visitsQuery)
            ->whereHas('status', fn($q) => $q->where('name', 'Завершён'))
            ->count();
            
        $cancelled = (clone $visitsQuery)
            ->whereHas('status', fn($q) => $q->where('name', 'Отменён'))
            ->count();

        $byDay = (clone $visitsQuery)
            ->select(DB::raw('DATE(starts_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'by_day' => $byDay,
        ];
    }

    public function getEmployeeLoad($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $totalVisits = Visit::select(['id'])->whereBetween('starts_at', [$startDate, $endDate])->count();
        
        $employeeStats = Visit::whereBetween('visits.starts_at', [$startDate, $endDate])
            ->join('schedules', 'visits.schedule_id', '=', 'schedules.id')
            ->join('employees', 'schedules.veterinarian_id', '=', 'employees.id')
            ->select(
                'schedules.veterinarian_id',
                'employees.id as employee_id',
                'employees.name as employee_name',
                DB::raw('count(visits.id) as visits_count'),
                DB::raw('count(distinct DATE(visits.starts_at)) as working_days')
            )
            ->groupBy('schedules.veterinarian_id', 'employee_id', 'employee_name')
            ->get();

        $employeeIds = $employeeStats->pluck('employee_id');
        $employees = Employee::with('specialties')->find($employeeIds)->keyBy('id');
            
        $employeeData = $employeeStats->map(function($stats) use ($totalVisits, $employees) {
            $avgVisitsPerDay = $stats->working_days > 0 ? $stats->visits_count / $stats->working_days : 0;
            $visitsPercentage = $totalVisits > 0 ? round(($stats->visits_count / $totalVisits) * 100, 1) : 0;
            
            return [
                'employee' => $employees->get($stats->employee_id),
                'visits_count' => $stats->visits_count,
                'working_days' => $stats->working_days,
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
        
        $vetIds = $employeeData->pluck('employee.id');
        $allSchedules = Schedule::select(['id', 'veterinarian_id', 'shift_starts_at', 'shift_ends_at'])
            ->whereIn('veterinarian_id', $vetIds)
            ->whereBetween('shift_starts_at', [$startDate, $endDate])
            ->get()
            ->groupBy('veterinarian_id');

        // Применяем рассчитанные пороги к каждому сотруднику
        return $employeeData->map(function($data) use ($lowThreshold, $mediumThreshold, $allSchedules) {
            $avgVisitsPerDay = $data['avg_visits_per_day'];
            $employee = $data['employee'];
            
            $schedules = $allSchedules->get($employee->id) ?? collect();
            
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
        $statusStats = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->join('statuses', 'visits.status_id', '=', 'statuses.id')
            ->select('statuses.name', DB::raw('count(visits.id) as count'))
            ->groupBy('statuses.name')
            ->get()
            ->pluck('count', 'name');
        
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
