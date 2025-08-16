<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use Carbon\Carbon;

class DashboardDataService
{
    /**
     * Получить сегодняшние приёмы
     */
    public function getTodayVisits()
    {
        return Visit::whereDate('starts_at', Carbon::today())
            ->with(['pet.client', 'schedule.veterinarian', 'status'])
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Получить ближайшие приёмы (завтра)
     */
    public function getTomorrowVisits($limit = 5)
    {
        return Visit::whereDate('starts_at', Carbon::tomorrow())
            ->with(['pet.client', 'schedule.veterinarian', 'status'])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последние заказы
     */
    public function getRecentOrders($limit = 5)
    {
        return Order::with(['client', 'pet', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
