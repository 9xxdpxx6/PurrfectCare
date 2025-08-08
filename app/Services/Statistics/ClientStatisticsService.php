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
        $newClients = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $repeatClients = User::whereHas('visits', function($query) use ($startDate, $endDate) {
            $query->whereBetween('starts_at', [$startDate, $endDate]);
        })->where('created_at', '<', $startDate)->count();
        $totalClients = User::whereHas('visits', function($query) use ($startDate, $endDate) {
            $query->whereBetween('starts_at', [$startDate, $endDate]);
        })->count();
        
        $totalForPercentage = $newClients + $repeatClients;
        $newClientsPercentage = $totalForPercentage > 0 ? round(($newClients / $totalForPercentage) * 100, 1) : 0;
        $repeatClientsPercentage = $totalForPercentage > 0 ? round(($repeatClients / $totalForPercentage) * 100, 1) : 0;
        
        return [
            'new_clients' => $newClients,
            'repeat_clients' => $repeatClients,
            'total_clients' => $totalClients,
            'new_clients_percentage' => $newClientsPercentage,
            'repeat_clients_percentage' => $repeatClientsPercentage,
        ];
    }

    public function getPetsData($startDate, $endDate)
    {
        return [
            'total_pets' => Pet::whereHas('visits', function($query) use ($startDate, $endDate) {
                $query->whereBetween('starts_at', [$startDate, $endDate]);
            })->count(),
            'by_breed' => Pet::whereHas('visits', function($query) use ($startDate, $endDate) {
                $query->whereBetween('starts_at', [$startDate, $endDate]);
            })->with('breed.species')->get()
                ->groupBy(function($pet) {
                    return $pet->breed ? $pet->breed->name : 'Неизвестная порода';
                })
                ->map->count()
                ->sortByDesc(function($count) {
                    return $count;
                }),
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
            return [
                'user' => $user,
                'orders_count' => $user->orders->count(),
                'total_spent' => $user->orders->sum('total'),
                'average_order' => $user->orders->count() > 0 ? round($user->orders->sum('total') / $user->orders->count(), 0) : 0,
            ];
        })
        ->sortByDesc('total_spent')
        ->take(10);
    }
}
