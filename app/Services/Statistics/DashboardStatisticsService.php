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

    /**
     * Получить все метрики конверсии
     */
    public function getConversionMetrics($startDate, $endDate)
    {
        $conversionService = new ConversionStatisticsService();
        return $conversionService->getAllConversionMetrics($startDate, $endDate);
    }
}
