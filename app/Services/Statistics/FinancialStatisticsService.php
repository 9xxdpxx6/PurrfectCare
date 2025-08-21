<?php

namespace App\Services\Statistics;

use App\Models\Order;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTest;
use App\Models\VaccinationType;
use App\Models\Branch;
use Carbon\Carbon;

class FinancialStatisticsService
{
    /**
     * Получить общее количество заказов за период
     */
    public function getTotalOrders($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['id'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->count();
    }

    /**
     * Получить общую выручку за период
     */
    public function getTotalRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['total'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->sum('total');
    }

    public function getCategoryRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        $orders = Order::select(['id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['items:id,order_id,item_type,item_id,quantity,unit_price'])
            ->get();
        
        $categories = [
            'services' => 0,
            'drugs' => 0,
            'lab_tests' => 0,
            'vaccinations' => 0,
        ];
        
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $type = class_basename($item->item_type);
                switch ($type) {
                    case 'Service':
                        $categories['services'] += $item->quantity * $item->unit_price;
                        break;
                    case 'Drug':
                        $categories['drugs'] += $item->quantity * $item->unit_price;
                        break;
                    case 'LabTest':
                        $categories['lab_tests'] += $item->quantity * $item->unit_price;
                        break;
                    case 'VaccinationType':
                        $categories['vaccinations'] += $item->quantity * $item->unit_price;
                        break;
                }
            }
        }
        
        return $categories;
    }

    public function getBranchRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at, is_paid и branch_id, select для выбора только нужных полей
        return Order::select(['id', 'branch_id', 'total', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['branch:id,name,address'])
            ->get()
            ->groupBy('branch_id')
            ->map(function($orders, $branchId) {
                $branch = $orders->first()->branch;
                return [
                    'branch' => $branch,
                    'revenue' => $orders->sum('total'),
                    'orders_count' => $orders->count(),
                ];
            })
            ->sortByDesc('revenue');
    }

    public function getRevenueData($startDate, $endDate)
    {
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
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

    public function getTopServices($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
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
            ->sortByDesc('revenue')
            ->take(10);
    }
}
