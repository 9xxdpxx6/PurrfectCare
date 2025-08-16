<?php

namespace App\Services\Statistics;

use App\Models\User;
use App\Models\Pet;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Visit;
use App\Models\Order;
use Carbon\Carbon;

class DashboardMetricsService
{
    /**
     * Получить дополнительные метрики для главной страницы
     */
    public function getAdditionalMetrics($startDate, $endDate, $totalOrders, $totalRevenue)
    {
        return [
            'total_clients' => $this->getTotalClients($startDate, $endDate),
            'total_pets' => $this->getTotalPets($startDate, $endDate),
            'total_branches' => $this->getTotalBranches(),
            'total_employees' => $this->getTotalEmployees(),
            'average_order' => $this->calculateAverageOrder($totalOrders, $totalRevenue),
            'conversion_rate' => $this->calculateConversionRate($startDate, $endDate),
        ];
    }

    /**
     * Получить общее количество клиентов за период
     */
    private function getTotalClients($startDate, $endDate)
    {
        return User::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Получить общее количество питомцев за период
     */
    private function getTotalPets($startDate, $endDate)
    {
        return Pet::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Получить общее количество филиалов
     */
    private function getTotalBranches()
    {
        return Branch::count();
    }

    /**
     * Получить общее количество сотрудников
     */
    private function getTotalEmployees()
    {
        return Employee::count();
    }

    /**
     * Рассчитать средний чек
     */
    private function calculateAverageOrder($totalOrders, $totalRevenue)
    {
        return $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;
    }

    /**
     * Рассчитать конверсию приёмов в заказы
     */
    private function calculateConversionRate($startDate, $endDate)
    {
        $totalVisits = Visit::whereBetween('starts_at', [$startDate, $endDate])->count();
        
        if ($totalVisits === 0) {
            return 0;
        }
        
        // Считаем количество уникальных визитов, которые связаны с заказами
        $visitsWithOrders = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('orders')
            ->count();
        
        return round(($visitsWithOrders / $totalVisits) * 100, 1);
    }
}
