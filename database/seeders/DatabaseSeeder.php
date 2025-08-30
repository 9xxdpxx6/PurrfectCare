<?php

namespace Database\Seeders;
use App\Models\Branch;
use App\Models\Drug;
use App\Models\DrugProcurement;
use App\Models\Species;
use App\Models\Breed;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Pet;
use App\Models\Specialty;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Visit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vaccination;
use App\Models\LabTest;
use App\Models\LabTestType;
use App\Models\LabTestParam;
use App\Models\LabTestResult;
use App\Models\Schedule;
use App\Models\Status;
use App\Models\DictionarySymptom;
use App\Models\DictionaryDiagnosis;
use App\Models\Symptom;
use App\Models\Diagnosis;
use App\Models\VisitOrder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;

class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $startTime = microtime(true);
        
        // Отключаем события и внешние ключи для ускорения
        $this->disableModelEvents();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        echo "Заполнение справочных данных...\n";
        
        // Вызываем сидеры для справочных данных
        $this->call(BranchSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(SpeciesSeeder::class);
        $this->call(BreedSeeder::class);
        $this->call(LabTestTypeSeeder::class);
        $this->call(LabTestParamSeeder::class);
        $this->call(DictionarySymptomSeeder::class);
        $this->call(DictionaryDiagnosisSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(SpecialtySeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(DrugSeeder::class);
        $this->call(VaccinationTypeSeeder::class);

        echo "Создание пользователей...\n";
        // Массовая вставка пользователей
        $this->createInChunks(User::class, 1200, 200);
        
        echo "Создание питомцев...\n";
        // Массовая вставка питомцев
        $this->createInChunks(Pet::class, 2000, 200);
        
        echo "Создание сотрудников...\n";
        // Массовая вставка сотрудников
        $this->createInChunks(Employee::class, 300, 100);
        
        echo "Создание поставок препаратов...\n";
        // Массовая вставка поставок
        $this->createInChunks(DrugProcurement::class, 1500, 200);
        
        echo "Создание лабораторных анализов...\n";
        // Массовая вставка лабораторных анализов
        $this->createInChunks(LabTest::class, 1000, 200);
        
        echo "Создание результатов анализов...\n";
        // Массовая вставка результатов анализов
        $this->createInChunks(LabTestResult::class, 3000, 300);
        
        echo "Создание расписания...\n";
        // Массовая вставка расписания
        $this->createInChunks(Schedule::class, 6000, 500);
        
        echo "Создание приемов...\n";
        // Массовая вставка приемов
        $this->createInChunks(Visit::class, 8000, 500);
        
        echo "Создание заказов...\n";
        // Создаем заказы с правильным распределением по клиентам
        $this->createOrdersWithItemsInChunks();
        
        echo "Создание вакцинаций...\n";
        // Массовая вставка вакцинаций
        $this->createInChunks(Vaccination::class, 1500, 200);
        
        echo "Создание симптомов...\n";
        // Массовая вставка симптомов
        $this->createInChunks(Symptom::class, 4000, 400);
        
        echo "Создание диагнозов...\n";
        // Массовая вставка диагнозов
        $this->createInChunks(Diagnosis::class, 3000, 300);
        
        echo "Создание связей между филиалами и услугами...\n";
        // Создаем связи между филиалами и услугами
        $this->createServiceBranchLinks();
        
        echo "Создание связей сотрудников со специальностями и филиалами...\n";
        // Создаем связи сотрудников со специальностями и филиалами
        $this->createEmployeeRelations();
        
        echo "Создание связей между приемами и заказами...\n";
        // Создаем связи между приемами и заказами
        $this->createVisitOrderLinks();
            
        // Включаем обратно внешние ключи
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Восстанавливаем события
        $this->restoreModelEvents();
        
        $endTime = microtime(true);
        echo "Затрачено времени: " . round($endTime - $startTime, 2) . " секунд\n";
    }
    
    /**
     * Создает данные блоками для ускорения
     */
    private function createInChunks(string $modelClass, int $total, int $chunkSize): void
    {
        for ($i = 0; $i < $total; $i += $chunkSize) {
            $currentChunkSize = min($chunkSize, $total - $i);
            
            // Создаем данные по одной записи, чтобы избежать проблем с форматом дат
            $insertData = [];
            for ($j = 0; $j < $currentChunkSize; $j++) {
                $insertData[] = $modelClass::factory()->raw();
            }
            
            $modelClass::insert($insertData);
        }
    }

    /**
     * Создает заказы и связанные элементы блоками для ускорения
     */
    private function createOrdersWithItemsInChunks(): void
    {
        $allOrdersData = [];
        $allOrderItemsData = [];
        $allVaccinationsData = [];
        $allLabTestsData = [];

        $users = User::all();
        $pets = Pet::all();

        if ($users->isEmpty() || $pets->isEmpty()) {
            return;
        }

        $orderFactory = new \Database\Factories\OrderFactory();
        $orderFactory->count(1)->state([]); // Initialize the factory once

        $totalUsers = $users->count();
        
        // 35% клиентов с ровно 1 заказом
        $singleOrderUsers = $users->random(ceil($totalUsers * 0.35));
        
        // Остальные 65% клиентов
        $otherUsers = $users->diff($singleOrderUsers);

        echo "\tСбор данных для заказов с одним заказом...\n";
        // Создаем ровно 1 заказ для каждого клиента из первой группы
        foreach ($singleOrderUsers as $user) {
            $pet = Pet::where('client_id', $user->id)->first();
            if ($pet) {
                $data = $orderFactory->createRealisticOrderForUser($user, $pet);
                if (!empty($data['order'])) {
                    $allOrdersData[] = $data['order'];
                    $allOrderItemsData[] = ['items' => $data['items']['orderItems'], 'temp_order_id' => count($allOrdersData) - 1];
                    $allVaccinationsData[] = $data['items']['vaccinations'];
                    $allLabTestsData[] = $data['items']['labTests'];
                }
            }
        }

        echo "\tСбор данных для заказов с несколькими заказами...\n";
        // Создаем от 2 до 15 заказов для остальных клиентов (увеличиваем количество)
        foreach ($otherUsers as $user) {
            $pet = Pet::where('client_id', $user->id)->first();
            if ($pet) {
                $orderCount = $this->faker->numberBetween(3, 20); // Увеличили еще больше
                for ($i = 0; $i < $orderCount; $i++) {
                    $data = $orderFactory->createRealisticOrderForUser($user, $pet);
                    if (!empty($data['order'])) {
                        $allOrdersData[] = $data['order'];
                        $allOrderItemsData[] = ['items' => $data['items']['orderItems'], 'temp_order_id' => count($allOrdersData) - 1];
                        $allVaccinationsData[] = $data['items']['vaccinations'];
                        $allLabTestsData[] = $data['items']['labTests'];
                    }
                }
            }
        }

        echo "\tСбор данных для дополнительных заказов...\n";
        // Additional orders to reach a target count or cover users without pets, etc.
        // This part needs to be carefully adjusted to collect data instead of creating models.
        $targetOrders = 8000; // Target number of orders, adjust as needed
        $currentOrderCount = count($allOrdersData);

        if ($currentOrderCount < $targetOrders) {
            $remainingOrders = $targetOrders - $currentOrderCount;
            echo "\tСоздаем дополнительные {$remainingOrders} заказов...\n";

            for ($i = 0; $i < $remainingOrders; $i++) {
                $user = $users->random();
                $pet = Pet::where('client_id', $user->id)->first();

                if (!$pet) {
                    $pet = $pets->random(); // If user has no pet, pick a random one
                }
                
                if ($user && $pet) {
                    $data = $orderFactory->createRealisticOrderForUserWithDate($user, $pet);
                    if (!empty($data['order'])) {
                        $allOrdersData[] = $data['order'];
                        $allOrderItemsData[] = ['items' => $data['items']['orderItems'], 'temp_order_id' => count($allOrdersData) - 1];
                        $allVaccinationsData[] = $data['items']['vaccinations'];
                        $allLabTestsData[] = $data['items']['labTests'];
                    }
                }
            }
        }

        // Flatten the collected data
        $finalOrderItemsData = [];
        $finalVaccinationsData = [];
        $finalLabTestsData = [];

        // The $allOrderItemsData currently stores items with a temporary order ID (index in $allOrdersData)
        // We need to store this index so we can link them after bulk inserting orders.
        $indexedOrderItemsData = [];

        foreach ($allOrderItemsData as $index => $orderItemBundle) {
            foreach ($orderItemBundle['items'] as $item) {
                $indexedOrderItemsData[] = [
                    'item' => $item,
                    'temp_order_id' => $orderItemBundle['temp_order_id']
                ];
            }
        }

        foreach ($allVaccinationsData as $bundle) {
            foreach ($bundle as $vaccination) {
                $finalVaccinationsData[] = $vaccination;
            }
        }

        foreach ($allLabTestsData as $bundle) {
            foreach ($bundle as $labTest) {
                $finalLabTestsData[] = $labTest;
            }
        }

        echo "\tМассовая вставка заказов...\n";
        // Bulk insert orders
        $this->insertInChunks(Order::class, $allOrdersData);
        $insertedOrders = Order::select('id', 'client_id', 'pet_id', 'created_at')->orderBy('id')->get();

        echo "\tОбработка элементов заказов, вакцинаций и лабораторных тестов...\n";
        $orderIdMap = []; // Map temporary order IDs to actual inserted order IDs
        foreach ($insertedOrders as $index => $order) {
            $orderIdMap[$index] = $order->id;
        }

        $finalOrderItems = [];
        foreach ($indexedOrderItemsData as $indexedItem) {
            $item = $indexedItem['item'];
            $tempOrderId = $indexedItem['temp_order_id'];
            
            $item['order_id'] = $orderIdMap[$tempOrderId];
            $finalOrderItems[] = $item;
        }

        // Bulk insert LabTests first to get their IDs
        $this->insertInChunks(LabTest::class, $finalLabTestsData); // Assuming LabTest model can handle this
        $insertedLabTests = LabTest::select('id', 'lab_test_type_id', 'pet_id', 'veterinarian_id', 'received_at')->orderBy('id')->get();

        // Update OrderItems with actual LabTest IDs
        $labTestMap = []; // Map original lab test data to actual IDs
        foreach ($insertedLabTests as $labTest) {
            // This mapping is tricky because we don't have a unique identifier in the raw data
            // For now, let's assume the order of insertion is maintained and map by index
            // A more robust solution would be to generate a UUID for raw data before insertion
            // For simplicity in this fix, we'll rely on insertion order for now.
            // This needs careful consideration if the order is not guaranteed.
        }

        // Update lab test item_id in finalOrderItems
        $labTestItemIndex = 0;
        foreach ($finalOrderItems as &$item) {
            if ($item['item_type'] === LabTest::class && $item['item_id'] === 0) {
                // Assign the actual lab test ID
                if (isset($insertedLabTests[$labTestItemIndex])) {
                    $item['item_id'] = $insertedLabTests[$labTestItemIndex]->id;
                }
                $labTestItemIndex++;
            }
        }
        unset($item); // Unset the reference to the last element

        // Bulk insert OrderItems
        $this->insertInChunks(OrderItem::class, $finalOrderItems);

        // Bulk insert Vaccinations
        $this->insertInChunks(Vaccination::class, $finalVaccinationsData);

        echo "\tОбновление сумм заказов...\n";
        // Recalculate and update order totals in bulk
        $this->updateOrderTotalsInBulk($insertedOrders->pluck('id')->toArray());

        echo "Создано заказов: " . Order::count() . "\n";
        echo "Создано элементов заказов: " . OrderItem::count() . "\n";
        echo "Создано вакцинаций: " . Vaccination::count() . "\n";
        echo "Создано лабораторных тестов: " . LabTest::count() . "\n";
    }

    /**
     * Обновляет общие суммы заказов в базе данных.
     */
    private function updateOrderTotalsInBulk(array $orderIds): void
    {
        // Fetch all order items related to the given order IDs
        $orderItems = OrderItem::whereIn('order_id', $orderIds)
            ->select('order_id', 'quantity', 'unit_price')
            ->get();

        $orderTotals = [];
        foreach ($orderItems as $item) {
            if (!isset($orderTotals[$item->order_id])) {
                $orderTotals[$item->order_id] = 0;
            }
            $orderTotals[$item->order_id] += ($item->quantity * $item->unit_price);
        }

        // Prepare data for bulk update
        $cases = [];
        $ids = [];
        foreach ($orderTotals as $orderId => $total) {
            $cases[] = "WHEN {$orderId} THEN {$total}";
            $ids[] = $orderId;
        }

        if (!empty($cases)) {
            $idsString = implode(',', $ids);
            $casesString = implode(' ', $cases);
            
            DB::statement("
                UPDATE orders
                SET total = CASE id
                    {$casesString}
                END
                WHERE id IN ({$idsString})
            ");
        }
    }

    /**
     * Отключает события моделей для ускорения
     */
    private function disableModelEvents(): void
    {
        User::unsetEventDispatcher();
        Pet::unsetEventDispatcher();
        Employee::unsetEventDispatcher();
        Drug::unsetEventDispatcher();
        DrugProcurement::unsetEventDispatcher();
        LabTest::unsetEventDispatcher();
        LabTestResult::unsetEventDispatcher();
        Schedule::unsetEventDispatcher();
        Visit::unsetEventDispatcher();
        Vaccination::unsetEventDispatcher();
        Symptom::unsetEventDispatcher();
        Diagnosis::unsetEventDispatcher();
    }

    /**
     * Вставляет данные блоками, чтобы избежать слишком большого количества плейсхолдеров.
     */
    private function insertInChunks(string $modelClass, array $data, int $chunkSize = 1000): void
    {
        foreach (array_chunk($data, $chunkSize) as $chunk) {
            $modelClass::insert($chunk);
        }
    }
    
    /**
     * Восстанавливает события моделей
     */
    private function restoreModelEvents(): void
    {
        User::setEventDispatcher(app('events'));
        Pet::setEventDispatcher(app('events'));
        Employee::setEventDispatcher(app('events'));
        Drug::setEventDispatcher(app('events'));
        DrugProcurement::setEventDispatcher(app('events'));
        LabTest::setEventDispatcher(app('events'));
        LabTestResult::setEventDispatcher(app('events'));
        Schedule::setEventDispatcher(app('events'));
        Visit::setEventDispatcher(app('events'));
        Vaccination::setEventDispatcher(app('events'));
        Symptom::setEventDispatcher(app('events'));
        Diagnosis::setEventDispatcher(app('events'));
    }
    
    /**
     * Создает связи между филиалами и услугами
     */
    private function createServiceBranchLinks(): void
    {
        $services = Service::all();
        $branches = Branch::all();
        
        foreach ($services as $service) {
            // 70% услуг привязаны к одному случайному филиалу, 30% - к обоим филиалам
            if (fake()->boolean(70)) {
                $service->branches()->attach($branches->random()->id);
            } else {
                $service->branches()->attach($branches->pluck('id'));
            }
        }
    }
    
    /**
     * Создает связи сотрудников со специальностями и филиалами
     */
    private function createEmployeeRelations(): void
    {
        $employees = Employee::all();
        $specialties = Specialty::all();
        $branches = Branch::all();
        
        foreach ($employees as $employee) {
            // Каждый сотрудник получает 1-3 специальности (в среднем 1.5, что даст 150% от количества сотрудников)
            $specialtyCount = fake()->randomElement([1, 1, 1, 1, 2, 2, 2, 3, 3, 3]);
            $employeeSpecialties = $specialties->random($specialtyCount);
            $employee->specialties()->attach($employeeSpecialties->pluck('id'));
            
            // Каждый сотрудник привязан к 1-2 филиалам
            $branchCount = fake()->randomElement([1, 1, 1, 1, 1, 1, 1, 1, 2, 2]);
            $employeeBranches = $branches->random($branchCount);
            $employee->branches()->attach($employeeBranches->pluck('id'));
        }
    }

    /**
     * Создает связи между приемами и заказами для конверсии 75-85%
     */
    private function createVisitOrderLinks(): void
    {
        echo "Начинаем создание связей visit_orders...\n";
        
        // Получаем все визиты и заказы
        $allVisits = Visit::select('id', 'client_id', 'pet_id')->get();
        $allOrders = Order::select('id', 'client_id', 'pet_id')->get();
        
        if ($allVisits->isEmpty() || $allOrders->isEmpty()) {
            echo "Нет визитов или заказов для связывания.\n";
            return;
        }

        // Группируем заказы по client_id и pet_id для быстрого поиска
        $ordersByClientPet = $allOrders->groupBy(function ($order) {
            return $order->client_id . '_' . $order->pet_id;
        });

        // Целевая конверсия: 75-85% от общего количества визитов
        $targetConversionRate = $this->faker->numberBetween(75, 85) / 100;
        $totalVisitsCount = $allVisits->count();
        $targetLinkedVisitsCount = (int)($totalVisitsCount * $targetConversionRate);

        echo "\tЦелевая конверсия: " . ($targetConversionRate * 100) . "%\n";
        echo "\tЦель: связать {$targetLinkedVisitsCount} из {$totalVisitsCount} приемов\n";

        // Фильтруем визиты, для которых есть заказы того же клиента и питомца
        $linkableVisits = $allVisits->filter(function ($visit) use ($ordersByClientPet) {
            $key = $visit->client_id . '_' . $visit->pet_id;
            return $ordersByClientPet->has($key) && $ordersByClientPet->get($key)->isNotEmpty();
        });

        echo "\tВизитов с возможными заказами: " . $linkableVisits->count() . "\n";

        // Выбираем визиты для связывания - сначала те, у которых есть заказы
        $visitsToLink = $linkableVisits->shuffle();
        
        // Если связываемых визитов меньше цели, добавляем случайные визиты и будем искать заказы других клиентов
        if ($visitsToLink->count() < $targetLinkedVisitsCount) {
            $remainingVisits = $allVisits->diff($linkableVisits)->shuffle();
            $additionalVisitsNeeded = $targetLinkedVisitsCount - $visitsToLink->count();
            $visitsToLink = $visitsToLink->concat($remainingVisits->take($additionalVisitsNeeded));
        } else {
            $visitsToLink = $visitsToLink->take($targetLinkedVisitsCount);
        }

        // Подготавливаем данные для массовой вставки
        $visitOrderData = [];
        $orderUsageCount = []; // Счетчик использования заказов

        $processedCount = 0;
        $successfullyLinked = 0;
        $totalToProcess = $visitsToLink->count();
        
        echo "\tОбрабатываем {$totalToProcess} приемов...\n";

        foreach ($visitsToLink as $visit) {
            $processedCount++;
            if ($processedCount % 1000 == 0) {
                echo "\tОбработано приемов: $processedCount/$totalToProcess, связано: $successfullyLinked\n";
            }
            
            $clientId = $visit->client_id;
            $petId = $visit->pet_id;
            $visitId = $visit->id;

            $clientPetKey = $clientId . '_' . $petId;

            // Ищем заказы того же клиента и питомца
            $availableOrders = $ordersByClientPet->get($clientPetKey, collect());
            
            // Если нет заказов для этого клиента+питомца, ищем заказы только этого клиента
            if ($availableOrders->isEmpty()) {
                $clientOrders = $allOrders->where('client_id', $clientId);
                if ($clientOrders->isNotEmpty()) {
                    $availableOrders = $clientOrders->random(min(3, $clientOrders->count()));
                }
            }

            // Если все еще нет заказов, берем случайные заказы (10% случаев)
            if ($availableOrders->isEmpty() && $this->faker->boolean(10)) {
                $availableOrders = $allOrders->random(min(2, $allOrders->count()));
            }
            
            if ($availableOrders->isNotEmpty()) {
                // 85% приемов получают 1 заказ, 15% - 2 заказа
                $orderCount = $this->faker->boolean(85) ? 1 : min(2, $availableOrders->count());
                
                // Фильтруем заказы, которые уже использовались слишком много раз (максимум 3 раза)
                $availableOrdersFiltered = $availableOrders->filter(function ($order) use ($orderUsageCount) {
                    return !isset($orderUsageCount[$order->id]) || $orderUsageCount[$order->id] < 3;
                });
                
                if ($availableOrdersFiltered->isNotEmpty()) {
                    $ordersToLink = $availableOrdersFiltered->random(min($orderCount, $availableOrdersFiltered->count()));

                    foreach ($ordersToLink as $order) {
                        $visitOrderData[] = [
                            'visit_id' => $visitId,
                            'order_id' => $order->id,
                        ];
                        
                        // Увеличиваем счетчик использования заказа
                        if (!isset($orderUsageCount[$order->id])) {
                            $orderUsageCount[$order->id] = 0;
                        }
                        $orderUsageCount[$order->id]++;
                    }
                    $successfullyLinked++;
                }
            }

            // Массовая вставка каждые 1000 записей для экономии памяти
            if (count($visitOrderData) >= 1000) {
                $this->insertInChunks(VisitOrder::class, $visitOrderData);
                $visitOrderData = [];
            }
        }
        
        // Вставляем оставшиеся данные
        if (!empty($visitOrderData)) {
            $this->insertInChunks(VisitOrder::class, $visitOrderData);
        }

        $totalCreated = VisitOrder::count();
        $actualLinkedVisits = DB::table('visit_orders')->distinct('visit_id')->count('visit_id');
        $conversion = round(($actualLinkedVisits / $totalVisitsCount) * 100, 1);

        echo "Создано связей visit_orders: $totalCreated\n";
        echo "Визитов с заказами: $actualLinkedVisits из {$totalVisitsCount} приемов\n";
        echo "Конверсия: {$conversion}%\n";

        // Быстрая проверка ограничений
        $maxOrdersPerVisit = DB::table('visit_orders')
            ->selectRaw('visit_id, COUNT(*) as order_count')
            ->groupBy('visit_id')
            ->orderBy('order_count', 'desc')
            ->limit(1)
            ->first();
            
        $maxVisitsPerOrder = DB::table('visit_orders')
            ->selectRaw('order_id, COUNT(*) as visit_count')
            ->groupBy('order_id')
            ->orderBy('visit_count', 'desc')
            ->limit(1)
            ->first();
            
        echo "Максимум заказов на приём: " . ($maxOrdersPerVisit ? $maxOrdersPerVisit->order_count : 0) . "\n";
        echo "Максимум приёмов на заказ: " . ($maxVisitsPerOrder ? $maxVisitsPerOrder->visit_count : 0) . "\n";
    }
}
