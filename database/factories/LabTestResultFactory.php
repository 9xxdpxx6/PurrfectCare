<?php

namespace Database\Factories;

use App\Models\LabTest;
use App\Models\LabTestParam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LabTestResult>
 */
class LabTestResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notes = [
            'В пределах нормы',
            'Незначительное отклонение',
            'Требуется повторный анализ',
            'Критическое значение',
            'Результат сомнительный',
            'Требуется консультация специалиста',
            'Норма для возраста',
            'Патологическое значение',
            'Пограничное значение',
            'Результат в норме'
        ];

        return [
            'lab_test_id' => LabTest::inRandomOrder()->first()->id,
            'lab_test_param_id' => LabTestParam::inRandomOrder()->first()->id,
            'value' => $this->faker->optional(0.9)->randomFloat(2, 0.1, 1000),
            'notes' => $this->faker->optional(0.3)->randomElement($notes),
        ];
    }
} 