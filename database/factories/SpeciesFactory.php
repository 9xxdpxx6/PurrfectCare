<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Species;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Species>
 */
class SpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Проверяем, есть ли уже записи в таблице species
        $existingSpecies = Species::all();

        if ($existingSpecies->count() > 0) {
            // Если записи есть, выбираем случайный вид
            return [
                'id' => $this->faker->randomElement($existingSpecies)->id,
            ];
        }

        // Если записей нет, создаем новую
        return [
            'name' => $this->faker->randomElement([
                'Собака', 'Кошка', 'Хомяк', 'Попугай', 'Кролик', 'Черепаха'
            ]),
        ];
    }
}
