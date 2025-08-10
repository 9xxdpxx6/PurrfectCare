<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\Employee;
use App\Models\VaccinationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vaccination>
 */
class VaccinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $administeredAt = $this->faker->dateTimeBetween('-2 years', 'now');
        
        // Определяем следующую дату вакцинации (обычно через 1-3 года)
        $nextDue = null;
        if ($this->faker->boolean(80)) { // 80% вакцинаций имеют следующую дату
            $nextDue = $this->faker->dateTimeBetween($administeredAt, '+3 years');
        }

        return [
            'vaccination_type_id' => VaccinationType::inRandomOrder()->first()?->id,
            'pet_id' => Pet::inRandomOrder()->first()->id,
            'veterinarian_id' => Employee::inRandomOrder()->first()->id,
            'administered_at' => $administeredAt,
            'next_due' => $nextDue,
            'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
        ];
    }
} 