<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
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
                'Центральный филиал',
                'Северный филиал',
                'Южный филиал',
                'Восточный филиал',
                'Западный филиал',
                'Ветклиника "Лапы и хвосты"',
                'Ветцентр "Здоровые питомцы"',
                'Клиника "Четыре лапы"',
                'Ветлечебница "Добрый доктор"',
                'Ветклиника "Айболит"'
            ]) . ' - ' . $this->faker->city(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'working_hours' => '08:00-20:00',
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }


}

