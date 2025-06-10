<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Миллиграмм', 'symbol' => 'мг'],
            ['name' => 'Грамм', 'symbol' => 'г'],
            ['name' => 'Килограмм', 'symbol' => 'кг'],
            ['name' => 'Миллилитр', 'symbol' => 'мл'],
            ['name' => 'Литр', 'symbol' => 'л'],
            ['name' => 'Штука', 'symbol' => 'шт'],
            ['name' => 'Ампула', 'symbol' => 'амп'],
            ['name' => 'Таблетка', 'symbol' => 'таб'],
            ['name' => 'Капсула', 'symbol' => 'капс'],
            ['name' => 'Упаковка', 'symbol' => 'уп'],
        ];

        DB::table('units')->insert($units);
    }
}
