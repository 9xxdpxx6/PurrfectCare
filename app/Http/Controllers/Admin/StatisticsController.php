<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Statistics\DashboardStatisticsService;
use App\Services\Statistics\FinancialStatisticsService;
use App\Services\Statistics\OperationalStatisticsService;
use App\Services\Statistics\ClientStatisticsService;
use App\Services\Statistics\MedicalStatisticsService;
use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Branch;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    protected $dashboardService;
    protected $financialService;
    protected $operationalService;
    protected $clientService;
    protected $medicalService;

    public function __construct(
        DashboardStatisticsService $dashboardService,
        FinancialStatisticsService $financialService,
        OperationalStatisticsService $operationalService,
        ClientStatisticsService $clientService,
        MedicalStatisticsService $medicalService
    ) {
        $this->dashboardService = $dashboardService;
        $this->financialService = $financialService;
        $this->operationalService = $operationalService;
        $this->clientService = $clientService;
        $this->medicalService = $medicalService;
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Основные метрики
        $metrics = $this->dashboardService->getMetrics($startDate, $endDate);
        
        // Дополнительные метрики
        $metrics['total_clients'] = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $metrics['total_pets'] = Pet::whereBetween('created_at', [$startDate, $endDate])->count();
        $metrics['total_employees'] = Employee::count();
        $metrics['total_branches'] = Branch::count();
        
        // Средний чек
        $metrics['average_order'] = $metrics['total_orders'] > 0 
            ? round($metrics['total_revenue'] / $metrics['total_orders'], 2) 
            : 0;
        
        // Конверсия приёмов в заказы (на основе реальных связей)
        $visitsWithOrders = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('orders')
            ->count();
        
        $metrics['conversion_rate'] = $metrics['total_visits'] > 0 
            ? round(($visitsWithOrders / $metrics['total_visits']) * 100, 1) 
            : 0;
        
        // Статистика по выбранному периоду
        $weeklyStats = $this->dashboardService->getPeriodStats($startDate, $endDate);
        
        // Средние показатели за неделю в выбранном периоде
        $weekAverageStats = $this->dashboardService->getWeekAverageStats($startDate, $endDate);
        
        // Топ услуг
        $topServices = $this->dashboardService->getTopServices($startDate);
        
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.dashboard', compact('metrics', 'weeklyStats', 'weekAverageStats', 'topServices', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function financial(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Выручка по периодам
        $revenueData = $this->financialService->getRevenueData($startDate, $endDate);
        
        // Выручка по категориям
        $categoryRevenue = $this->financialService->getCategoryRevenue($startDate, $endDate);
        
        // Выручка по филиалам
        $branchRevenue = $this->financialService->getBranchRevenue($startDate, $endDate);
        
        // Топ услуг
        $topServices = $this->financialService->getTopServices($startDate, $endDate);
        
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.financial', compact('revenueData', 'categoryRevenue', 'branchRevenue', 'topServices', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function operational(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Данные приемов
        $visitsData = $this->operationalService->getVisitsData($startDate, $endDate);
        
        // Загруженность сотрудников
        $employeeLoad = $this->operationalService->getEmployeeLoad($startDate, $endDate);
        
        // Статистика по статусам
        $statusStats = $this->operationalService->getStatusStats($startDate, $endDate);
        
        // Статистика расписания
        $scheduleStats = $this->operationalService->getScheduleStats($startDate, $endDate);
        
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.operational', compact('visitsData', 'employeeLoad', 'statusStats', 'scheduleStats', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function clients(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Данные клиентов
        $clientsData = $this->clientService->getClientsData($startDate, $endDate);
        
        // Данные питомцев
        $petsData = $this->clientService->getPetsData($startDate, $endDate);
        
        // Топ клиентов
        $topClients = $this->clientService->getTopClients($startDate, $endDate);
        
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.clients', compact('clientsData', 'petsData', 'topClients', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function medical(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
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
        
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.medical', compact('diagnosesData', 'diagnosesCount', 'totalDiagnosesCount', 'vaccinationsData', 'labTestsData', 'labTestsDataForDisplay', 'labTestsTypesCount', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    private function getStartDate($period)
    {
        return match($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            'all' => $this->getEarliestDataDate(),
            default => Carbon::now()->subMonth(),
        };
    }

    private function getEarliestDataDate()
    {
        // Получаем самую раннюю дату из всех основных таблиц
        $dates = [];
        
        // Заказы
        $earliestOrder = Order::orderBy('created_at')->first();
        if ($earliestOrder) {
            $dates[] = $earliestOrder->created_at;
        }
        
        // Приемы
        $earliestVisit = Visit::orderBy('starts_at')->first();
        if ($earliestVisit) {
            $dates[] = $earliestVisit->starts_at;
        }
        
        // Клиенты
        $earliestUser = User::orderBy('created_at')->first();
        if ($earliestUser) {
            $dates[] = $earliestUser->created_at;
        }
        
        // Питомцы
        $earliestPet = Pet::orderBy('created_at')->first();
        if ($earliestPet) {
            $dates[] = $earliestPet->created_at;
        }
        
        // Если есть данные, возвращаем самую раннюю дату
        if (!empty($dates)) {
            return min($dates);
        }
        
        // Если данных нет, возвращаем дату 3 года назад
        return Carbon::now()->subYears(3);
    }
} 