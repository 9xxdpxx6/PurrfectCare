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
        Visit::factory(300)->create();

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
     * Создает связи между приемами и заказами
     */
    private function createVisitOrderLinks(): void
    {
        $visits = Visit::all();
        $orders = Order::all();
        
        // Создаем связи для 60% приемов
        $visitsToLink = $visits->random(min($visits->count(), (int)($visits->count() * 0.6)));
        
        foreach ($visitsToLink as $visit) {
            // Получаем заказы того же клиента и питомца
            $clientOrders = $orders->where('client_id', $visit->client_id)
                                  ->where('pet_id', $visit->pet_id);
            
            if ($clientOrders->isNotEmpty()) {
                // Связываем с 1-3 подходящими заказами
                $ordersToLink = $clientOrders->random(min($clientOrders->count(), rand(1, 3)));
                
                foreach ($ordersToLink as $order) {
                    // Проверяем, что связь не существует
                    if (!VisitOrder::where('visit_id', $visit->id)
                                   ->where('order_id', $order->id)
                                   ->exists()) {
                        VisitOrder::create([
                            'visit_id' => $visit->id,
                            'order_id' => $order->id,
                        ]);
                    }
                }
            }
        }
        
        // Дополнительно создаем некоторые случайные связи (10% от общего количества)
        $randomLinksCount = min(50, (int)($visits->count() * 0.1));
        
        for ($i = 0; $i < $randomLinksCount; $i++) {
            $randomVisit = $visits->random();
            $randomOrder = $orders->random();
            
            // Проверяем, что связь не существует
            if (!VisitOrder::where('visit_id', $randomVisit->id)
                           ->where('order_id', $randomOrder->id)
                           ->exists()) {
                VisitOrder::create([
                    'visit_id' => $randomVisit->id,
                    'order_id' => $randomOrder->id,
                ]);
            }
        }
    }
}
