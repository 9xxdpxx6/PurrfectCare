<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\DashboardStatisticsService;
use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $statisticsService;

    public function __construct(DashboardStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function dashboard()
    {
        // Получаем данные за текущий месяц
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Основные метрики
        $metrics = $this->statisticsService->getMetrics($startDate, $endDate);
        
        // Дополнительные метрики для главной страницы
        $additionalMetrics = [
            'total_clients' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_pets' => Pet::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_branches' => Branch::count(),
            'total_employees' => Employee::count(),
            'average_order' => $metrics['total_orders'] > 0 ? round($metrics['total_revenue'] / $metrics['total_orders']) : 0,
            'conversion_rate' => $metrics['total_visits'] > 0 ? round(($metrics['total_orders'] / $metrics['total_visits']) * 100) : 0,
        ];
        
        // Сегодняшние приёмы
        $todayVisits = Visit::whereDate('starts_at', Carbon::today())
            ->with(['pet.client', 'schedule.veterinarian', 'status'])
            ->orderBy('starts_at')
            ->get();
        
        // Ближайшие приёмы (завтра)
        $tomorrowVisits = Visit::whereDate('starts_at', Carbon::tomorrow())
            ->with(['pet.client', 'schedule.veterinarian', 'status'])
            ->orderBy('starts_at')
            ->limit(5)
            ->get();
        
        // Последние заказы
        $recentOrders = Order::with(['client', 'pet', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Статистика по дням недели
        $weekStats = $this->getWeekStats();
        
        // Топ услуг
        $topServices = $this->statisticsService->getTopServices($startDate);
        
        return view('admin.dashboard', compact(
            'metrics',
            'additionalMetrics',
            'todayVisits',
            'tomorrowVisits',
            'recentOrders',
            'weekStats',
            'topServices'
        ));
    }
    
    private function getWeekStats()
    {
        $stats = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
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
        
        // Считаем статистику для каждого дня недели
        for ($i = 0; $i < 7; $i++) {
            $dayOfWeek = $i + 1; // 1 = понедельник, 7 = воскресенье
            $dayName = Carbon::create()->startOfWeek(Carbon::MONDAY)->addDays($i)->format('l');
            $dayNameRu = $dayNames[$dayName] ?? $dayName;
            
            $totalVisits = 0;
            $totalOrders = 0;
            $totalRevenue = 0;
            $weekCount = 0;
            
            // Считаем сумму по всем неделям месяца для данного дня недели
            foreach ($weeksInMonth as $week) {
                $weekStart = $week['start'];
                $weekEnd = $week['end'];
                
                // Находим дату для конкретного дня недели в этой неделе
                $targetDate = $weekStart->copy()->addDays($i);
                
                // Проверяем, что дата попадает в текущий месяц
                if ($targetDate->between($startOfMonth, $endOfMonth)) {
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
        
        // Получаем самый прибыльный день за месяц
        $topDay = $this->statisticsService->getTopDays($startOfMonth, $endOfMonth, 1);
        $bestDay = !empty($topDay) ? $topDay[0] : null;
        
        return [
            'weekdays' => $stats,
            'bestDay' => $bestDay
        ];
    }
} 