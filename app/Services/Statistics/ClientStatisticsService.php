<?php

namespace App\Services\Statistics;

use App\Models\User;
use App\Models\Pet;
use App\Models\Order;
use Carbon\Carbon;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class ClientStatisticsService
{
    public function getClientsData($startDate, $endDate)
    {
        // Получаем всех клиентов, которые делали заказы в выбранном периоде
        // Оптимизация: используем индексы на created_at и client_id, select для выбора только нужных полей
        $clientsWithOrders = Order::select(['id', 'client_id', 'total', 'is_paid', 'created_at'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['client:id,name,email'])
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
            // Оптимизация: используем индекс на client_id и created_at, select для выбора только нужных полей
            $hasOrdersBeforePeriod = Order::select(['id'])
                ->where('client_id', $clientId)
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
        // Оптимизация: используем индексы на starts_at и select для выбора только нужных полей
        $pets = Pet::select(['id', 'breed_id'])
            ->whereHas('visits', function($query) use ($startDate, $endDate) {
                $query->select(['id', 'pet_id', 'starts_at']);
                $query->whereBetween('starts_at', [$startDate, $endDate]);
            })
            ->with(['breed:id,name,species_id', 'breed.species:id,name'])
            ->get();

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
        // Оптимизация: используем индексы на created_at и select для выбора только нужных полей
        return User::select(['id', 'name', 'email'])
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->select(['id', 'client_id', 'created_at']);
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with(['orders' => function($query) use ($startDate, $endDate) {
                $query->select(['id', 'client_id', 'total', 'is_paid', 'created_at']);
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
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

    /**
     * Экспорт данных по клиентам
     */
    public function exportClientsData($startDate, $endDate, $format = 'excel')
    {
        try {
            $clientsData = $this->getClientsData($startDate, $endDate);
            $petsData = $this->getPetsData($startDate, $endDate);
            $topClients = $this->getTopClients($startDate, $endDate);
            
            // Форматируем основные показатели
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество клиентов',
                    'Значение' => $clientsData['total_clients'],
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Новые клиенты',
                    'Значение' => $clientsData['new_clients'],
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Постоянные клиенты',
                    'Значение' => $clientsData['repeat_clients'],
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Процент новых клиентов',
                    'Значение' => $clientsData['new_clients_percentage'] . '%',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Процент постоянных клиентов',
                    'Значение' => $clientsData['repeat_clients_percentage'] . '%',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общий доход',
                    'Значение' => number_format($clientsData['total_revenue'], 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Доход от новых клиентов',
                    'Значение' => number_format($clientsData['new_clients_revenue'], 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Доход от постоянных клиентов',
                    'Значение' => number_format($clientsData['repeat_clients_revenue'], 2, ',', ' ') . ' руб.',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Процент дохода от новых клиентов',
                    'Значение' => $clientsData['new_clients_revenue_percentage'] . '%',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Процент дохода от постоянных клиентов',
                    'Значение' => $clientsData['repeat_clients_revenue_percentage'] . '%',
                    'Период' => $startDate . ' - ' . $endDate
                ],
                [
                    'Показатель' => 'Общее количество питомцев',
                    'Значение' => $petsData['total_pets'],
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по породам
            $formattedBreeds = [];
            foreach ($petsData['by_breed'] as $breed => $count) {
                $formattedBreeds[] = [
                    'Порода' => $breed,
                    'Количество питомцев' => $count
                ];
            }
            
            // Форматируем данные по видам
            $formattedSpecies = [];
            foreach ($petsData['by_species'] as $species => $breeds) {
                $totalInSpecies = $breeds->sum();
                $formattedSpecies[] = [
                    'Вид' => $species,
                    'Количество питомцев' => $totalInSpecies,
                    'Популярные породы' => $breeds->take(3)->keys()->implode(', ')
                ];
            }
            
            // Форматируем топ клиентов
            $formattedTopClients = [];
            foreach ($topClients as $data) {
                $formattedTopClients[] = [
                    'Клиент' => $data['user'] ? $data['user']->name : 'Неизвестно',
                    'Email' => $data['user'] ? $data['user']->email : '',
                    'Количество заказов' => $data['orders_count'],
                    'Общая сумма (руб.)' => $data['total_spent'],
                    'Средний чек (руб.)' => $data['average_order']
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedBreeds, $formattedSpecies, $formattedTopClients);
            
            $filename = app(ExportService::class)->generateFilename('clients_data', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте данных по клиентам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт топ клиентов
     */
    public function exportTopClients($startDate, $endDate, $format = 'excel')
    {
        try {
            $topClients = $this->getTopClients($startDate, $endDate);
            
            $formattedData = [];
            foreach ($topClients as $data) {
                $formattedData[] = [
                    'Клиент' => $data['user'] ? $data['user']->name : 'Неизвестно',
                    'Email' => $data['user'] ? $data['user']->email : '',
                    'Количество заказов' => $data['orders_count'],
                    'Общая сумма (руб.)' => $data['total_spent'],
                    'Средний чек (руб.)' => $data['average_order']
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('top_clients', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте топ клиентов', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Экспорт данных по питомцам
     */
    public function exportPetsData($startDate, $endDate, $format = 'excel')
    {
        try {
            $petsData = $this->getPetsData($startDate, $endDate);
            
            // Форматируем основные показатели
            $formattedMetrics = [
                [
                    'Показатель' => 'Общее количество питомцев',
                    'Значение' => $petsData['total_pets'],
                    'Период' => $startDate . ' - ' . $endDate
                ]
            ];
            
            // Форматируем данные по породам
            $formattedBreeds = [];
            foreach ($petsData['by_breed'] as $breed => $count) {
                $formattedBreeds[] = [
                    'Порода' => $breed,
                    'Количество питомцев' => $count
                ];
            }
            
            // Форматируем данные по видам
            $formattedSpecies = [];
            foreach ($petsData['by_species'] as $species => $breeds) {
                $totalInSpecies = $breeds->sum();
                $formattedSpecies[] = [
                    'Вид' => $species,
                    'Количество питомцев' => $totalInSpecies,
                    'Популярные породы' => $breeds->take(3)->keys()->implode(', ')
                ];
            }
            
            // Объединяем все данные
            $allData = array_merge($formattedMetrics, $formattedBreeds, $formattedSpecies);
            
            $filename = app(ExportService::class)->generateFilename('pets_data', 'xlsx');
            
            return app(ExportService::class)->toExcel($allData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте данных по питомцам', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
