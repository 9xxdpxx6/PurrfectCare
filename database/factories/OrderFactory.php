<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Pet;
use App\Models\Status;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notes = [
            'Срочный заказ',
            'Доставка на дом',
            'Скидка по карте',
            'Повторный заказ',
            'Рекомендация врача',
            'Акция',
            'VIP клиент',
            'Первый заказ',
            'Крупный заказ',
            'Сезонный заказ'
        ];

        return [
            'client_id' => User::inRandomOrder()->first()->id,
            'pet_id' => Pet::inRandomOrder()->first()->id,
            'status_id' => Status::inRandomOrder()->first()->id,
            'branch_id' => Branch::inRandomOrder()->first()->id,
            'manager_id' => Employee::inRandomOrder()->first()->id,
            'notes' => $this->faker->optional(0.6)->randomElement($notes),
            'total' => $this->faker->randomFloat(2, 500, 15000),
        ];
    }
} 