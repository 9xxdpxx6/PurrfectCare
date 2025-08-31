<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use App\Services\Statistics\DashboardStatisticsService;
use App\Services\Statistics\DashboardMetricsService;
use App\Services\Statistics\WeekStatisticsService;
use App\Services\Statistics\FinancialStatisticsService;
use App\Services\Statistics\OperationalStatisticsService;
use App\Services\Statistics\ClientStatisticsService;
use App\Services\Statistics\MedicalStatisticsService;
use App\Services\Statistics\DateRangeService;
use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Branch;
use Carbon\Carbon;

class StatisticsController extends AdminController
{
    protected $dashboardService;
    protected $metricsService;
    protected $weekStatisticsService;
    protected $financialService;
    protected $operationalService;
    protected $clientService;
    protected $medicalService;
    protected $dateRangeService;

    public function __construct(
        DashboardStatisticsService $dashboardService,
        DashboardMetricsService $metricsService,
        WeekStatisticsService $weekStatisticsService,
        FinancialStatisticsService $financialService,
        OperationalStatisticsService $operationalService,
        ClientStatisticsService $clientService,
        MedicalStatisticsService $medicalService,
        DateRangeService $dateRangeService
    ) {
        parent::__construct();
        $this->dashboardService = $dashboardService;
        $this->metricsService = $metricsService;
        $this->weekStatisticsService = $weekStatisticsService;
        $this->financialService = $financialService;
        $this->operationalService = $operationalService;
        $this->clientService = $clientService;
        $this->medicalService = $medicalService;
        $this->dateRangeService = $dateRangeService;
        $this->permissionPrefix = 'statistics';
    }

    public function dashboard(Request $request)
    {
        $this->authorize('statistics_general.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Основные метрики
        $metrics = $this->dashboardService->getMetrics($startDate, $endDate);
        
        // Дополнительные метрики через сервис
        $additionalMetrics = $this->metricsService->getAdditionalMetrics(
            $startDate, 
            $endDate, 
            $metrics['total_orders'], 
            $metrics['total_revenue']
        );
        
        // Объединяем метрики
        $metrics = array_merge($metrics, $additionalMetrics);
        
        // Статистика по выбранному периоду
        $weeklyStats = $this->dashboardService->getPeriodStats($startDate, $endDate);
        
        // Средние показатели за неделю в выбранном периоде
        $weekAverageStats = $this->weekStatisticsService->getWeekStats($startDate, $endDate);
        
        // Топ услуг
        $topServices = $this->dashboardService->getTopServices($startDate);
        
        return view('admin.statistics.dashboard', compact('metrics', 'weeklyStats', 'weekAverageStats', 'topServices', 'period', 'startDate', 'endDate', 'dateRangeString'));
    }
    
    public function financial(Request $request)
    {
        $this->authorize('statistics_finance.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Выручка по периодам
        $revenueData = $this->financialService->getRevenueData($startDate, $endDate);
        
        // Общее количество заказов и выручка
        $totalOrders = $this->financialService->getTotalOrders($startDate, $endDate);
        $totalRevenue = $this->financialService->getTotalRevenue($startDate, $endDate);
        
        // Выручка по категориям
        $categoryRevenue = $this->financialService->getCategoryRevenue($startDate, $endDate);
        
        // Выручка по филиалам
        $branchRevenue = $this->financialService->getBranchRevenue($startDate, $endDate);
        
        // Топ услуг
        $topServices = $this->financialService->getTopServices($startDate, $endDate);
        
        return view('admin.statistics.financial', compact(
            'revenueData', 
            'totalOrders', 
            'totalRevenue',
            'categoryRevenue', 
            'branchRevenue', 
            'topServices', 
            'period', 
            'startDate', 
            'endDate', 
            'dateRangeString'
        ));
    }
    
    public function operational(Request $request)
    {
        $this->authorize('statistics_efficiency.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Данные приемов
        $visitsData = $this->operationalService->getVisitsData($startDate, $endDate);
        
        // Загруженность сотрудников
        $employeeLoad = $this->operationalService->getEmployeeLoad($startDate, $endDate);
        
        // Статистика по статусам
        $statusStats = $this->operationalService->getStatusStats($startDate, $endDate);
        
        // Статистика расписания
        $scheduleStats = $this->operationalService->getScheduleStats($startDate, $endDate);
        
        return view('admin.statistics.operational', compact('visitsData', 'employeeLoad', 'statusStats', 'scheduleStats', 'period', 'startDate', 'endDate', 'dateRangeString'));
    }
    
    public function clients(Request $request)
    {
        $this->authorize('statistics_clients.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Данные клиентов
        $clientsData = $this->clientService->getClientsData($startDate, $endDate);
        
        // Данные питомцев
        $petsData = $this->clientService->getPetsData($startDate, $endDate);
        
        // Топ клиентов
        $topClients = $this->clientService->getTopClients($startDate, $endDate);
        
        return view('admin.statistics.clients', compact('clientsData', 'petsData', 'topClients', 'period', 'startDate', 'endDate', 'dateRangeString'));
    }
    
    public function medical(Request $request)
    {
        $this->authorize('statistics_medicine.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Статистика диагнозов
        $diagnosesData = $this->medicalService->getDiagnosesData($startDate, $endDate);
        
        // Дополнительные данные для диагнозов
        $diagnosesCount = $this->medicalService->getDiagnosesCount($startDate, $endDate);
        $totalDiagnosesCount = $this->medicalService->getTotalDiagnosesCount($startDate, $endDate);
        
        // Статистика вакцинаций
        $vaccinationsData = $this->medicalService->getVaccinationsData($startDate, $endDate);
        
        // Статистика анализов
        $labTestsData = $this->medicalService->getLabTestsData($startDate, $endDate);
        $labTestsTypesCount = $this->medicalService->getLabTestsTypesCount($startDate, $endDate);
        
        // Подготавливаем данные для отображения
        $labTestsDataForDisplay = $labTestsData->mapWithKeys(function($item) {
            return [$item['name'] => $item['count']];
        });
        
        return view('admin.statistics.medical', compact('diagnosesData', 'diagnosesCount', 'totalDiagnosesCount', 'vaccinationsData', 'labTestsData', 'labTestsDataForDisplay', 'labTestsTypesCount', 'period', 'startDate', 'endDate', 'dateRangeString'));
    }
    
    public function conversion(Request $request)
    {
        $this->authorize('statistics_conversion.read');
        
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $dateRangeString = $dateRange['dateRange'];
        
        // Получаем все метрики конверсии
        $conversionMetrics = $this->dashboardService->getConversionMetrics($startDate, $endDate);
        
        return view('admin.statistics.conversion', compact('conversionMetrics', 'period', 'startDate', 'endDate', 'dateRangeString'));
    }
    
} 