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
        $stats = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Определяем формат даты в зависимости от периода
        $period = $end->diffInDays($current);
        $dateFormat = $period > 180 ? 'd.m.Y' : 'd.m';
        
        while ($current <= $end) {
            $dateKey = $current->format($dateFormat);
            
            $stats[$dateKey] = [
                // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
                'visits' => Visit::select(['id'])->whereDate('starts_at', $current)->count(),
                
                // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
                'orders' => Order::select(['id'])->whereDate('created_at', $current)->count(),
                
                // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
                'revenue' => Order::select(['total'])
                    ->whereDate('created_at', $current)
                    ->where('is_paid', true) // Только оплаченные заказы
                    ->sum('total'),
            ];
            
            $current->addDay();
        }
        
        return $stats;
    }

    public function getTopServices($startDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['id', 'created_at'])
            ->where('created_at', '>=', $startDate)
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['items' => function($query) {
                $query->select(['id', 'order_id', 'item_type', 'item_id', 'quantity', 'unit_price'])
                    ->where('item_type', Service::class);
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
            // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
            $data[$current->format('Y-m-d')] = Order::select(['total'])
                ->whereDate('created_at', $current)
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
