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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $startTime = microtime(true);
        
        DB::transaction(function () {
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
            $this->call(VaccinationTypeSeeder::class);
            $this->call(SpecialtySeeder::class);
            $this->call(SupplierSeeder::class);

            echo "Создание пользователей...\n";
            // Массовая вставка пользователей
            $this->createInChunks(User::class, 1200, 200);
            
            echo "Создание питомцев...\n";
            // Массовая вставка питомцев
            $this->createInChunks(Pet::class, 2000, 200);
            
            echo "Создание сотрудников...\n";
            // Массовая вставка сотрудников
            $this->createInChunks(Employee::class, 300, 100);
            
            echo "Создание препаратов...\n";
            // Массовая вставка препаратов
            $this->createInChunks(Drug::class, 1000, 200);
            
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
            Order::factory()->createWithDistribution();
            
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
        });
        
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
     * Создает связи между приемами и заказами для конверсии 70-90% (оптимизированная версия)
     */
    private function createVisitOrderLinks(): void
    {
        echo "Начинаем создание связей visit_orders...\n";
        
        // Получаем данные более эффективно
        $visits = Visit::select('id', 'client_id', 'pet_id')->get();
        $orders = Order::select('id', 'client_id', 'pet_id')->get();
        
        // Целевая конверсия: 75-85%
        $conversionRate = fake()->numberBetween(75, 85) / 100;
        $visitsToLinkCount = (int)($visits->count() * $conversionRate);
        
                            // Случайно выбираем приемы для связывания - ПРОСТОЙ СПОСОБ
        $visitsToLink = $visits->shuffle()->take($visitsToLinkCount);
        
        // Группируем заказы по клиентам и питомцам для быстрого поиска
        $ordersByClientPet = $orders->groupBy(function($order) {
            return $order->client_id . '_' . $order->pet_id;
        });
        
        // Подготавливаем данные для массовой вставки
        $visitOrderData = [];
        $usedOrders = collect();
        
        $processedCount = 0;
        $totalToProcess = $visitsToLink->count();
        
        foreach ($visitsToLink as $visit) {
            $processedCount++;
            if ($processedCount % 1000 == 0) {
                echo "Обработано приемов: $processedCount/$totalToProcess\n";
            }
            
            // Получаем свойства - теперь это точно объект Eloquent
            $clientId = $visit->client_id;
            $petId = $visit->pet_id;
            $visitId = $visit->id;
            
            $clientPetKey = $clientId . '_' . $petId;
            
            // Ищем доступные заказы того же клиента и питомца
            $availableClientOrders = $ordersByClientPet->get($clientPetKey, collect())
                ->whereNotIn('id', $usedOrders->pluck('id'));
            
            if ($availableClientOrders->isNotEmpty()) {
                // 80% приемов получают 1 заказ, 20% - 2 заказа
                $orderCount = fake()->boolean(80) ? 1 : 2;
                
                if ($orderCount === 1) {
                    // Берем один случайный заказ
                    $orderToLink = $availableClientOrders->random();
                    $visitOrderData[] = [
                        'visit_id' => $visitId,
                        'order_id' => $orderToLink->id,
                    ];
                    $usedOrders->push($orderToLink);
                } else {
                    // Берем два случайных заказа
                    $ordersToLink = $availableClientOrders->random(min($availableClientOrders->count(), 2));
                    foreach ($ordersToLink as $order) {
                        $visitOrderData[] = [
                            'visit_id' => $visitId,
                            'order_id' => $order->id,
                        ];
                        $usedOrders->push($order);
                    }
                }
            } else {
                // Если нет заказов того же клиента, берем любой неиспользованный
                $availableOrders = $orders->whereNotIn('id', $usedOrders->pluck('id'));
                
                if ($availableOrders->isNotEmpty()) {
                    $randomOrder = $availableOrders->random();
                    $visitOrderData[] = [
                        'visit_id' => $visitId,
                        'order_id' => $randomOrder->id,
                    ];
                    $usedOrders->push($randomOrder);
                }
            }
            
            // Массовая вставка каждые 1000 записей для экономии памяти
            if (count($visitOrderData) >= 1000) {
                VisitOrder::insert($visitOrderData);
                $visitOrderData = [];
            }
        }
        
        // Вставляем оставшиеся данные
        if (!empty($visitOrderData)) {
            VisitOrder::insert($visitOrderData);
        }
        
        $totalCreated = VisitOrder::count();
        $conversion = round(($totalCreated / $visits->count()) * 100, 1);
        
        echo "Создано связей visit_orders: $totalCreated из " . $visits->count() . " приемов\n";
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

