<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\LabTestType;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabTest>
 */
class LabTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $receivedAt = $this->faker->dateTimeBetween('-6 months', 'now');
        
        // Определяем дату завершения (обычно через 1-7 дней)
        $completedAt = null;
        if ($this->faker->boolean(85)) { // 85% анализов завершены
            $completedAt = $this->faker->dateTimeBetween($receivedAt, '+7 days');
        }

        return [
            'pet_id' => Pet::inRandomOrder()->first()->id,
            'lab_test_type_id' => LabTestType::inRandomOrder()->first()->id,
            'veterinarian_id' => Employee::inRandomOrder()->first()->id,
            'received_at' => $receivedAt,
            'completed_at' => $completedAt,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
} 