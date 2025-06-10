<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Species;
use App\Models\Breed;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Breed>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Миллиграмм',
                'Грамм',
                'Килограмм',
                'Миллилитр',
                'Литр',
                'Штука',
                'Ампула',
                'Таблетка',
                'Капсула',
                'Упаковка'
            ]),
            'symbol' => $this->faker->unique()->randomElement([
                'мг',
                'г',
                'кг',
                'мл',
                'л',
                'шт',
                'амп',
                'таб',
                'капс',
                'уп'
            ]),
        ];
    }
}
