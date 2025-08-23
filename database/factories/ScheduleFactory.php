<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Создаем смену в рабочее время (8:00 - 20:00)
        $shiftStartsAt = $this->faker->dateTimeBetween('-3 months', '+1 month');
        $shiftStartsAt->setTime(
            $this->faker->numberBetween(8, 12), // Час начала смены
            $this->faker->randomElement([0, 15, 30, 45]) // Минуты
        );

        // Длительность смены 4-12 часов
        $shiftDuration = $this->faker->numberBetween(4, 12);
        $shiftEndsAt = clone $shiftStartsAt;
        $shiftEndsAt->add(new \DateInterval("PT{$shiftDuration}H"));

        return [
            'veterinarian_id' => Employee::inRandomOrder()->first()->id,
            'branch_id' => Branch::inRandomOrder()->first()->id,
            'shift_starts_at' => $shiftStartsAt->format('Y-m-d H:i:s'),
            'shift_ends_at' => $shiftEndsAt->format('Y-m-d H:i:s'),
        ];
    }
} 