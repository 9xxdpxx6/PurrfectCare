<?php

namespace Database\Factories;

use App\Models\Drug;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Species;
use App\Models\Breed;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Breed>
 */
class DrugProcurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::inRandomOrder()->first()->id ?? Supplier::factory()->create()->id,
            'drug_id' => Drug::inRandomOrder()->first()->id ?? Drug::factory()->create()->id,
            'delivery_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'manufacture_date' => $this->faker->dateTimeBetween('-5 years', '-1 year'),
            'packaging_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'price' => $this->faker->randomFloat(2, 50, 4000),
            'quantity' => $this->faker->numberBetween(10, 500),
        ];
    }
}
