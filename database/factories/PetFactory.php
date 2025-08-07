<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Breed;
use App\Models\User;
use App\Models\Pet;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Получаем случайную породу (breed)
        $breed = Breed::inRandomOrder()->first() ?? Breed::factory()->create();

        // Получаем случайного пользователя (client)
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        // Проверяем, есть ли уже записи в таблице pets
        $existingPets = Pet::where('breed_id', $breed->id)->get();

        if ($existingPets->count() > 0) {
            // Если записи есть, выбираем случайного питомца
            return [
                'id' => $this->faker->randomElement($existingPets)->id,
                'breed_id' => $breed->id,
                'client_id' => $user->id,
                'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
                'updated_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            ];
        }

        // Если записей нет, создаем нового питомца
        return [
            'name' => $this->faker->randomElement([
                'Бобик', 'Рекс', 'Шарик', 'Рыжик', 'Персик', 'Мурзик', 'Тузик', 'Барсик',
                'Дружок', 'Пушок', 'Малыш', 'Лео', 'Симба', 'Тоша', 'Марта', 'Лайма',
                'Бим', 'Чарли', 'Макс', 'Белка', 'Стрелка', 'Грей', 'Мила', 'Зефир'
            ]) . ' ' . $this->faker->optional(0.5)->randomElement([
                '', 'младший', 'старший', ' I', ' II', ' III', ' IV', ' V', ' VI', 'обыкновенный',
                ' Рыжий', ' Маленький', ' Большой', ' Пушистый', ' Храбрый', ' Умный'
            ]),
            'breed_id' => $breed->id,
            'birthdate' => $this->faker->optional()->dateTimeBetween('-10 years', '-1 year'),
            'client_id' => $user->id,
            'temperature' => $this->faker->optional()->randomFloat(2, 35.0, 40.0),
            'weight' => $this->faker->optional()->randomFloat(2, 1.0, 50.0),
            'gender' => $this->faker->randomElement(array_merge(
                array_fill(0, 45, 'male'),   // 45% male
                array_fill(0, 45, 'female'), // 45% female
                array_fill(0, 10, 'unknown') // 10% unknown
            )),
            'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
        ];
    }
}
