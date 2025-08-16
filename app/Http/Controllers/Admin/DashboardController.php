<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\DashboardStatisticsService;
use App\Services\Statistics\DashboardMetricsService;
use App\Services\Statistics\WeekStatisticsService;
use App\Services\Statistics\DashboardDataService;
use App\Services\Statistics\DateRangeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $statisticsService;
    protected $metricsService;
    protected $weekStatisticsService;
    protected $dataService;
    protected $dateRangeService;

    public function __construct(
        DashboardStatisticsService $statisticsService,
        DashboardMetricsService $metricsService,
        WeekStatisticsService $weekStatisticsService,
        DashboardDataService $dataService,
        DateRangeService $dateRangeService
    ) {
        $this->statisticsService = $statisticsService;
        $this->metricsService = $metricsService;
        $this->weekStatisticsService = $weekStatisticsService;
        $this->dataService = $dataService;
        $this->dateRangeService = $dateRangeService;
    }

    public function dashboard()
    {
        // Получаем данные за текущий месяц
        $startDate = $this->dateRangeService->getStartDate('month');
        $endDate = Carbon::now();
        
        // Основные метрики
        $metrics = $this->statisticsService->getMetrics($startDate, $endDate);
        
        // Дополнительные метрики для главной страницы
        $additionalMetrics = $this->metricsService->getAdditionalMetrics(
            $startDate, 
            $endDate, 
            $metrics['total_orders'], 
            $metrics['total_revenue']
        );
        
        // Сегодняшние приёмы
        $todayVisits = $this->dataService->getTodayVisits();
        
        // Ближайшие приёмы (завтра)
        $tomorrowVisits = $this->dataService->getTomorrowVisits();
        
        // Последние заказы
        $recentOrders = $this->dataService->getRecentOrders();
        
        // Статистика по дням недели
        $weekStats = $this->weekStatisticsService->getWeekStats($startDate, $endDate);
        
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
} 