<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Ставропольская 79',
                'address' => 'ул. Ленина, д. 10, г. Москва',
                'phone' => '+7 (495) 123-45-67',
                'opens_at' => '2025-01-01 09:00:00',
                'closes_at' => '2025-01-01 21:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Красная 43',
                'address' => 'ул. Мира, д. 45, г. Москва',
                'phone' => '+7 (495) 987-65-43',
                'opens_at' => '2025-01-01 10:00:00',
                'closes_at' => '2025-01-01 19:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('branches')->insert($branches);
    }
}
