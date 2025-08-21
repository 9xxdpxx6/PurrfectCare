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
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return User::select(['id'])->whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Получить общее количество питомцев за период
     */
    private function getTotalPets($startDate, $endDate)
    {
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        return Pet::select(['id'])->whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Получить общее количество филиалов
     */
    private function getTotalBranches()
    {
        // Оптимизация: используем select для выбора только нужных полей
        return Branch::select(['id'])->count();
    }

    /**
     * Получить общее количество сотрудников
     */
    private function getTotalEmployees()
    {
        // Оптимизация: используем select для выбора только нужных полей
        return Employee::select(['id'])->count();
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
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $totalVisits = Visit::select(['id'])->whereBetween('starts_at', [$startDate, $endDate])->count();
        
        if ($totalVisits === 0) {
            return 0;
        }
        
        // Считаем количество уникальных приёмов, которые связаны с заказами
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $visitsWithOrders = Visit::select(['id'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('orders')
            ->count();
        
        return round(($visitsWithOrders / $totalVisits) * 100, 1);
    }
}
