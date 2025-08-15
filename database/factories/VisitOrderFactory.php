<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VisitOrder>
 */
class VisitOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visit_id' => \App\Models\Visit::inRandomOrder()->first()?->id ?? \App\Models\Visit::factory(),
            'order_id' => \App\Models\Order::inRandomOrder()->first()?->id ?? \App\Models\Order::factory(),
        ];
    }
}
