<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;
use App\Services\Statistics\ConversionStatisticsService;
use Illuminate\Support\Facades\DB;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class DashboardStatisticsService
{
    public function getMetrics($startDate, $endDate)
    {
        $conversionService = new ConversionStatisticsService();
        $overallConversion = $conversionService->getOverallConversion($startDate, $endDate);
        
        return [
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            'total_visits' => Visit::select(['id'])->whereBetween('starts_at', [$startDate, $endDate])->count(),
            
            // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
            'total_orders' => Order::select(['id'])->whereBetween('created_at', [$startDate, $endDate])->count(),
            
            // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
            'total_revenue' => Order::select(['total'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('is_paid', true) // Только оплаченные заказы
                ->sum('total'),
                
            // Оптимизация: используем select для выбора только нужных полей
            'total_services' => Service::select(['id'])->count(),
            'total_veterinarians' => Employee::select(['id'])->count(),
            'conversion_rate' => $overallConversion['conversion_rate'],
            'visits_with_orders' => $overallConversion['visits_with_orders'],
        ];
    }

    public function getPeriodStats($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $visitsByDay = Visit::whereBetween('starts_at', [$start, $end])
            ->select(DB::raw('DATE(starts_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $ordersByDay = Order::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $revenueByDay = Order::whereBetween('created_at', [$start, $end])
            ->where('is_paid', true)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total) as revenue'))
            ->groupBy('date')
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $stats = [];
        $current = $start->clone();
        $period = $end->diffInDays($current);
        $dateFormat = $period > 180 ? 'd.m.Y' : 'd.m';
        
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $displayDateKey = $current->format($dateFormat);
            
            $stats[$displayDateKey] = [
                'visits' => $visitsByDay->get($dateKey)->count ?? 0,
                'orders' => $ordersByDay->get($dateKey)->count ?? 0,
                'revenue' => $revenueByDay->get($dateKey)->revenue ?? 0,
            ];
            
            $current->addDay();
        }
        
        return $stats;
    }

    public function getTopServices($startDate)
    {
        $topServicesData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.item_type', Service::class)
            ->where('orders.is_paid', true)
            ->where('orders.created_at', '>=', $startDate)
            ->select('order_items.item_id', DB::raw('count(order_items.id) as count'), DB::raw('sum(order_items.quantity * order_items.unit_price) as revenue'))
            ->groupBy('order_items.item_id')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        $serviceIds = $topServicesData->pluck('item_id');
        $services = Service::find($serviceIds)->keyBy('id');

        return $topServicesData->map(function($item) use ($services) {
            return [
                'service' => $services->get($item->item_id),
                'count' => (int) $item->count,
                'revenue' => (float) $item->revenue,
            ];
        });
    }

    public function getRevenueData($startDate)
    {
        return Order::where('created_at', '>=', $startDate)
            ->where('is_paid', true)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date');
    }

    /**
     * Получить все метрики конверсии
     */
    public function getConversionMetrics($startDate, $endDate)
    {
        $conversionService = new ConversionStatisticsService();
        return $conversionService->getAllConversionMetrics($startDate, $endDate);
    }

    /**
     * Экспорт метрик дашборда
     */
    public function exportMetrics($startDate, $endDate, $format = 'excel')
    {
        try {
            $metrics = $this->getMetrics($startDate, $endDate);
            $periodStats = $this->getPeriodStats($startDate, $endDate);
            $topServices = $this->getTopServices($startDate);
            
            // Форматируем основные метрики
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество визитов',
                    'Значение' => $metrics['total_visits'],
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество заказов',
                    'Значение' => $metrics['total_orders'],
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общая выручка',
                    'Значение' => number_format($metrics['total_revenue'], 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество услуг',
                    'Значение' => $metrics['total_services'],
                    'Период' => 'Всего в системе'
                ],
                [
                    'Показатель' => 'Общее количество ветеринаров',
                    'Значение' => $metrics['total_veterinarians'],
                    'Период' => 'Всего в системе'
                ],
                [
                    'Показатель' => 'Конверсия визитов в заказы',
                    'Значение' => number_format($metrics['conversion_rate'], 2) . '%',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Визиты с заказами',
                    'Значение' => $metrics['visits_with_orders'],
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем статистику по дням
            $formattedPeriodStats = [];
            foreach ($periodStats as $date => $stats) {
                $formattedPeriodStats[] = [
                    'Дата' => $date,
                    'Визиты' => $stats['visits'],
                    'Заказы' => $stats['orders'],
                    'Выручка' => number_format($stats['revenue'], 2, ',', ' ') . ' руб.'
                ];
            }
            
            // Форматируем топ услуг
            $formattedTopServices = [];
            foreach ($topServices as $service) {
                $formattedTopServices[] = [
                    'Услуга' => $service['service'] ? $service['service']->name : 'Неизвестно',
                    'Количество заказов' => $service['count'],
                    'Выручка' => number_format($service['revenue'], 2, ',', ' ') . ' руб.'
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedPeriodStats, $formattedTopServices);
            
            $filename = app(ExportService::class)->generateFilename('dashboard_metrics', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте метрик дашборда', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт детальной статистики по дням
     */
    public function exportPeriodStats($startDate, $endDate, $format = 'excel')
    {
        try {
            $periodStats = $this->getPeriodStats($startDate, $endDate);
            
            $formattedData = [];
            foreach ($periodStats as $date => $stats) {
                $formattedData[] = [
                    'Дата' => $date,
                    'Количество визитов' => $stats['visits'],
                    'Количество заказов' => $stats['orders'],
                    'Выручка (руб.)' => $stats['revenue'],
                    'Средний чек' => $stats['orders'] > 0 ? number_format($stats['revenue'] / $stats['orders'], 2, ',', ' ') : '0,00'
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('period_stats', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте статистики по периодам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт топ услуг
     */
    public function exportTopServices($startDate, $endDate, $format = 'excel')
    {
        try {
            $topServices = $this->getTopServices($startDate);
            
            $formattedData = [];
            foreach ($topServices as $service) {
                $formattedData[] = [
                    'Услуга' => $service['service'] ? $service['service']->name : 'Неизвестно',
                    'Описание' => $service['service'] ? $service['service']->description : '',
                    'Количество заказов' => $service['count'],
                    'Выручка (руб.)' => $service['revenue'],
                    'Средняя цена' => $service['count'] > 0 ? number_format($service['revenue'] / $service['count'], 2, ',', ' ') : '0,00',
                    'Период' => $startDate . ' - ' . $endDate
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('top_services', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте топ услуг', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
