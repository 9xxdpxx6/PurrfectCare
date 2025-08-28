<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;
use Carbon\Carbon;

class DashboardDataService
{
    /**
     * Получить сегодняшние приёмы (последние 10)
     */
    public function getTodayVisits($limit = 10)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        return Visit::select(['id', 'starts_at', 'pet_id', 'schedule_id', 'status_id'])
            ->whereDate('starts_at', Carbon::today())
            ->with([
                'pet:id,name,client_id',
                'pet.client:id,name,email',
                'schedule:id,veterinarian_id',
                'schedule.veterinarian:id,name',
                'status:id,name,color'
            ])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить ближайшие приёмы (завтра)
     */
    public function getTomorrowVisits($limit = 10)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        return Visit::select(['id', 'starts_at', 'pet_id', 'schedule_id', 'status_id'])
            ->whereDate('starts_at', Carbon::tomorrow())
            ->with([
                'pet:id,name,client_id',
                'pet.client:id,name,email',
                'schedule:id,veterinarian_id',
                'schedule.veterinarian:id,name',
                'status:id,name,color'
            ])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последние заказы
     */
    public function getRecentOrders($limit = 10)
    {
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return Order::select(['id', 'client_id', 'pet_id', 'status_id', 'total', 'created_at'])
            ->with([
                'client:id,name,email',
                'pet:id,name',
                'status:id,name,color'
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
