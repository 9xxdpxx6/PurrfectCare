<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Новый', 'color' => '#007bff'],
            ['name' => 'В работе', 'color' => '#ffc107'],
            ['name' => 'Подтвержден', 'color' => '#28a745'],
            ['name' => 'Отменен', 'color' => '#dc3545'],
            ['name' => 'Завершен', 'color' => '#6c757d'],
            ['name' => 'Отложен', 'color' => '#fd7e14'],
            ['name' => 'Требует внимания', 'color' => '#e83e8c'],
            ['name' => 'В ожидании', 'color' => '#17a2b8']
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
} 