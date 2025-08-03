<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            // Врачебные специальности (is_veterinarian = true)
            ['name' => 'Главный ветеринарный врач', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-терапевт', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-хирург', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-анестезиолог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-кардиолог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-дерматолог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-офтальмолог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-стоматолог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-невролог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-онколог', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-ортопед', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач УЗИ-диагностики', 'is_veterinarian' => true],
            ['name' => 'Ветеринарный врач-рентгенолог', 'is_veterinarian' => true],
            ['name' => 'Врач интенсивной терапии (реаниматолог)', 'is_veterinarian' => true],
            ['name' => 'Врач-репродуктолог', 'is_veterinarian' => true],
            ['name' => 'Врач-экзотолог (ратолог, герпетолог)', 'is_veterinarian' => true],

            // Средний и младший персонал (is_veterinarian = false)
            ['name' => 'Управляющий клиники', 'is_veterinarian' => false],
            ['name' => 'Администратор', 'is_veterinarian' => false],
            ['name' => 'Ассистент ветеринарного врача', 'is_veterinarian' => false],
            ['name' => 'Ветеринарный фельдшер', 'is_veterinarian' => false],
            ['name' => 'Лаборант', 'is_veterinarian' => false],
            ['name' => 'Грумер', 'is_veterinarian' => false],
            ['name' => 'Менеджер по закупкам', 'is_veterinarian' => false],
            ['name' => 'Санитар ветеринарный', 'is_veterinarian' => false],
            ['name' => 'Стажёр', 'is_veterinarian' => false],
        ];

        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(
                ['name' => $specialty['name']],
                $specialty
            );
        }
    }
}
