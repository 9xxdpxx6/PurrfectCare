<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Specialty>
 */
class SpecialtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Хирург', 'Терапевт', 'Офтальмолог', 'Дерматолог', 'Стоматолог',
                'Администратор', 'Лаборант', 'Менеджер'
            ]),
            'is_veterinarian' => $this->faker->boolean(70),
        ];
    }
}
