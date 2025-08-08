<?php

namespace App\Services\Statistics;

use App\Models\Order;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTest;
use App\Models\Vaccination;
use App\Models\Branch;
use Carbon\Carbon;

class FinancialStatisticsService
{
    public function getCategoryRevenue($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->with('items.item')->get();
        
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
                    case 'Vaccination':
                        $categories['vaccinations'] += $item->quantity * $item->unit_price;
                        break;
                }
            }
        }
        
        return $categories;
    }

    public function getBranchRevenue($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->with('branch')
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
            $data[$current->format('Y-m-d')] = Order::whereDate('created_at', $current)->sum('total');
            $current->addDay();
        }
        
        return $data;
    }

    public function getTopServices($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
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
            ->sortByDesc('revenue')
            ->take(10);
    }
}
