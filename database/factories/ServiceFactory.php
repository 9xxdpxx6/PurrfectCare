<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'Консультация ветеринара' => ['price' => [500, 1500], 'duration' => [15, 30]],
            'Вакцинация' => ['price' => [800, 2000], 'duration' => [20, 45]],
            'Стерилизация/кастрация' => ['price' => [3000, 8000], 'duration' => [60, 120]],
            'Чистка зубов' => ['price' => [1500, 3500], 'duration' => [30, 60]],
            'Стрижка когтей' => ['price' => [300, 800], 'duration' => [10, 20]],
            'Анализ крови' => ['price' => [1200, 2500], 'duration' => [15, 30]],
            'УЗИ' => ['price' => [2000, 5000], 'duration' => [30, 60]],
            'Рентген' => ['price' => [1500, 4000], 'duration' => [20, 40]],
            'Хирургическая операция' => ['price' => [5000, 15000], 'duration' => [120, 240]],
            'Лечение зубов' => ['price' => [2500, 6000], 'duration' => [45, 90]],
            'Обработка от паразитов' => ['price' => [400, 1200], 'duration' => [15, 30]],
            'Микрочипирование' => ['price' => [800, 1500], 'duration' => [10, 20]],
            'ЭКГ' => ['price' => [1800, 3500], 'duration' => [25, 45]],
            'Биохимический анализ' => ['price' => [1500, 3000], 'duration' => [20, 40]],
            'Цитологическое исследование' => ['price' => [2000, 4500], 'duration' => [30, 60]],
            'Физиотерапия' => ['price' => [800, 2000], 'duration' => [30, 60]],
            'Массаж' => ['price' => [600, 1500], 'duration' => [20, 40]],
            'Груминг' => ['price' => [1000, 3000], 'duration' => [60, 120]],
            'Экстренная помощь' => ['price' => [2000, 5000], 'duration' => [30, 90]],
            'Дневной стационар' => ['price' => [1500, 3000], 'duration' => [480, 960]],
        ];

        $service = $this->faker->randomElement(array_keys($services));
        $priceRange = $services[$service]['price'];
        $durationRange = $services[$service]['duration'];

        return [
            'name' => $service,
            'price' => $this->faker->numberBetween($priceRange[0], $priceRange[1]),
            'description' => $this->faker->paragraph(2),
            'duration' => $this->faker->numberBetween($durationRange[0], $durationRange[1]),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }
} 