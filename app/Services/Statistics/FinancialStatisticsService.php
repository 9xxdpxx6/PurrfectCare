<?php

namespace App\Services\Statistics;

use App\Models\Order;
use App\Models\Service;
use App\Models\Drug;
use App\Models\LabTest;
use App\Models\VaccinationType;
use App\Models\Branch;
use Carbon\Carbon;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class FinancialStatisticsService
{
    /**
     * Получить общее количество заказов за период
     */
    public function getTotalOrders($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['id'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->count();
    }

    /**
     * Получить общую выручку за период
     */
    public function getTotalRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['total'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->sum('total');
    }

    public function getCategoryRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        $orders = Order::select(['id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['items:id,order_id,item_type,item_id,quantity,unit_price'])
            ->get();
        
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
                        $categories['services'] += $item->quantity * $item->unit_price;
                        break;
                    case 'Drug':
                        $categories['drugs'] += $item->quantity * $item->unit_price;
                        break;
                    case 'LabTest':
                        $categories['lab_tests'] += $item->quantity * $item->unit_price;
                        break;
                    case 'VaccinationType':
                        $categories['vaccinations'] += $item->quantity * $item->unit_price;
                        break;
                }
            }
        }
        
        return $categories;
    }

    public function getBranchRevenue($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at, is_paid и branch_id, select для выбора только нужных полей
        return Order::select(['id', 'branch_id', 'total', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['branch:id,name,address'])
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

    public function getRevenueData($startDate, $endDate)
    {
        $data = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
            $data[$current->format('Y-m-d')] = Order::select(['total'])
                ->whereDate('created_at', $current)
                ->where('is_paid', true) // Только оплаченные заказы
                ->sum('total');
            $current->addDay();
        }
        
        return $data;
    }

    public function getTopServices($startDate, $endDate)
    {
        // Оптимизация: используем индексы на created_at и is_paid, select для выбора только нужных полей
        return Order::select(['id', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_paid', true) // Только оплаченные заказы
            ->with(['items' => function($query) {
                $query->select(['id', 'order_id', 'item_type', 'item_id', 'quantity', 'unit_price'])
                    ->where('item_type', Service::class);
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
            ->sortByDesc('revenue')
            ->take(10);
    }

    /**
     * Экспорт выручки за период
     */
    public function exportRevenue($startDate, $endDate, $format = 'excel')
    {
        try {
            $totalRevenue = $this->getTotalRevenue($startDate, $endDate);
            $totalOrders = $this->getTotalOrders($startDate, $endDate);
            $categoryRevenue = $this->getCategoryRevenue($startDate, $endDate);
            $branchRevenue = $this->getBranchRevenue($startDate, $endDate);
            $revenueData = $this->getRevenueData($startDate, $endDate);
            
            // Форматируем основные показатели
            $formattedMetrics = [
                [
                    'Показатель' => 'Общая выручка',
                    'Значение' => number_format($totalRevenue, 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество заказов',
                    'Значение' => $totalOrders,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Средний чек',
                    'Значение' => $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2, ',', ' ') . ' руб.' : '0,00 руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем выручку по категориям
            $formattedCategoryRevenue = [
                [
                    'Категория' => 'Услуги',
                    'Выручка (руб.)' => $categoryRevenue['services'],
                    'Процент' => $totalRevenue > 0 ? number_format(($categoryRevenue['services'] / $totalRevenue) * 100, 2) . '%' : '0%'
                ],
                [
                    'Категория' => 'Препараты',
                    'Выручка (руб.)' => $categoryRevenue['drugs'],
                    'Процент' => $totalRevenue > 0 ? number_format(($categoryRevenue['drugs'] / $totalRevenue) * 100, 2) . '%' : '0%'
                ],
                [
                    'Категория' => 'Лабораторные анализы',
                    'Выручка (руб.)' => $categoryRevenue['lab_tests'],
                    'Процент' => $totalRevenue > 0 ? number_format(($categoryRevenue['lab_tests'] / $totalRevenue) * 100, 2) . '%' : '0%'
                ],
                [
                    'Категория' => 'Вакцинации',
                    'Выручка (руб.)' => $categoryRevenue['vaccinations'],
                    'Процент' => $totalRevenue > 0 ? number_format(($categoryRevenue['vaccinations'] / $totalRevenue) * 100, 2) . '%' : '0%'
                ]
            ];
            
            // Форматируем выручку по филиалам
            $formattedBranchRevenue = [];
            foreach ($branchRevenue as $branchData) {
                $formattedBranchRevenue[] = [
                    'Филиал' => $branchData['branch'] ? $branchData['branch']->name : 'Неизвестно',
                    'Адрес' => $branchData['branch'] ? $branchData['branch']->address : '',
                    'Выручка (руб.)' => $branchData['revenue'],
                    'Количество заказов' => $branchData['orders_count'],
                    'Средний чек' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00'
                ];
            }
            
            // Форматируем выручку по дням
            $formattedRevenueData = [];
            foreach ($revenueData as $date => $revenue) {
                $formattedRevenueData[] = [
                    'Дата' => Carbon::parse($date)->format('d.m.Y'),
                    'Выручка (руб.)' => $revenue,
                    'День недели' => Carbon::parse($date)->locale('ru')->dayName
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedCategoryRevenue, $formattedBranchRevenue, $formattedRevenueData);
            
            $filename = app(ExportService::class)->generateFilename('revenue_report', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте отчета по выручке', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт заказов за период
     */
    public function exportOrders($startDate, $endDate, $format = 'excel')
    {
        try {
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('is_paid', true)
                ->with(['client:id,name,email', 'pet:id,name', 'branch:id,name', 'status:id,name', 'manager:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $formattedData = [];
            foreach ($orders as $order) {
                $formattedData[] = [
                    'ID заказа' => $order->id,
                    'Клиент' => $order->client ? $order->client->name : 'Не указан',
                    'Email клиента' => $order->client ? $order->client->email : '',
                    'Питомец' => $order->pet ? $order->pet->name : 'Не указан',
                    'Филиал' => $order->branch ? $order->branch->name : 'Не указан',
                    'Статус' => $order->status ? $order->status->name : 'Не указан',
                    'Менеджер' => $order->manager ? $order->manager->name : 'Не указан',
                    'Сумма (руб.)' => $order->total,
                    'Оплачен' => $order->is_paid ? 'Да' : 'Нет',
                    'Дата создания' => $order->created_at ? $order->created_at->format('d.m.Y H:i') : '',
                    'Дата закрытия' => $order->closed_at ? $order->closed_at->format('d.m.Y H:i') : 'Не закрыт'
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('orders_report', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте отчета по заказам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт выручки по категориям
     */
    public function exportCategoryRevenue($startDate, $endDate, $format = 'excel')
    {
        try {
            $categoryRevenue = $this->getCategoryRevenue($startDate, $endDate);
            $totalRevenue = array_sum($categoryRevenue);
            
            $formattedData = [];
            foreach ($categoryRevenue as $category => $revenue) {
                $categoryName = match($category) {
                    'services' => 'Услуги',
                    'drugs' => 'Препараты',
                    'lab_tests' => 'Лабораторные анализы',
                    'vaccinations' => 'Вакцинации',
                    default => ucfirst($category)
                };
                
                $formattedData[] = [
                    'Категория' => $categoryName,
                    'Выручка (руб.)' => $revenue,
                    'Процент от общей выручки' => $totalRevenue > 0 ? number_format(($revenue / $totalRevenue) * 100, 2) . '%' : '0%'
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('category_revenue', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте выручки по категориям', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт выручки по филиалам
     */
    public function exportBranchRevenue($startDate, $endDate, $format = 'excel')
    {
        try {
            $branchRevenue = $this->getBranchRevenue($startDate, $endDate);
            
            $formattedData = [];
            foreach ($branchRevenue as $branchData) {
                $formattedData[] = [
                    'Филиал' => $branchData['branch'] ? $branchData['branch']->name : 'Неизвестно',
                    'Адрес' => $branchData['branch'] ? $branchData['branch']->address : '',
                    'Выручка (руб.)' => $branchData['revenue'],
                    'Количество заказов' => $branchData['orders_count'],
                    'Средний чек (руб.)' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00'
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('branch_revenue', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте выручки по филиалам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт выручки по филиалам с детальной аналитикой
     */
    public function exportRevenueByBranch($startDate, $endDate, $format = 'excel')
    {
        try {
            $branchRevenue = $this->getBranchRevenue($startDate, $endDate);
            $totalRevenue = $this->getTotalRevenue($startDate, $endDate);
            $totalOrders = $this->getTotalOrders($startDate, $endDate);
            
            // Форматируем основные показатели
            $formattedMetrics = [
                [
                    'Показатель' => 'Общая выручка по всем филиалам',
                    'Значение' => number_format($totalRevenue, 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество заказов',
                    'Значение' => $totalOrders,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество филиалов',
                    'Значение' => $branchRevenue->count(),
                    'Период' => 'Активные филиалы'
                ],
                [
                    'Показатель' => 'Средняя выручка на филиал',
                    'Значение' => $branchRevenue->count() > 0 ? number_format($totalRevenue / $branchRevenue->count(), 2, ',', ' ') . ' руб.' : '0,00 руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по филиалам
            $formattedBranchData = [];
            foreach ($branchRevenue as $branchData) {
                $revenuePercentage = $totalRevenue > 0 ? ($branchData['revenue'] / $totalRevenue) * 100 : 0;
                $ordersPercentage = $totalOrders > 0 ? ($branchData['orders_count'] / $totalOrders) * 100 : 0;
                
                $formattedBranchData[] = [
                    'Филиал' => $branchData['branch'] ? $branchData['branch']->name : 'Неизвестно',
                    'Адрес' => $branchData['branch'] ? $branchData['branch']->address : '',
                    'Выручка (руб.)' => $branchData['revenue'],
                    'Процент от общей выручки' => number_format($revenuePercentage, 2) . '%',
                    'Количество заказов' => $branchData['orders_count'],
                    'Процент от общих заказов' => number_format($ordersPercentage, 2) . '%',
                    'Средний чек (руб.)' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00',
                    'Выручка на заказ' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00'
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedBranchData);
            
            $filename = app(ExportService::class)->generateFilename('revenue_by_branch', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте выручки по филиалам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт анализа прибыльности
     */
    public function exportProfitabilityAnalysis($startDate, $endDate, $format = 'excel')
    {
        try {
            $categoryRevenue = $this->getCategoryRevenue($startDate, $endDate);
            $branchRevenue = $this->getBranchRevenue($startDate, $endDate);
            $totalRevenue = array_sum($categoryRevenue);
            $totalOrders = $this->getTotalOrders($startDate, $endDate);
            
            // Форматируем основные показатели прибыльности
            $formattedMetrics = [
                [
                    'Показатель' => 'Общая выручка',
                    'Значение' => number_format($totalRevenue, 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество заказов',
                    'Значение' => $totalOrders,
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Средний чек',
                    'Значение' => $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2, ',', ' ') . ' руб.' : '0,00 руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество филиалов',
                    'Значение' => $branchRevenue->count(),
                    'Период' => 'Активные филиалы'
                ]
            ];
            
            // Форматируем анализ по категориям
            $formattedCategoryAnalysis = [];
            foreach ($categoryRevenue as $category => $revenue) {
                $categoryName = match($category) {
                    'services' => 'Услуги',
                    'drugs' => 'Препараты',
                    'lab_tests' => 'Лабораторные анализы',
                    'vaccinations' => 'Вакцинации',
                    default => ucfirst($category)
                };
                
                $revenuePercentage = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
                
                // Определяем рентабельность категории (условная логика)
                $profitability = match($category) {
                    'services' => 'Высокая',
                    'drugs' => 'Средняя',
                    'lab_tests' => 'Высокая',
                    'vaccinations' => 'Средняя',
                    default => 'Неизвестно'
                };
                
                $formattedCategoryAnalysis[] = [
                    'Категория' => $categoryName,
                    'Выручка (руб.)' => $revenue,
                    'Процент от общей выручки' => number_format($revenuePercentage, 2) . '%',
                    'Рентабельность' => $profitability,
                    'Средняя цена за единицу' => $revenue > 0 ? number_format($revenue / max(1, $totalOrders / 4), 2, ',', ' ') . ' руб.' : '0,00 руб.'
                ];
            }
            
            // Форматируем анализ по филиалам
            $formattedBranchAnalysis = [];
            foreach ($branchRevenue as $branchData) {
                $revenuePercentage = $totalRevenue > 0 ? ($branchData['revenue'] / $totalRevenue) * 100 : 0;
                $ordersPercentage = $totalOrders > 0 ? ($branchData['orders_count'] / $totalOrders) * 100 : 0;
                
                // Определяем эффективность филиала
                $efficiency = match(true) {
                    $revenuePercentage >= 30 => 'Высокая',
                    $revenuePercentage >= 15 => 'Средняя',
                    $revenuePercentage >= 5 => 'Низкая',
                    default => 'Критическая'
                };
                
                $formattedBranchAnalysis[] = [
                    'Филиал' => $branchData['branch'] ? $branchData['branch']->name : 'Неизвестно',
                    'Адрес' => $branchData['branch'] ? $branchData['branch']->address : '',
                    'Выручка (руб.)' => $branchData['revenue'],
                    'Процент от общей выручки' => number_format($revenuePercentage, 2) . '%',
                    'Количество заказов' => $branchData['orders_count'],
                    'Процент от общих заказов' => number_format($ordersPercentage, 2) . '%',
                    'Средний чек (руб.)' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00',
                    'Эффективность' => $efficiency,
                    'Выручка на заказ' => $branchData['orders_count'] > 0 ? number_format($branchData['revenue'] / $branchData['orders_count'], 2, ',', ' ') : '0,00'
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedCategoryAnalysis, $formattedBranchAnalysis);
            
            $filename = app(ExportService::class)->generateFilename('profitability_analysis', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте анализа прибыльности', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт детального анализа выручки по периодам
     */
    public function exportRevenueTrendAnalysis($startDate, $endDate, $format = 'excel')
    {
        try {
            $revenueData = $this->getRevenueData($startDate, $endDate);
            $totalRevenue = array_sum($revenueData);
            $totalDays = count($revenueData);
            $averageDailyRevenue = $totalDays > 0 ? $totalRevenue / $totalDays : 0;
            
            // Форматируем основные показатели тренда
            $formattedMetrics = [
                [
                    'Показатель' => 'Общая выручка за период',
                    'Значение' => number_format($totalRevenue, 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Количество дней',
                    'Значение' => $totalDays,
                    'Период' => 'Анализируемый период'
                ],
                [
                    'Показатель' => 'Средняя дневная выручка',
                    'Значение' => number_format($averageDailyRevenue, 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Максимальная дневная выручка',
                    'Значение' => number_format(max($revenueData), 2, ',', ' ') . ' руб.',
                    'Период' => 'Пиковый день'
                ],
                [
                    'Показатель' => 'Минимальная дневная выручка',
                    'Значение' => number_format(min($revenueData), 2, ',', ' ') . ' руб.',
                    'Период' => 'Слабый день'
                ]
            ];
            
            // Форматируем данные по дням
            $formattedDailyData = [];
            foreach ($revenueData as $date => $revenue) {
                $dayOfWeek = Carbon::parse($date)->locale('ru')->dayName;
                $isWeekend = in_array($dayOfWeek, ['Суббота', 'Воскресенье']);
                $trend = $revenue > $averageDailyRevenue ? 'Выше среднего' : ($revenue < $averageDailyRevenue ? 'Ниже среднего' : 'Средний');
                
                $formattedDailyData[] = [
                    'Дата' => Carbon::parse($date)->format('d.m.Y'),
                    'День недели' => $dayOfWeek,
                    'Тип дня' => $isWeekend ? 'Выходной' : 'Рабочий',
                    'Выручка (руб.)' => $revenue,
                    'Отклонение от среднего' => $revenue - $averageDailyRevenue,
                    'Процент от среднего' => $averageDailyRevenue > 0 ? number_format(($revenue / $averageDailyRevenue) * 100, 2) . '%' : '0%',
                    'Тренд' => $trend
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedDailyData);
            
            $filename = app(ExportService::class)->generateFilename('revenue_trend_analysis', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте анализа трендов выручки', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
