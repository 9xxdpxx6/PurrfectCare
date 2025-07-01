<?php

namespace Database\Factories;

use App\Models\Vaccination;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VaccinationDrug>
 */
class VaccinationDrugFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dosageRange = [0.5, 1.0];

        return [
            'vaccination_id' => Vaccination::inRandomOrder()->first()->id,
            'drug_id' => Drug::inRandomOrder()->first()->id,
            'batch_number' => $this->faker->regexify('[A-Z]{2}[0-9]{6}'),
            'dosage' => $this->faker->randomFloat(2, $dosageRange[0], $dosageRange[1]),
        ];
    }
} 