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
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

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

    /**
     * Экспорт данных конверсии - несколько листов
     */
    public function exportConversionData($startDate, $endDate, $format = 'excel')
    {
        try {
            $overallConversion = $this->getOverallConversion($startDate, $endDate);
            $conversionByBranches = $this->getConversionByBranches($startDate, $endDate);
            $conversionByClientTypes = $this->getConversionByClientTypes($startDate, $endDate);
            $conversionByVeterinarians = $this->getConversionByVeterinarians($startDate, $endDate);
            $conversionByVisitStatuses = $this->getConversionByVisitStatuses($startDate, $endDate);
            $conversionByTimeOfDay = $this->getConversionByTimeOfDay($startDate, $endDate);
            $conversionByWeekdays = $this->getConversionByWeekdays($startDate, $endDate);
            
            // Форматируем период
            $periodFormatted = Carbon::parse($startDate)->format('d.m.Y') . ' - ' . Carbon::parse($endDate)->format('d.m.Y');
            
            // Лист 1: Обзор
            $formattedOverall = [
                [
                    'Показатель' => 'Общее количество визитов',
                    'Значение' => $overallConversion['total_visits'],
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Общее количество заказов',
                    'Значение' => $overallConversion['total_orders'],
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Визиты с заказами',
                    'Значение' => $overallConversion['visits_with_orders'],
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Общая конверсия (%)',
                    'Значение' => $overallConversion['conversion_rate'] . '%',
                    'Период' => $periodFormatted
                ]
            ];
            
            // Лист 2: По филиалам
            $formattedBranches = [];
            $isFirstBranch = true;
            foreach ($conversionByBranches as $data) {
                $formattedBranches[] = [
                    'Филиал' => $data['branch'] ? $data['branch']->name : 'Неизвестно',
                    'Адрес' => $data['branch'] ? $data['branch']->address : '',
                    'Количество визитов' => $data['visits_count'],
                    'Количество заказов' => $data['orders_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstBranch ? $periodFormatted : ''
                ];
                $isFirstBranch = false;
            }
            
            // Лист 3: По типам клиентов
            $formattedClientTypes = [];
            $isFirstClientType = true;
            foreach ($conversionByClientTypes as $data) {
                $formattedClientTypes[] = [
                    'Тип клиента' => $data['client_type'],
                    'Количество клиентов' => $data['clients_count'],
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstClientType ? $periodFormatted : ''
                ];
                $isFirstClientType = false;
            }
            
            // Лист 4: По ветеринарам
            $formattedVeterinarians = [];
            $isFirstVeterinarian = true;
            foreach ($conversionByVeterinarians as $data) {
                $formattedVeterinarians[] = [
                    'Ветеринар' => $data['veterinarian'] ? $data['veterinarian']->name : 'Неизвестно',
                    'Email' => $data['veterinarian'] ? $data['veterinarian']->email : '',
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstVeterinarian ? $periodFormatted : ''
                ];
                $isFirstVeterinarian = false;
            }
            
            // Лист 5: По статусам визитов
            $formattedVisitStatuses = [];
            $isFirstStatus = true;
            foreach ($conversionByVisitStatuses as $data) {
                $formattedVisitStatuses[] = [
                    'Статус визита' => $data['status'] ? $data['status']->name : 'Неизвестно',
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstStatus ? $periodFormatted : ''
                ];
                $isFirstStatus = false;
            }
            
            // Лист 6: По времени суток
            $formattedTimeOfDay = [];
            $isFirstTimeSlot = true;
            foreach ($conversionByTimeOfDay as $data) {
                $formattedTimeOfDay[] = [
                    'Время суток' => $data['time_slot'],
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstTimeSlot ? $periodFormatted : ''
                ];
                $isFirstTimeSlot = false;
            }
            
            // Лист 7: По дням недели
            $formattedWeekdays = [];
            $isFirstWeekday = true;
            foreach ($conversionByWeekdays as $data) {
                $formattedWeekdays[] = [
                    'День недели' => $data['weekday'],
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstWeekday ? $periodFormatted : ''
                ];
                $isFirstWeekday = false;
            }
            
            // Подготавливаем данные для нескольких листов
            $sheetsData = [
                'Обзор' => [
                    'headers' => ['Показатель', 'Значение', 'Период'],
                    'data' => $formattedOverall
                ],
                'По филиалам' => [
                    'headers' => ['Филиал', 'Адрес', 'Количество визитов', 'Количество заказов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedBranches
                ],
                'По типам клиентов' => [
                    'headers' => ['Тип клиента', 'Количество клиентов', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedClientTypes
                ],
                'По ветеринарам' => [
                    'headers' => ['Ветеринар', 'Email', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedVeterinarians
                ],
                'По статусам визитов' => [
                    'headers' => ['Статус визита', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedVisitStatuses
                ],
                'По времени суток' => [
                    'headers' => ['Время суток', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedTimeOfDay
                ],
                'По дням недели' => [
                    'headers' => ['День недели', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedWeekdays
                ]
            ];
            
            $filename = app(ExportService::class)->generateFilename('conversion_data', 'xlsx');
            
            return app(ExportService::class)->toExcelMultipleSheets($sheetsData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте данных конверсии', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт конверсии по филиалам - несколько листов
     */
    public function exportConversionByBranches($startDate, $endDate, $format = 'excel')
    {
        try {
            $conversionByBranches = $this->getConversionByBranches($startDate, $endDate);
            
            // Форматируем период
            $periodFormatted = Carbon::parse($startDate)->format('d.m.Y') . ' - ' . Carbon::parse($endDate)->format('d.m.Y');
            
            // Лист 1: Обзор
            $totalVisits = collect($conversionByBranches)->sum('visits_count');
            $totalOrders = collect($conversionByBranches)->sum('orders_count');
            $totalVisitsWithOrders = collect($conversionByBranches)->sum('visits_with_orders');
            $overallConversionRate = $totalVisits > 0 ? round(($totalVisitsWithOrders / $totalVisits) * 100, 1) : 0;
            
            $formattedOverall = [
                [
                    'Показатель' => 'Общее количество визитов',
                    'Значение' => $totalVisits,
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Общее количество заказов',
                    'Значение' => $totalOrders,
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Визиты с заказами',
                    'Значение' => $totalVisitsWithOrders,
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Общая конверсия (%)',
                    'Значение' => $overallConversionRate . '%',
                    'Период' => $periodFormatted
                ]
            ];
            
            // Лист 2: По филиалам
            $formattedBranches = [];
            $isFirstBranch = true;
            foreach ($conversionByBranches as $data) {
                $formattedBranches[] = [
                    'Филиал' => $data['branch'] ? $data['branch']->name : 'Неизвестно',
                    'Адрес' => $data['branch'] ? $data['branch']->address : '',
                    'Количество визитов' => $data['visits_count'],
                    'Количество заказов' => $data['orders_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstBranch ? $periodFormatted : ''
                ];
                $isFirstBranch = false;
            }
            
            // Подготавливаем данные для нескольких листов
            $sheetsData = [
                'Обзор' => [
                    'headers' => ['Показатель', 'Значение', 'Период'],
                    'data' => $formattedOverall
                ],
                'По филиалам' => [
                    'headers' => ['Филиал', 'Адрес', 'Количество визитов', 'Количество заказов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedBranches
                ]
            ];
            
            $filename = app(ExportService::class)->generateFilename('conversion_by_branches', 'xlsx');
            
            return app(ExportService::class)->toExcelMultipleSheets($sheetsData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте конверсии по филиалам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт конверсии по ветеринарам - несколько листов
     */
    public function exportConversionByVeterinarians($startDate, $endDate, $format = 'excel')
    {
        try {
            $conversionByVeterinarians = $this->getConversionByVeterinarians($startDate, $endDate);
            
            // Форматируем период
            $periodFormatted = Carbon::parse($startDate)->format('d.m.Y') . ' - ' . Carbon::parse($endDate)->format('d.m.Y');
            
            // Лист 1: Обзор
            $totalVisits = collect($conversionByVeterinarians)->sum('visits_count');
            $totalVisitsWithOrders = collect($conversionByVeterinarians)->sum('visits_with_orders');
            $overallConversionRate = $totalVisits > 0 ? round(($totalVisitsWithOrders / $totalVisits) * 100, 1) : 0;
            
            $formattedOverall = [
                [
                    'Показатель' => 'Общее количество визитов',
                    'Значение' => $totalVisits,
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Визиты с заказами',
                    'Значение' => $totalVisitsWithOrders,
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Общая конверсия (%)',
                    'Значение' => $overallConversionRate . '%',
                    'Период' => $periodFormatted
                ],
                [
                    'Показатель' => 'Количество ветеринаров',
                    'Значение' => count($conversionByVeterinarians),
                    'Период' => $periodFormatted
                ]
            ];
            
            // Лист 2: По ветеринарам
            $formattedVeterinarians = [];
            $isFirstVeterinarian = true;
            foreach ($conversionByVeterinarians as $data) {
                $formattedVeterinarians[] = [
                    'Ветеринар' => $data['veterinarian'] ? $data['veterinarian']->name : 'Неизвестно',
                    'Email' => $data['veterinarian'] ? $data['veterinarian']->email : '',
                    'Количество визитов' => $data['visits_count'],
                    'Визиты с заказами' => $data['visits_with_orders'],
                    'Конверсия (%)' => $data['conversion_rate'] . '%',
                    'Период' => $isFirstVeterinarian ? $periodFormatted : ''
                ];
                $isFirstVeterinarian = false;
            }
            
            // Подготавливаем данные для нескольких листов
            $sheetsData = [
                'Обзор' => [
                    'headers' => ['Показатель', 'Значение', 'Период'],
                    'data' => $formattedOverall
                ],
                'По ветеринарам' => [
                    'headers' => ['Ветеринар', 'Email', 'Количество визитов', 'Визиты с заказами', 'Конверсия (%)', 'Период'],
                    'data' => $formattedVeterinarians
                ]
            ];
            
            $filename = app(ExportService::class)->generateFilename('conversion_by_veterinarians', 'xlsx');
            
            return app(ExportService::class)->toExcelMultipleSheets($sheetsData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте конверсии по ветеринарам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
