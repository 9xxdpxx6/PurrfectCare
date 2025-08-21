<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use Carbon\Carbon;

class WeekStatisticsService
{
    /**
     * Получить статистику по дням недели за текущий месяц
     */
    public function getWeekStats($startDate, $endDate)
    {
        $stats = [];
        $startOfMonth = Carbon::parse($startDate);
        $endOfMonth = Carbon::parse($endDate);
        
        $dayNames = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье'
        ];
        
        // Получаем все недели в текущем месяце
        $weeksInMonth = $this->getWeeksInMonth($startOfMonth, $endOfMonth);
        
        // Считаем статистику для каждого дня недели
        for ($i = 0; $i < 7; $i++) {
            $dayOfWeek = $i + 1; // 1 = понедельник, 7 = воскресенье
            $dayName = Carbon::create()->startOfWeek(Carbon::MONDAY)->addDays($i)->format('l');
            $dayNameRu = $dayNames[$dayName] ?? $dayName;
            
            $dayStats = $this->calculateDayStats($dayOfWeek, $weeksInMonth, $startOfMonth, $endOfMonth);
            $stats[$dayNameRu] = $dayStats;
        }
        
        // Определяем топ-3 дня недели по выручке
        $weekdayRanking = $this->getWeekdayRanking($stats);
        $topWeekdays = array_slice($weekdayRanking, 0, 3);
        
        // Добавляем ранг для каждого дня недели
        foreach ($stats as $dayName => &$dayStats) {
            $dayStats['rank'] = $this->getDayRank($dayName, $topWeekdays);
        }
        
        // Получаем самый прибыльный день за месяц
        $bestDay = $this->getBestDay($startOfMonth, $endOfMonth);
        
        return [
            'weekdays' => $stats,
            'bestDay' => $bestDay
        ];
    }

    /**
     * Получить все недели в текущем месяце
     */
    private function getWeeksInMonth($startOfMonth, $endOfMonth)
    {
        $weeksInMonth = [];
        $currentDate = $startOfMonth->copy();
        
        while ($currentDate->lte($endOfMonth)) {
            $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            
            // Проверяем, что неделя пересекается с текущим месяцем
            if ($weekStart->lte($endOfMonth) && $weekEnd->gte($startOfMonth)) {
                $weeksInMonth[] = [
                    'start' => $weekStart,
                    'end' => $weekEnd
                ];
            }
            
            $currentDate->addWeek();
        }
        
        return $weeksInMonth;
    }

    /**
     * Рассчитать статистику для конкретного дня недели
     */
    private function calculateDayStats($dayOfWeek, $weeksInMonth, $startOfMonth, $endOfMonth)
    {
        $totalVisits = 0;
        $totalOrders = 0;
        $totalRevenue = 0;
        $weekCount = 0;
        
        // Считаем сумму по всем неделям месяца для данного дня недели
        foreach ($weeksInMonth as $week) {
            $weekStart = $week['start'];
            $weekEnd = $week['end'];
            
            // Находим дату для конкретного дня недели в этой неделе
            $targetDate = $weekStart->copy()->addDays($dayOfWeek - 1);
            
            // Проверяем, что дата попадает в текущий месяц
            if ($targetDate->between($startOfMonth, $endOfMonth)) {
                // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
                $totalVisits += Visit::select(['id'])->whereDate('starts_at', $targetDate)->count();
                
                // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
                $totalOrders += Order::select(['id'])->whereDate('created_at', $targetDate)->count();
                
                // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
                $totalRevenue += Order::select(['total'])
                    ->whereDate('created_at', $targetDate)
                    ->where('is_paid', true)
                    ->sum('total');
                $weekCount++;
            }
        }
        
        // Вычисляем средние значения
        return [
            'visits' => $weekCount > 0 ? round($totalVisits / $weekCount, 1) : 0,
            'orders' => $weekCount > 0 ? round($totalOrders / $weekCount, 1) : 0,
            'revenue' => $weekCount > 0 ? round($totalRevenue / $weekCount, 2) : 0,
        ];
    }

    /**
     * Получить рейтинг дней недели по выручке
     */
    private function getWeekdayRanking($stats)
    {
        $weekdayRanking = [];
        foreach ($stats as $dayName => $dayStats) {
            $weekdayRanking[] = [
                'day' => $dayName,
                'revenue' => $dayStats['revenue']
            ];
        }
        
        // Сортируем по выручке
        usort($weekdayRanking, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        return $weekdayRanking;
    }

    /**
     * Получить ранг дня недели
     */
    private function getDayRank($dayName, $topWeekdays)
    {
        foreach ($topWeekdays as $index => $topDay) {
            if ($topDay['day'] === $dayName) {
                return $index + 1;
            }
        }
        return null;
    }

    /**
     * Получить самый прибыльный день за месяц
     */
    private function getBestDay($startOfMonth, $endOfMonth)
    {
        $topDays = $this->getTopDays($startOfMonth, $endOfMonth, 1);
        return !empty($topDays) ? $topDays[0] : null;
    }

    /**
     * Получить топ дней по выручке
     */
    private function getTopDays($startDate, $endDate, $limit = 3)
    {
        $startOfPeriod = Carbon::parse($startDate);
        $endOfPeriod = Carbon::parse($endDate);
        
        $topDays = [];
        $current = $startOfPeriod->copy();
        
        while ($current <= $endOfPeriod) {
            // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
            $revenue = Order::select(['total'])
                ->whereDate('created_at', $current)
                ->where('is_paid', true)
                ->sum('total');
            
            if ($revenue > 0) {
                $topDays[] = [
                    'date' => $current->copy(),
                    'revenue' => $revenue,
                    // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
                    'visits' => Visit::select(['id'])->whereDate('starts_at', $current)->count(),
                    // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
                    'orders' => Order::select(['id'])->whereDate('created_at', $current)->count(),
                ];
            }
            
            $current->addDay();
        }
        
        // Сортируем по выручке и берем топ
        usort($topDays, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        return array_slice($topDays, 0, $limit);
    }
}
