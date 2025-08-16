<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;
use App\Services\Statistics\ConversionStatisticsService;

class DashboardStatisticsService
{
    public function getMetrics($startDate, $endDate)
    {
        $conversionService = new ConversionStatisticsService();
        $overallConversion = $conversionService->getOverallConversion($startDate, $endDate);
        
        return [
            'total_visits' => Visit::whereBetween('starts_at', [$startDate, $endDate])->count(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('is_paid', true) // Только оплаченные заказы
                ->sum('total'),
            'total_services' => Service::count(),
            'total_veterinarians' => Employee::count(),
            'conversion_rate' => $overallConversion['conversion_rate'],
            'visits_with_orders' => $overallConversion['visits_with_orders'],
        ];
    }

    public function getPeriodStats($startDate, $endDate)
    {
        $stats = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Определяем формат даты в зависимости от периода
        $period = $end->diffInDays($current);
        $dateFormat = $period > 180 ? 'd.m.Y' : 'd.m';
        
        while ($current <= $end) {
            $dateKey = $current->format($dateFormat);
            
            $stats[$dateKey] = [
                'visits' => Visit::whereDate('starts_at', $current)->count(),
                'orders' => Order::whereDate('created_at', $current)->count(),
                'revenue' => Order::whereDate('created_at', $current)
                    ->where('is_paid', true) // Только оплаченные заказы
                    ->sum('total'),
            ];
            
            $current->addDay();
        }
        
        return $stats;
    }

    public function getTopServices($startDate)
    {
        return Order::where('created_at', '>=', $startDate)
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['items' => function($query) {
                $query->where('item_type', Service::class);
            }])
            ->get()
            ->flatMap(function($order) {
                return $order->items;
            })
            ->groupBy('item_id')
            ->map(function($items) {
                return [
                    'service' => $items->first()->item,
                    'count' => $items->count(),
                    'revenue' => $items->sum(function($item) {
                        return $item->quantity * $item->unit_price;
                    }),
                ];
            })
            ->sortByDesc('count')
            ->take(5);
    }

    public function getRevenueData($startDate)
    {
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::now();
        
        while ($current <= $end) {
            $data[$current->format('Y-m-d')] = Order::whereDate('created_at', $current)
                ->where('is_paid', true) // Только оплаченные заказы
                ->sum('total');
            $current->addDay();
        }
        
        return $data;
    }

    public function getWeekAverageStats($startDate, $endDate)
    {
        $stats = [];
        $startOfPeriod = Carbon::parse($startDate);
        $endOfPeriod = Carbon::parse($endDate);
        
        $dayNames = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье'
        ];
        
        // Получаем все недели в выбранном периоде
        $weeksInPeriod = [];
        $currentDate = $startOfPeriod->copy();
        
        while ($currentDate->lte($endOfPeriod)) {
            $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            
            // Проверяем, что неделя пересекается с выбранным периодом
            if ($weekStart->lte($endOfPeriod) && $weekEnd->gte($startOfPeriod)) {
                $weeksInPeriod[] = [
                    'start' => $weekStart,
                    'end' => $weekEnd
                ];
            }
            
            $currentDate->addWeek();
        }
        
        // Считаем статистику для каждого дня недели
        for ($i = 0; $i < 7; $i++) {
            $dayName = Carbon::create()->startOfWeek(Carbon::MONDAY)->addDays($i)->format('l');
            $dayNameRu = $dayNames[$dayName] ?? $dayName;
            
            $totalVisits = 0;
            $totalOrders = 0;
            $totalRevenue = 0;
            $weekCount = 0;
            
            // Считаем сумму по всем неделям периода для данного дня недели
            foreach ($weeksInPeriod as $week) {
                $weekStart = $week['start'];
                $weekEnd = $week['end'];
                
                // Находим дату для конкретного дня недели в этой неделе
                $targetDate = $weekStart->copy()->addDays($i);
                
                // Проверяем, что дата попадает в выбранный период
                if ($targetDate->between($startOfPeriod, $endOfPeriod)) {
                    $totalVisits += Visit::whereDate('starts_at', $targetDate)->count();
                    $totalOrders += Order::whereDate('created_at', $targetDate)->count();
                    $totalRevenue += Order::whereDate('created_at', $targetDate)
                        ->where('is_paid', true)
                        ->sum('total');
                    $weekCount++;
                }
            }
            
            // Вычисляем средние значения
            $stats[$dayNameRu] = [
                'visits' => $weekCount > 0 ? round($totalVisits / $weekCount, 1) : 0,
                'orders' => $weekCount > 0 ? round($totalOrders / $weekCount, 1) : 0,
                'revenue' => $weekCount > 0 ? round($totalRevenue / $weekCount, 2) : 0,
            ];
        }
        
        // Определяем топ-3 дня недели по выручке
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
        
        // Добавляем информацию о топ-3 днях недели
        $topWeekdays = array_slice($weekdayRanking, 0, 3);
        foreach ($stats as $dayName => &$dayStats) {
            $dayStats['rank'] = null;
            foreach ($topWeekdays as $index => $topDay) {
                if ($topDay['day'] === $dayName) {
                    $dayStats['rank'] = $index + 1;
                    break;
                }
            }
        }
        
        // Получаем самый прибыльный день за весь период
        $topDay = $this->getTopDays($startDate, $endDate, 1);
        $bestDay = !empty($topDay) ? $topDay[0] : null;
        
        return [
            'weekdays' => $stats,
            'bestDay' => $bestDay
        ];
    }

    public function getTopDays($startDate, $endDate, $limit = 3)
    {
        $startOfPeriod = Carbon::parse($startDate);
        $endOfPeriod = Carbon::parse($endDate);
        
        $topDays = [];
        $current = $startOfPeriod->copy();
        
        while ($current <= $endOfPeriod) {
            $revenue = Order::whereDate('created_at', $current)
                ->where('is_paid', true)
                ->sum('total');
            
            if ($revenue > 0) {
                $topDays[] = [
                    'date' => $current->copy(),
                    'revenue' => $revenue,
                    'visits' => Visit::whereDate('starts_at', $current)->count(),
                    'orders' => Order::whereDate('created_at', $current)->count(),
                ];
            }
            
            $current->addDay();
        }
        
        // Сортируем по выручке и берем топ-3
        usort($topDays, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        return array_slice($topDays, 0, $limit);
    }

    /**
     * Получить все метрики конверсии
     */
    public function getConversionMetrics($startDate, $endDate)
    {
        $conversionService = new ConversionStatisticsService();
        return $conversionService->getAllConversionMetrics($startDate, $endDate);
    }
}
