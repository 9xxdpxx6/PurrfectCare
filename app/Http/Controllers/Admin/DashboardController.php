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
        $startOfWeek = Carbon::now()->startOfWeek();
        
        $dayNames = [
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота',
            'Sunday' => 'Воскресенье'
        ];
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayName = $date->format('l'); // День недели на английском
            $dayNameRu = $dayNames[$dayName] ?? $dayName;
            
            $stats[$dayNameRu] = [
                'visits' => Visit::whereDate('starts_at', $date)->count(),
                'orders' => Order::whereDate('created_at', $date)->count(),
                'revenue' => Order::whereDate('created_at', $date)
                    ->where('is_paid', true)
                    ->sum('total'),
            ];
        }
        
        return $stats;
    }
} 