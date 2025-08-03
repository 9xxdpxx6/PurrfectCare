<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $species = [
            ['name' => 'Собака'],
            ['name' => 'Кошка'],
            ['name' => 'Хомяк'],
            ['name' => 'Морская свинка'],
            ['name' => 'Кролик'],
            ['name' => 'Попугай'],
            ['name' => 'Канарейка'],
            ['name' => 'Волнистый попугай'],
            ['name' => 'Черепаха'],
            ['name' => 'Хорёк'],
            ['name' => 'Шиншилла'],
            ['name' => 'Дегу'],
            ['name' => 'Крыса'],
            ['name' => 'Мышь'],
            ['name' => 'Песчанка'],
            ['name' => 'Хорь'],
            ['name' => 'Еж'],
            ['name' => 'Сахарный поссум'],
            ['name' => 'Игуана'],
            ['name' => 'Змея'],
            ['name' => 'Ящерица'],
            ['name' => 'Рыбка'],
            ['name' => 'Краб'],
            ['name' => 'Улитка'],
            ['name' => 'Паук']
        ];

        foreach ($species as $specie) {
            Species::firstOrCreate(['name' => $specie['name']]);
        }
    }
} 