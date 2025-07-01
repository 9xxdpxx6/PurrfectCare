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
            ['name' => 'В обработке', 'color' => '#ffc107'],
            ['name' => 'Подтвержден', 'color' => '#28a745'],
            ['name' => 'Отменен', 'color' => '#dc3545'],
            ['name' => 'Завершен', 'color' => '#6c757d'],
            ['name' => 'Отложен', 'color' => '#fd7e14'],
            ['name' => 'Требует внимания', 'color' => '#e83e8c'],
            ['name' => 'В ожидании', 'color' => '#17a2b8'],
            ['name' => 'Выполняется', 'color' => '#20c997'],
            ['name' => 'Готов', 'color' => '#198754'],
            ['name' => 'Принят', 'color' => '#0d6efd'],
            ['name' => 'В работе', 'color' => '#ffca2c'],
            ['name' => 'Выполнен', 'color' => '#198754'],
            ['name' => 'Отклонен', 'color' => '#dc3545'],
            ['name' => 'На рассмотрении', 'color' => '#6f42c1']
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
} 