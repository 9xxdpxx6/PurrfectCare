<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Species;
use App\Models\Breed;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Breed>
 */
class BreedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Получаем случайный вид (species)
        $species = Species::inRandomOrder()->first() ?? Species::factory()->create();

        // Проверяем, есть ли уже записи в таблице breeds
        $existingBreeds = Breed::where('species_id', $species->id)->get();

        if ($existingBreeds->count() > 0) {
            // Если записи есть, выбираем случайную породу
            return [
                'id' => $this->faker->randomElement($existingBreeds)->id,
                'species_id' => $species->id,
            ];
        }

        // Если записей нет, создаем новую
        return [
            'name' => $this->faker->randomElement([
                'Лабрадор', 'Немецкая овчарка', 'Мейн-кун', 'Британская короткошёрстная',
                'Сиамская кошка', 'Дворняга', 'Сфинкс', 'Пудель', 'Шпиц', 'Русская голубая'
            ]),
            'species_id' => $species->id,
        ];
    }
}
