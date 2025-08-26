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
        $branches = Branch::select(['id', 'name', 'address'])->get();

        $visitsQuery = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->join('schedules', 'visits.schedule_id', '=', 'schedules.id')
            ->join('branch_employee', 'schedules.veterinarian_id', '=', 'branch_employee.employee_id');

        $visitsInBranch = (clone $visitsQuery)
            ->select('branch_employee.branch_id', DB::raw('count(visits.id) as visits_count'))
            ->groupBy('branch_employee.branch_id')
            ->get()
            ->keyBy('branch_id');

        $ordersInBranch = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('branch_id', DB::raw('count(id) as orders_count'))
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $visitsWithOrdersInBranch = (clone $visitsQuery)
            ->whereHas('orders')
            ->select('branch_employee.branch_id', DB::raw('count(visits.id) as visits_with_orders_count'))
            ->groupBy('branch_employee.branch_id')
            ->get()
            ->keyBy('branch_id');

        $conversionData = [];

        foreach ($branches as $branch) {
            $visitsCount = $visitsInBranch->get($branch->id)->visits_count ?? 0;
            $ordersCount = $ordersInBranch->get($branch->id)->orders_count ?? 0;
            $visitsWithOrdersCount = $visitsWithOrdersInBranch->get($branch->id)->visits_with_orders_count ?? 0;

            $conversionRate = $visitsCount > 0 ? round(($visitsWithOrdersCount / $visitsCount) * 100, 1) : 0;

            $conversionData[] = [
                'branch' => $branch,
                'visits_count' => $visitsCount,
                'orders_count' => $ordersCount,
                'visits_with_orders' => $visitsWithOrdersCount,
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
        $clientsInPeriod = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct()
            ->pluck('client_id');

        if ($clientsInPeriod->isEmpty()) {
            return [];
        }

        $regularClientIds = Order::whereIn('client_id', $clientsInPeriod)
            ->where('created_at', '<', $startDate)
            ->distinct()
            ->pluck('client_id');

        $newClientIds = $clientsInPeriod->diff($regularClientIds);

        $conversionData = [];

        if ($regularClientIds->isNotEmpty()) {
            $regularVisits = Visit::whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $regularClientIds)
                ->count();
            
            $regularVisitsWithOrders = Visit::whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $regularClientIds)
                ->whereHas('orders')
                ->count();
            
            $conversionData[] = [
                'client_type' => 'Постоянные клиенты',
                'visits_count' => $regularVisits,
                'visits_with_orders' => $regularVisitsWithOrders,
                'conversion_rate' => $regularVisits > 0 ? round(($regularVisitsWithOrders / $regularVisits) * 100, 1) : 0,
                'clients_count' => $regularClientIds->count(),
            ];
        }

        if ($newClientIds->isNotEmpty()) {
            $newVisits = Visit::whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $newClientIds)
                ->count();
            
            $newVisitsWithOrders = Visit::whereBetween('starts_at', [$startDate, $endDate])
                ->whereIn('client_id', $newClientIds)
                ->whereHas('orders')
                ->count();
            
            $conversionData[] = [
                'client_type' => 'Новые клиенты',
                'visits_count' => $newVisits,
                'visits_with_orders' => $newVisitsWithOrders,
                'conversion_rate' => $newVisits > 0 ? round(($newVisitsWithOrders / $newVisits) * 100, 1) : 0,
                'clients_count' => $newClientIds->count(),
            ];
        }

        return $conversionData;
    }

    /**
     * Конверсия по ветеринарам
     */
    public function getConversionByVeterinarians($startDate, $endDate)
    {
        $veterinarians = Employee::select(['id', 'name', 'email'])->has('roles')->get();

        $visitsQuery = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->join('schedules', 'visits.schedule_id', '=', 'schedules.id');

        $visitsByVet = (clone $visitsQuery)
            ->select('schedules.veterinarian_id', DB::raw('count(visits.id) as visits_count'))
            ->groupBy('schedules.veterinarian_id')
            ->get()
            ->keyBy('veterinarian_id');

        $visitsWithOrdersByVet = (clone $visitsQuery)
            ->whereHas('orders')
            ->select('schedules.veterinarian_id', DB::raw('count(visits.id) as visits_with_orders_count'))
            ->groupBy('schedules.veterinarian_id')
            ->get()
            ->keyBy('veterinarian_id');

        $conversionData = [];

        foreach ($veterinarians as $veterinarian) {
            $visitsCount = $visitsByVet->get($veterinarian->id)->visits_count ?? 0;
            $visitsWithOrdersCount = $visitsWithOrdersByVet->get($veterinarian->id)->visits_with_orders_count ?? 0;

            $conversionRate = $visitsCount > 0 ? round(($visitsWithOrdersCount / $visitsCount) * 100, 1) : 0;

            $conversionData[] = [
                'veterinarian' => $veterinarian,
                'visits_count' => $visitsCount,
                'visits_with_orders' => $visitsWithOrdersCount,
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
        $statuses = Status::select(['id', 'name', 'color'])->get();
        $visitsQuery = Visit::whereBetween('starts_at', [$startDate, $endDate]);

        $visitsByStatus = (clone $visitsQuery)
            ->select('status_id', DB::raw('count(id) as visits_count'))
            ->groupBy('status_id')
            ->get()
            ->keyBy('status_id');

        $visitsWithOrdersByStatus = (clone $visitsQuery)
            ->whereHas('orders')
            ->select('status_id', DB::raw('count(id) as visits_with_orders_count'))
            ->groupBy('status_id')
            ->get()
            ->keyBy('status_id');

        $conversionData = [];

        foreach ($statuses as $status) {
            $visitsCount = $visitsByStatus->get($status->id)->visits_count ?? 0;
            $visitsWithOrdersCount = $visitsWithOrdersByStatus->get($status->id)->visits_with_orders_count ?? 0;

            $conversionRate = $visitsCount > 0 ? round(($visitsWithOrdersCount / $visitsCount) * 100, 1) : 0;

            $conversionData[] = [
                'status' => $status,
                'visits_count' => $visitsCount,
                'visits_with_orders' => $visitsWithOrdersCount,
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
            'morning' => ['name' => 'Утро (6:00-12:00)'],
            'afternoon' => ['name' => 'День (12:00-18:00)'],
            'evening' => ['name' => 'Вечер (18:00-24:00)'],
            'night' => ['name' => 'Ночь (0:00-6:00)'],
        ];
        
        $visitsQuery = Visit::whereBetween('starts_at', [$startDate, $endDate]);

        $selectCase = DB::raw("CASE 
            WHEN HOUR(starts_at) >= 6 AND HOUR(starts_at) < 12 THEN 'morning'
            WHEN HOUR(starts_at) >= 12 AND HOUR(starts_at) < 18 THEN 'afternoon'
            WHEN HOUR(starts_at) >= 18 THEN 'evening'
            ELSE 'night' 
        END as time_slot");

        $visitsByTimeSlot = (clone $visitsQuery)
            ->select($selectCase, DB::raw('count(id) as visits_count'))
            ->groupBy('time_slot')
            ->get()
            ->keyBy('time_slot');

        $visitsWithOrdersByTimeSlot = (clone $visitsQuery)
            ->whereHas('orders')
            ->select($selectCase, DB::raw('count(id) as visits_with_orders_count'))
            ->groupBy('time_slot')
            ->get()
            ->keyBy('time_slot');

        $conversionData = [];
        
        foreach ($timeSlots as $slotKey => $slot) {
            $visitsCount = $visitsByTimeSlot->get($slotKey)->visits_count ?? 0;
            $visitsWithOrdersCount = $visitsWithOrdersByTimeSlot->get($slotKey)->visits_with_orders_count ?? 0;
            
            $conversionRate = $visitsCount > 0 ? round(($visitsWithOrdersCount / $visitsCount) * 100, 1) : 0;
            
            $conversionData[] = [
                'time_slot' => $slot['name'],
                'visits_count' => $visitsCount,
                'visits_with_orders' => $visitsWithOrdersCount,
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
        $visitsByWeekday = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->select(DB::raw('DAYOFWEEK(starts_at) as weekday'), DB::raw('count(id) as visits_count'))
            ->groupBy('weekday')
            ->get()
            ->keyBy('weekday');

        $visitsWithOrdersByWeekday = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('orders')
            ->select(DB::raw('DAYOFWEEK(starts_at) as weekday'), DB::raw('count(id) as visits_with_orders_count'))
            ->groupBy('weekday')
            ->get()
            ->keyBy('weekday');

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
            $visitsCount = $visitsByWeekday->get($dayNumber)->visits_count ?? 0;
            $visitsWithOrdersCount = $visitsWithOrdersByWeekday->get($dayNumber)->visits_with_orders_count ?? 0;

            $conversionRate = $visitsCount > 0 ? round(($visitsWithOrdersCount / $visitsCount) * 100, 1) : 0;

            $conversionData[] = [
                'weekday' => $dayName,
                'visits_count' => $visitsCount,
                'visits_with_orders' => $visitsWithOrdersCount,
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
