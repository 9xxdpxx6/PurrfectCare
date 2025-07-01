<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Drug;
use App\Models\Service;
use App\Models\LabTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Случайно выбираем тип товара (препарат, услуга или лабораторный анализ)
        $itemType = $this->faker->randomElement(['drug', 'service', 'lab_test']);
        
        if ($itemType === 'drug') {
            $item = Drug::inRandomOrder()->first();
            $itemType = Drug::class;
            $unitPrice = $item->price;
        } elseif ($itemType === 'service') {
            $item = Service::inRandomOrder()->first();
            $itemType = Service::class;
            $unitPrice = $item->price;
        } else {
            // Для лабораторных анализов используем реалистичную цену
            $item = LabTest::inRandomOrder()->first();
            $itemType = LabTest::class;
            $unitPrice = $this->faker->randomFloat(2, 500, 3000); // Цена лабораторного анализа
        }

        return [
            'order_id' => Order::inRandomOrder()->first()->id,
            'item_type' => $itemType,
            'item_id' => $item->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'unit_price' => $unitPrice,
        ];
    }
} 