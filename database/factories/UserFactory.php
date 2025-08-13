<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Проверяем, есть ли уже записи в таблице users
        $existingUsers = \App\Models\User::all();

        if ($existingUsers->count() > 0) {
            // Если записи есть, выбираем случайного пользователя
            return [
                'id' => $this->faker->randomElement($existingUsers)->id,
            ];
        }

        // Если записей нет, создаем нового пользователя
        return [
            'name' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+7' . $this->faker->numerify('##########'), // Российский формат телефона
            'address' => $this->faker->address,
            'telegram' => $this->faker->optional(0.7)->userName, // 70% вероятность наличия telegram
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Установите пароль по умолчанию
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
