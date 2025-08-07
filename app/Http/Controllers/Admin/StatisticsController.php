<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Order;
use App\Models\User;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTest;
use App\Models\Vaccination;
use App\Models\Branch;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Основные метрики
        $metrics = [
            'total_visits' => Visit::whereBetween('starts_at', [$startDate, $endDate])->count(),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])->sum('total'),
            'total_clients' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_pets' => Pet::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_employees' => Employee::count(),
            'total_branches' => Branch::count(),
            'total_services' => Service::count(),
            'total_veterinarians' => Employee::whereHas('specialties', function($query) {
                $query->where('name', 'like', '%ветеринар%');
            })->count(),
        ];
        
        // Средний чек
        $metrics['average_order'] = $metrics['total_orders'] > 0 
            ? round($metrics['total_revenue'] / $metrics['total_orders'], 2) 
            : 0;
        
        // Конверсия приёмов в заказы
        $visitsWithOrders = Visit::whereBetween('starts_at', [$startDate, $endDate])
            ->whereHas('client.orders', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->count();
        
        $metrics['conversion_rate'] = $metrics['total_visits'] > 0 
            ? round(($visitsWithOrders / $metrics['total_visits']) * 100, 1) 
            : 0;
        
        // Статистика по дням недели
        $weeklyStats = $this->getWeeklyStats($startDate);
        
        // Топ услуг
        $topServices = $this->getTopServices($startDate);
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.dashboard', compact('metrics', 'weeklyStats', 'topServices', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function financial(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Выручка по периодам
        $revenueData = $this->getRevenueData($startDate, $endDate);
        
        // Выручка по категориям
        $categoryRevenue = $this->getCategoryRevenue($startDate, $endDate);
        
        // Топ доходных услуг
        $topServices = $this->getTopServices($startDate, $endDate);
        
        // Прибыльность по филиалам
        $branchRevenue = $this->getBranchRevenue($startDate, $endDate);
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.financial', compact('revenueData', 'categoryRevenue', 'topServices', 'branchRevenue', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function operational(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Статистика приёмов
        $visitsData = $this->getVisitsData($startDate, $endDate);
        
        // Загруженность ветеринаров
        $employeeLoad = $this->getEmployeeLoad($startDate, $endDate);
        
        // Статистика по статусам
        $statusStats = $this->getStatusStats($startDate, $endDate);
        
        // Заполненность расписания
        $scheduleStats = $this->getScheduleStats($startDate, $endDate);
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.operational', compact('visitsData', 'employeeLoad', 'statusStats', 'scheduleStats', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function clients(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Статистика клиентов
        $clientsData = $this->getClientsData($startDate, $endDate);
        
        // Статистика питомцев
        $petsData = $this->getPetsData($startDate, $endDate);
        
        // Топ клиентов
        $topClients = $this->getTopClients($startDate, $endDate);
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.clients', compact('clientsData', 'petsData', 'topClients', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    public function medical(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDateInput = $request->get('start_date');
        $endDateInput = $request->get('end_date');
        if ($period === 'custom' && $startDateInput && $endDateInput) {
            try {
                $startDate = Carbon::createFromFormat('d.m.Y', $startDateInput)->startOfDay();
                $endDate = Carbon::createFromFormat('d.m.Y', $endDateInput)->endOfDay();
            } catch (\Throwable $e) {
                $startDate = $this->getStartDate('month');
                $endDate = Carbon::now();
            }
        } else {
            $startDate = $this->getStartDate($period);
            $endDate = Carbon::now();
        }
        
        // Статистика диагнозов
        $diagnosesData = $this->getDiagnosesData($startDate, $endDate);
        
        // Статистика вакцинаций
        $vaccinationsData = $this->getVaccinationsData($startDate, $endDate);
        
        // Статистика анализов
        $labTestsData = $this->getLabTestsData($startDate, $endDate);
        $dateRange = $startDate->format('d.m.Y') . ' — ' . $endDate->format('d.m.Y');
        return view('admin.statistics.medical', compact('diagnosesData', 'vaccinationsData', 'labTestsData', 'period', 'startDate', 'endDate', 'dateRange'));
    }
    
    private function getStartDate($period)
    {
        return match($period) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            'all' => Carbon::createFromDate(2020, 1, 1), // Начало 2020 года для "за всё время"
            default => Carbon::now()->subMonth(),
        };
    }
    
    private function getWeeklyStats($startDate)
    {
        $stats = [];
        $dayNames = [
            1 => 'Пн',
            2 => 'Вт', 
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            0 => 'Вс' // Carbon воскресенье = 0
        ];
        
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays(6 - $i);
            $dayOfWeek = $date->dayOfWeek;
            $dayName = $dayNames[$dayOfWeek];
            
            $stats[$dayName] = [
                'visits' => Visit::whereDate('starts_at', $date)->count(),
                'orders' => Order::whereDate('created_at', $date)->count(),
                'revenue' => Order::whereDate('created_at', $date)->sum('total'),
            ];
        }
        return $stats;
    }
    
    private function getTopServices($startDate)
    {
        return Order::where('created_at', '>=', $startDate)
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
            ->sortByDesc('count')
            ->take(5);
    }
    
    private function getRevenueData($startDate)
    {
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::now();
        
        while ($current <= $end) {
            $data[$current->format('Y-m-d')] = Order::whereDate('created_at', $current)->sum('total');
            $current->addDay();
        }
        
        return $data;
    }
    
    private function getCategoryRevenue($startDate)
    {
        $orders = Order::where('created_at', '>=', $startDate)->with('items.item')->get();
        
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
                        $categories['services'] += $item->total;
                        break;
                    case 'Drug':
                        $categories['drugs'] += $item->total;
                        break;
                    case 'LabTest':
                        $categories['lab_tests'] += $item->total;
                        break;
                    case 'Vaccination':
                        $categories['vaccinations'] += $item->total;
                        break;
                }
            }
        }
        
        return $categories;
    }
    
    private function getBranchRevenue($startDate)
    {
        return Order::where('created_at', '>=', $startDate)
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
    
    private function getVisitsData($startDate)
    {
        return [
            'total' => Visit::where('starts_at', '>=', $startDate)->count(),
            'by_status' => Visit::where('starts_at', '>=', $startDate)
                ->with('status')
                ->get()
                ->groupBy('status.name')
                ->map->count(),
            'by_day' => Visit::where('starts_at', '>=', $startDate)
                ->get()
                ->groupBy(function($visit) {
                    return $visit->starts_at->format('Y-m-d');
                })
                ->map->count(),
        ];
    }
    
    private function getEmployeeLoad($startDate)
    {
        return Visit::where('starts_at', '>=', $startDate)
            ->with('schedule.veterinarian')
            ->get()
            ->groupBy('schedule.veterinarian_id')
            ->map(function($visits, $employeeId) {
                $employee = $visits->first()->schedule->veterinarian;
                return [
                    'employee' => $employee,
                    'visits_count' => $visits->count(),
                ];
            })
            ->sortByDesc('visits_count');
    }
    
    private function getStatusStats($startDate)
    {
        return Visit::where('starts_at', '>=', $startDate)
            ->with('status')
            ->get()
            ->groupBy('status.name')
            ->map->count();
    }
    
    private function getScheduleStats($startDate)
    {
        // Здесь можно добавить логику для анализа расписания
        return [
            'total_schedules' => \App\Models\Schedule::where('shift_starts_at', '>=', $startDate)->count(),
            'schedules_with_visits' => \App\Models\Schedule::where('shift_starts_at', '>=', $startDate)
                ->whereHas('visits')
                ->count(),
        ];
    }
    
    private function getClientsData($startDate)
    {
        return [
            'new_clients' => User::where('created_at', '>=', $startDate)->count(),
            'repeat_clients' => User::whereHas('visits', function($query) use ($startDate) {
                $query->where('starts_at', '>=', $startDate);
            })->where('created_at', '<', $startDate)->count(),
            'total_clients' => User::whereHas('visits', function($query) use ($startDate) {
                $query->where('starts_at', '>=', $startDate);
            })->count(),
        ];
    }
    
    private function getPetsData($startDate)
    {
        return [
            'total_pets' => Pet::whereHas('visits', function($query) use ($startDate) {
                $query->where('starts_at', '>=', $startDate);
            })->count(),
            'by_breed' => Pet::whereHas('visits', function($query) use ($startDate) {
                $query->where('starts_at', '>=', $startDate);
            })->with('breed.species')->get()
                ->groupBy(function($pet) {
                    return $pet->breed ? $pet->breed->name : 'Неизвестная порода';
                })
                ->map->count(),
        ];
    }
    
    private function getTopClients($startDate)
    {
        return User::whereHas('orders', function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        })->with(['orders' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])->get()
        ->map(function($user) {
            return [
                'user' => $user,
                'orders_count' => $user->orders->count(),
                'total_spent' => $user->orders->sum('total'),
            ];
        })
        ->sortByDesc('total_spent')
        ->take(10);
    }
    
    private function getDiagnosesData($startDate)
    {
        return Visit::where('starts_at', '>=', $startDate)
            ->with('diagnoses.dictionaryDiagnosis')
            ->get()
            ->flatMap(function($visit) {
                return $visit->diagnoses;
            })
            ->groupBy(function($diagnosis) {
                return $diagnosis->dictionaryDiagnosis ? $diagnosis->dictionaryDiagnosis->name : 'Неизвестный диагноз';
            })
            ->map->count()
            ->sortByDesc(function($count) {
                return $count;
            })
            ->take(10);
    }
    
    private function getVaccinationsData($startDate)
    {
        return Vaccination::where('created_at', '>=', $startDate)
            ->with('pet.breed.species')
            ->get()
            ->groupBy(function($vaccination) {
                if ($vaccination->pet && $vaccination->pet->breed && $vaccination->pet->breed->species) {
                    return $vaccination->pet->breed->species->name;
                }
                return 'Неизвестный вид';
            })
            ->map->count();
    }
    
    private function getLabTestsData($startDate)
    {
        return LabTest::where('created_at', '>=', $startDate)
            ->with('labTestType')
            ->get()
            ->groupBy(function($labTest) {
                return $labTest->labTestType ? $labTest->labTestType->name : 'Неизвестный анализ';
            })
            ->map->count()
            ->sortByDesc(function($count) {
                return $count;
            })
            ->take(10);
    }
} 