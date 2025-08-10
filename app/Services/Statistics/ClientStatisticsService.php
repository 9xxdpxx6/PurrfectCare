<?php

namespace App\Services\Statistics;

use App\Models\User;
use App\Models\Pet;
use App\Models\Order;
use Carbon\Carbon;

class ClientStatisticsService
{
    public function getClientsData($startDate, $endDate)
    {
        // Получаем всех клиентов, которые делали заказы в выбранном периоде
        $clientsWithOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->with('client')
            ->get()
            ->groupBy('client_id');
        
        $newClients = 0;
        $repeatClients = 0;
        $newClientsRevenue = 0;
        $repeatClientsRevenue = 0;
        
        foreach ($clientsWithOrders as $clientId => $orders) {
            $client = $orders->first()->client;
            // Считаем доход только с оплаченных заказов
            $totalClientRevenue = $orders->where('is_paid', true)->sum('total');
            
            // Проверяем, есть ли у клиента заказы до выбранного периода
            $hasOrdersBeforePeriod = Order::where('client_id', $clientId)
                ->where('created_at', '<', $startDate)
                ->exists();
            
            if ($hasOrdersBeforePeriod) {
                // У клиента были заказы до выбранного периода - постоянный клиент
                $repeatClients++;
                $repeatClientsRevenue += $totalClientRevenue;
            } else {
                // Первый раз заказывает в выбранном периоде - новый клиент
                $newClients++;
                $newClientsRevenue += $totalClientRevenue;
            }
        }
        
        $totalClients = $newClients + $repeatClients;
        $totalForPercentage = $totalClients;
        $newClientsPercentage = $totalForPercentage > 0 ? round(($newClients / $totalForPercentage) * 100, 1) : 0;
        $repeatClientsPercentage = $totalForPercentage > 0 ? round(($repeatClients / $totalForPercentage) * 100, 1) : 0;
        
        // Общий доход за период
        $totalRevenue = $newClientsRevenue + $repeatClientsRevenue;
        $newClientsRevenuePercentage = $totalRevenue > 0 ? round(($newClientsRevenue / $totalRevenue) * 100, 1) : 0;
        $repeatClientsRevenuePercentage = $totalRevenue > 0 ? round(($repeatClientsRevenue / $totalRevenue) * 100, 1) : 0;
        

        
        return [
            'new_clients' => $newClients,
            'repeat_clients' => $repeatClients,
            'total_clients' => $totalClients,
            'new_clients_percentage' => $newClientsPercentage,
            'repeat_clients_percentage' => $repeatClientsPercentage,
            'new_clients_revenue' => $newClientsRevenue,
            'repeat_clients_revenue' => $repeatClientsRevenue,
            'total_revenue' => $totalRevenue,
            'new_clients_revenue_percentage' => $newClientsRevenuePercentage,
            'repeat_clients_revenue_percentage' => $repeatClientsRevenuePercentage,
        ];
    }

    public function getPetsData($startDate, $endDate)
    {
        $pets = Pet::whereHas('visits', function($query) use ($startDate, $endDate) {
            $query->whereBetween('starts_at', [$startDate, $endDate]);
        })->with('breed.species')->get();

        // Группируем по видам животных
        $bySpecies = $pets->groupBy(function($pet) {
            return $pet->breed && $pet->breed->species ? $pet->breed->species->name : 'Неизвестный вид';
        })->map(function($speciesPets) {
            // Для каждого вида группируем по породам
            return $speciesPets->groupBy(function($pet) {
                return $pet->breed ? $pet->breed->name : 'Неизвестная порода';
            })->map->count()->sortByDesc(function($count) {
                return $count;
            });
        })->sortByDesc(function($speciesData) {
            return $speciesData->sum();
        });

        // Получаем топ-3 вида
        $topSpecies = $bySpecies->take(3);

        return [
            'total_pets' => $pets->count(),
            'by_breed' => $pets->groupBy(function($pet) {
                return $pet->breed ? $pet->breed->name : 'Неизвестная порода';
            })->map->count()->sortByDesc(function($count) {
                return $count;
            }),
            'by_species' => $topSpecies,
        ];
    }

    public function getTopClients($startDate, $endDate)
    {
        return User::whereHas('orders', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->with(['orders' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()
        ->map(function($user) {
            // Фильтруем только оплаченные заказы для подсчета доходов
            $paidOrders = $user->orders->where('is_paid', true);
            return [
                'user' => $user,
                'orders_count' => $user->orders->count(),
                'total_spent' => $paidOrders->sum('total'),
                'average_order' => $paidOrders->count() > 0 ? round($paidOrders->sum('total') / $paidOrders->count(), 0) : 0,
            ];
        })
        ->sortByDesc('total_spent')
        ->take(10);
    }
}
