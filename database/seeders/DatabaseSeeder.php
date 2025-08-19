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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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

        // Создаем базовые данные
        // Species и Breed создаются через сидеры
        User::factory(120)->create();
        Pet::factory(200)->create();
        $this->call(SpecialtySeeder::class);

        // Создаем сотрудников с привязкой к специальностям и филиалам
        Employee::factory(30)->create()->each(function ($employee) {
            $specialties = Specialty::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $employee->specialties()->attach($specialties);
            $branches = Branch::inRandomOrder()->limit(rand(1, 2))->pluck('id');
            $employee->branches()->attach($branches);
        });

        // Создаем поставщиков
        $this->call(SupplierSeeder::class);

        // Создаем препараты и поставки
        Drug::factory(100)->create();
        DrugProcurement::factory(150)->create();

        // Создаем связи между филиалами и услугами
        Service::all()->each(function ($service) {
            $branches = Branch::inRandomOrder();
            
            // 70% услуг привязаны к одному случайному филиалу, 30% - к обоим филиалам
            if (fake()->boolean(70)) {
                $service->branches()->attach($branches->inRandomOrder()->first()->id);
            } else {
                $service->branches()->attach($branches->pluck('id'));
            }
        });

        // Создаем лабораторные анализы
        LabTest::factory(100)->create();
        // LabTestParam создается через LabTestParamSeeder
        LabTestResult::factory(300)->create();

        // Создаем расписание
        Schedule::factory(200)->create();

        // Создаем приемы
        Visit::factory(800)->create();

        // Создаем заказы с правильным распределением по клиентам
        Order::factory()->createWithDistribution();

        // Создаем вакцинации
        Vaccination::factory(150)->create();

        // Создаем симптомы и диагнозы
        Symptom::factory(400)->create();
        Diagnosis::factory(300)->create();

        // Создаем связи между приемами и заказами
        $this->createVisitOrderLinks();
    }

    /**
     * Создает связи между приемами и заказами для конверсии 70-90%
     */
    private function createVisitOrderLinks(): void
    {
        $visits = Visit::all();
        $orders = Order::all();
        
        // Целевая конверсия: 75-85% (случайно в этом диапазоне)
        $conversionRate = fake()->numberBetween(75, 85) / 100;
        $visitsToLinkCount = (int)($visits->count() * $conversionRate);
        
        // Случайно выбираем приемы для связывания
        $visitsToLink = $visits->random($visitsToLinkCount);
        
        // Отслеживаем уже использованные заказы
        $usedOrders = collect();
        
        foreach ($visitsToLink as $visit) {
            // Получаем заказы того же клиента и питомца, которые еще не использованы
            $availableClientOrders = $orders->where('client_id', $visit->client_id)
                                          ->where('pet_id', $visit->pet_id)
                                          ->whereNotIn('id', $usedOrders->pluck('id'));
            
            if ($availableClientOrders->isNotEmpty()) {
                // Максимум 2 заказа на приём
                $orderCount = fake()->randomElement([1, 1, 1, 1, 1, 1, 1, 1, 2, 2]);
                $ordersToLink = $availableClientOrders->random(min($availableClientOrders->count(), $orderCount));
                
                foreach ($ordersToLink as $order) {
                    VisitOrder::create([
                        'visit_id' => $visit->id,
                        'order_id' => $order->id,
                    ]);
                    
                    // Добавляем заказ в список использованных
                    $usedOrders->push($order);
                }
            } else {
                // Если нет доступных заказов того же клиента и питомца, 
                // ищем любой неиспользованный заказ
                $availableOrders = $orders->whereNotIn('id', $usedOrders->pluck('id'));
                
                if ($availableOrders->isNotEmpty()) {
                    $randomOrder = $availableOrders->random();
                    
                    VisitOrder::create([
                        'visit_id' => $visit->id,
                        'order_id' => $randomOrder->id,
                    ]);
                    
                    // Добавляем заказ в список использованных
                    $usedOrders->push($randomOrder);
                }
            }
        }
        
        echo "Создано связей visit_orders: " . VisitOrder::count() . " из " . $visits->count() . " приемов\n";
        echo "Конверсия: " . round((VisitOrder::distinct('visit_id')->count() / $visits->count()) * 100, 1) . "%\n";
        
        // Проверяем ограничения
        $maxOrdersPerVisit = VisitOrder::selectRaw('visit_id, COUNT(*) as order_count')
            ->groupBy('visit_id')
            ->orderBy('order_count', 'desc')
            ->first();
            
        $maxVisitsPerOrder = VisitOrder::selectRaw('order_id, COUNT(*) as visit_count')
            ->groupBy('order_id')
            ->orderBy('visit_count', 'desc')
            ->first();
            
        echo "Максимум заказов на приём: " . ($maxOrdersPerVisit ? $maxOrdersPerVisit->order_count : 0) . "\n";
        echo "Максимум приёмов на заказ: " . ($maxVisitsPerOrder ? $maxVisitsPerOrder->visit_count : 0) . "\n";
    }
}
