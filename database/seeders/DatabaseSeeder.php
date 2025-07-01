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
use App\Models\VaccinationDrug;
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
        $this->call(LabTestTypeSeeder::class);
        $this->call(DictionarySymptomSeeder::class);
        $this->call(DictionaryDiagnosisSeeder::class);

        // Создаем базовые данные
        Species::factory(6)->create();
        Breed::factory(20)->create();
        User::factory(50)->create();
        Pet::factory(100)->create();
        Specialty::factory(8)->create();

        // Создаем сотрудников с привязкой к специальностям и филиалам
        Employee::factory(30)->create()->each(function ($employee) {
            $specialties = Specialty::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $employee->specialties()->attach($specialties);
            $branches = Branch::inRandomOrder()->limit(rand(1, 2))->pluck('id');
            $employee->branches()->attach($branches);
        });

        // Создаем поставщиков
        Supplier::factory(30)->create();

        // Создаем препараты и поставки
        Drug::factory(100)->create();
        DrugProcurement::factory(150)->create();

        // Создаем услуги
        Service::factory(25)->create();

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
        LabTestParam::factory(50)->create();
        LabTestResult::factory(300)->create();

        // Создаем расписание
        Schedule::factory(200)->create();

        // Создаем визиты
        Visit::factory(300)->create();

        // Создаем заказы и элементы заказов
        Order::factory(200)->create();
        OrderItem::factory(400)->create();

        // Создаем вакцинации и препараты вакцинации
        Vaccination::factory(150)->create();
        VaccinationDrug::factory(200)->create();

        // Создаем симптомы и диагнозы
        Symptom::factory(400)->create();
        Diagnosis::factory(300)->create();
    }
}
