<?php

namespace App\Services\Statistics;

use App\Models\Visit;
use App\Models\Order;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConversionStatisticsService
{
    /**
     * Общая конверсия приёмов в заказы
     */
    public function getOverallConversion($startDate, $endDate)
    {
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $totalVisits = Visit::select(['id'])->whereBetween('starts_at', [$startDate, $endDate])->count();
        
        // Оптимизация: используем индекс на created_at и select для выбора только нужных полей
        $totalOrders = Order::select(['id'])->whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Приёмы, которые привели к заказам
        // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
        $visitsWithOrders = Visit::select(['id'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('orders')
            ->count();
        
        $conversionRate = $totalVisits > 0 ? round(($visitsWithOrders / $totalVisits) * 100, 1) : 0;
        
        return [
            'total_visits' => $totalVisits,
            'total_orders' => $totalOrders,
            'visits_with_orders' => $visitsWithOrders,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Конверсия по филиалам
     */
    public function getConversionByBranches($startDate, $endDate)
    {
        // Оптимизация: используем select для выбора только нужных полей
        $branches = Branch::select(['id', 'name', 'address'])->get();
        $conversionData = [];
        
        foreach ($branches as $branch) {
            // Приёмы в филиале
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsInBranch = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereHas('schedule.veterinarian.branches', function($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })
                ->count();
            
            // Заказы в филиале
            // Оптимизация: используем индексы на created_at и branch_id, select для выбора только нужных полей
            $ordersInBranch = Order::select(['id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('branch_id', $branch->id)
                ->count();
            
            // Приёмы в филиале, которые привели к заказам
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsWithOrdersInBranch = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereHas('schedule.veterinarian.branches', function($query) use ($branch) {
                    $query->where('branch_id', $branch->id);
                })
                ->whereHas('orders')
                ->count();
            
            $conversionRate = $visitsInBranch > 0 ? round(($visitsWithOrdersInBranch / $visitsInBranch) * 100, 1) : 0;
            
            $conversionData[] = [
                'branch' => $branch,
                'visits_count' => $visitsInBranch,
                'orders_count' => $ordersInBranch,
                'visits_with_orders' => $visitsWithOrdersInBranch,
                'conversion_rate' => $conversionRate,
            ];
        }
        
        // Сортируем по конверсии (по убыванию)
        usort($conversionData, function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });
        
        return $conversionData;
    }

    /**
     * Конверсия по типам клиентов (постоянные vs новые)
     */
    public function getConversionByClientTypes($startDate, $endDate)
    {
        // Получаем всех клиентов, которые делали заказы в выбранном периоде
        // Оптимизация: используем индексы на created_at и client_id, select для выбора только нужных полей
        $clientsWithOrders = Order::select(['id', 'client_id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['client:id,name,email'])
            ->get()
            ->groupBy('client_id');
        
        $regularClients = [];
        $newClients = [];
        
        foreach ($clientsWithOrders as $clientId => $orders) {
            // Проверяем, есть ли у клиента заказы до выбранного периода
            // Оптимизация: используем индекс на client_id и created_at, select для выбора только нужных полей
            $hasOrdersBeforePeriod = Order::select(['id'])
                ->where('client_id', $clientId)
                ->where('created_at', '<', $startDate)
                ->exists();
            
            if ($hasOrdersBeforePeriod) {
                // У клиента были заказы до выбранного периода - постоянный клиент
                $regularClients[] = $clientId;
            } else {
                // Первый раз заказывает в выбранном периоде - новый клиент
                $newClients[] = $clientId;
            }
        }
        
        $conversionData = [];
        
        // Конверсия для постоянных клиентов
        if (!empty($regularClients)) {
            // Оптимизация: используем индексы на starts_at и client_id, select для выбора только нужных полей
            $regularVisits = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $regularClients)
                ->count();
            
            $regularVisitsWithOrders = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $regularClients)
                ->whereHas('orders')
                ->count();
            
            $conversionData[] = [
                'client_type' => 'Постоянные клиенты',
                'visits_count' => $regularVisits,
                'visits_with_orders' => $regularVisitsWithOrders,
                'conversion_rate' => $regularVisits > 0 ? round(($regularVisitsWithOrders / $regularVisits) * 100, 1) : 0,
                'clients_count' => count($regularClients),
            ];
        }
        
        // Конверсия для новых клиентов
        if (!empty($newClients)) {
            // Оптимизация: используем индексы на starts_at и client_id, select для выбора только нужных полей
            $newVisits = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $newClients)
                ->count();
            
            $newVisitsWithOrders = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $newClients)
                ->whereHas('orders')
                ->count();
            
            $conversionData[] = [
                'client_type' => 'Новые клиенты',
                'visits_count' => $newVisits,
                'visits_with_orders' => $newVisitsWithOrders,
                'conversion_rate' => $newVisits > 0 ? round(($newVisitsWithOrders / $newVisits) * 100, 1) : 0,
                'clients_count' => count($newClients),
            ];
        }
        
        return $conversionData;
    }

    /**
     * Конверсия по ветеринарам
     */
    public function getConversionByVeterinarians($startDate, $endDate)
    {
        // Оптимизация: используем select для выбора только нужных полей
        $veterinarians = Employee::select(['id', 'name', 'email'])->get();
        $conversionData = [];
        
        foreach ($veterinarians as $veterinarian) {
            // Приёмы у данного ветеринара
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsByVet = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereHas('schedule', function($query) use ($veterinarian) {
                    $query->where('veterinarian_id', $veterinarian->id);
                })
                ->count();
            
            // Приёмы у ветеринара, которые привели к заказам
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsWithOrdersByVet = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereHas('schedule', function($query) use ($veterinarian) {
                    $query->where('veterinarian_id', $veterinarian->id);
                })
                ->whereHas('orders')
                ->count();
            
            $conversionRate = $visitsByVet > 0 ? round(($visitsWithOrdersByVet / $visitsByVet) * 100, 1) : 0;
            
            $conversionData[] = [
                'veterinarian' => $veterinarian,
                'visits_count' => $visitsByVet,
                'visits_with_orders' => $visitsWithOrdersByVet,
                'conversion_rate' => $conversionRate,
            ];
        }
        
        // Сортируем по конверсии (по убыванию)
        usort($conversionData, function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });
        
        return $conversionData;
    }

    /**
     * Конверсия по статусам приёмов
     */
    public function getConversionByVisitStatuses($startDate, $endDate)
    {
        // Оптимизация: используем select для выбора только нужных полей
        $statuses = Status::select(['id', 'name'])->get();
        $conversionData = [];
        
        foreach ($statuses as $status) {
            // Приёмы с данным статусом
            // Оптимизация: используем индексы на starts_at и status_id, select для выбора только нужных полей
            $visitsWithStatus = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->where('status_id', $status->id)
                ->count();
            
            // Приёмы с данным статусом, которые привели к заказам
            // Оптимизация: используем индексы на starts_at и status_id, select для выбора только нужных полей
            $visitsWithStatusAndOrders = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->where('status_id', $status->id)
                ->whereHas('orders')
                ->count();
            
            $conversionRate = $visitsWithStatus > 0 ? round(($visitsWithStatusAndOrders / $visitsWithStatus) * 100, 1) : 0;
            
            $conversionData[] = [
                'status' => $status,
                'visits_count' => $visitsWithStatus,
                'visits_with_orders' => $visitsWithStatusAndOrders,
                'conversion_rate' => $conversionRate,
            ];
        }
        
        // Сортируем по конверсии (по убыванию)
        usort($conversionData, function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });
        
        return $conversionData;
    }

    /**
     * Конверсия по времени суток
     */
    public function getConversionByTimeOfDay($startDate, $endDate)
    {
        $timeSlots = [
            'morning' => ['start' => 6, 'end' => 12, 'name' => 'Утро (6:00-12:00)'],
            'afternoon' => ['start' => 12, 'end' => 18, 'name' => 'День (12:00-18:00)'],
            'evening' => ['start' => 18, 'end' => 24, 'name' => 'Вечер (18:00-24:00)'],
            'night' => ['start' => 0, 'end' => 6, 'name' => 'Ночь (0:00-6:00)'],
        ];
        
        $conversionData = [];
        
        foreach ($timeSlots as $slot) {
            // Приёмы в данном временном слоте
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsInTimeSlot = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereRaw('HOUR(starts_at) >= ? AND HOUR(starts_at) < ?', [$slot['start'], $slot['end']])
                ->count();
            
            // Приёмы в данном временном слоте, которые привели к заказам
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsWithOrdersInTimeSlot = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereRaw('HOUR(starts_at) >= ? AND HOUR(starts_at) < ?', [$slot['start'], $slot['end']])
                ->whereHas('orders')
                ->count();
            
            $conversionRate = $visitsInTimeSlot > 0 ? round(($visitsWithOrdersInTimeSlot / $visitsInTimeSlot) * 100, 1) : 0;
            
            $conversionData[] = [
                'time_slot' => $slot['name'],
                'visits_count' => $visitsInTimeSlot,
                'visits_with_orders' => $visitsWithOrdersInTimeSlot,
                'conversion_rate' => $conversionRate,
            ];
        }
        
        return $conversionData;
    }

    /**
     * Конверсия по дням недели
     */
    public function getConversionByWeekdays($startDate, $endDate)
    {
        $weekdays = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье'
        ];
        
        $conversionData = [];
        
        foreach ($weekdays as $dayNumber => $dayName) {
            // Приёмы в данный день недели
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsOnWeekday = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereRaw('DAYOFWEEK(starts_at) = ?', [$dayNumber])
                ->count();
            
            // Приёмы в данный день недели, которые привели к заказам
            // Оптимизация: используем индекс на starts_at и select для выбора только нужных полей
            $visitsWithOrdersOnWeekday = Visit::select(['id'])
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->whereRaw('DAYOFWEEK(starts_at) = ?', [$dayNumber])
                ->whereHas('orders')
                ->count();
            
            $conversionRate = $visitsOnWeekday > 0 ? round(($visitsWithOrdersOnWeekday / $visitsOnWeekday) * 100, 1) : 0;
            
            $conversionData[] = [
                'weekday' => $dayName,
                'visits_count' => $visitsOnWeekday,
                'visits_with_orders' => $visitsWithOrdersOnWeekday,
                'conversion_rate' => $conversionRate,
            ];
        }
        
        return $conversionData;
    }

    /**
     * Получить все виды конверсии для дашборда
     */
    public function getAllConversionMetrics($startDate, $endDate)
    {
        return [
            'overall' => $this->getOverallConversion($startDate, $endDate),
            'by_branches' => $this->getConversionByBranches($startDate, $endDate),
            'by_client_types' => $this->getConversionByClientTypes($startDate, $endDate),
            'by_veterinarians' => $this->getConversionByVeterinarians($startDate, $endDate),
            'by_visit_statuses' => $this->getConversionByVisitStatuses($startDate, $endDate),
            'by_time_of_day' => $this->getConversionByTimeOfDay($startDate, $endDate),
            'by_weekdays' => $this->getConversionByWeekdays($startDate, $endDate),
        ];
    }
}
