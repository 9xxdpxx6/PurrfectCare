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
use App\Services\Statistics\ConversionStatisticsService;
use App\Services\Statistics\DateRangeService;
use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Branch;
use Carbon\Carbon;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class StatisticsController extends AdminController
{
    protected $dashboardService;
    protected $metricsService;
    protected $weekStatisticsService;
    protected $financialService;
    protected $operationalService;
    protected $clientService;
    protected $medicalService;
    protected $conversionService;
    protected $dateRangeService;

    public function __construct(
        DashboardStatisticsService $dashboardService,
        DashboardMetricsService $metricsService,
        WeekStatisticsService $weekStatisticsService,
        FinancialStatisticsService $financialService,
        OperationalStatisticsService $operationalService,
        ClientStatisticsService $clientService,
        MedicalStatisticsService $medicalService,
        ConversionStatisticsService $conversionService,
        DateRangeService $dateRangeService
    ) {
        // Не вызываем parent::__construct() чтобы избежать автоматической проверки разрешений
        // Используем только middleware для аутентификации
        $this->middleware('auth:admin');
        $this->dashboardService = $dashboardService;
        $this->metricsService = $metricsService;
        $this->weekStatisticsService = $weekStatisticsService;
        $this->financialService = $financialService;
        $this->operationalService = $operationalService;
        $this->clientService = $clientService;
        $this->medicalService = $medicalService;
        $this->conversionService = $conversionService;
        $this->dateRangeService = $dateRangeService;
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

    /**
     * Экспорт дашборда
     */
    public function exportDashboard(Request $request)
    {
        try {
            $this->authorize('statistics_general.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->dashboardService->exportMetrics($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте дашборда', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт финансовой статистики
     */
    public function exportFinancial(Request $request)
    {
        try {
            $this->authorize('statistics_finance.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            $response = $this->financialService->exportRevenue($startDate, $endDate);
            
            // Добавляем заголовки для правильного скачивания файла
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте финансовой статистики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт заказов
     */
    public function exportOrders(Request $request)
    {
        try {
            $this->authorize('statistics_finance.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->financialService->exportOrders($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте заказов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт выручки по категориям
     */
    public function exportCategoryRevenue(Request $request)
    {
        try {
            $this->authorize('statistics_finance.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->financialService->exportCategoryRevenue($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте выручки по категориям', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт выручки по филиалам
     */
    public function exportBranchRevenue(Request $request)
    {
        try {
            $this->authorize('statistics_finance.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->financialService->exportBranchRevenue($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте выручки по филиалам', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт статистики по периодам
     */
    public function exportPeriodStats(Request $request)
    {
        try {
            $this->authorize('statistics_general.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->dashboardService->exportPeriodStats($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте статистики по периодам', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт топ услуг
     */
    public function exportTopServices(Request $request)
    {
        try {
            $this->authorize('statistics_finance.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->dashboardService->exportTopServices($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте топ услуг', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт медицинской статистики
     */
    public function exportMedical(Request $request)
    {
        try {
            $this->authorize('statistics_medicine.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->medicalService->exportMedicalData($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте медицинской статистики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт конверсионной статистики
     */
    public function exportConversion(Request $request)
    {
        try {
            $this->authorize('statistics_conversion.export');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            return $this->conversionService->exportConversionData($startDate, $endDate);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте конверсионной статистики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Получение опций филиалов для tomselect
     */
    public function branchOptions(Request $request)
    {
        $query = $request->get('q', '');
        $selected = $request->get('selected', '');
        
        $branches = Branch::select('id', 'name')
            ->when($query, function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->when($selected, function($q) use ($selected) {
                $q->orWhere('id', $selected);
            })
            ->orderBy('name')
            ->limit(30)
            ->get();
        
        return response()->json($branches->map(function($branch) {
            return [
                'value' => $branch->id,
                'text' => $branch->name
            ];
        }));
    }
    
    /**
     * Получение данных загруженности сотрудников по филиалу
     */
    public function employeeLoad(Request $request)
    {
        try {
            $this->authorize('statistics_efficiency.read');
            
            $period = $request->get('period', 'month');
            $startDateInput = $request->get('start_date');
            $endDateInput = $request->get('end_date');
            $branchId = $request->get('branch_id');
            
            $dateRange = $this->dateRangeService->processDateRange($period, $startDateInput, $endDateInput);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            
            // Получаем данные загруженности с фильтрацией по филиалу
            $employeeLoad = $this->operationalService->getEmployeeLoadByBranch($startDate, $endDate, $branchId);
            
            return response()->json([
                'success' => true,
                'employeeLoad' => $employeeLoad->toArray(),
                'total' => $employeeLoad->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка в employeeLoad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
} 