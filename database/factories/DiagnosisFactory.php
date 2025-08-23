<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Models\DictionaryDiagnosis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diagnosis>
 */
class DiagnosisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customDiagnoses = [
            'Смешанная патология',
            'Комбинированное заболевание',
            'Сложный случай',
            'Множественная патология',
            'Сочетанное заболевание',
            'Полиэтиологическая патология',
            'Мультисистемное поражение',
            'Генерализованное заболевание',
            'Диффузная патология',
            'Системное поражение'
        ];

        $treatmentPlans = [
            'Антибиотикотерапия курсом 7-10 дней',
            'Противовоспалительная терапия',
            'Диетотерапия с исключением аллергенов',
            'Хирургическое лечение',
            'Физиотерапия',
            'Гормональная терапия',
            'Иммуномодулирующая терапия',
            'Витаминотерапия',
            'Противопаразитарная обработка',
            'Антигистаминная терапия',
            'Обезболивающая терапия',
            'Жаропонижающая терапия',
            'Дезинтоксикационная терапия',
            'Регидратационная терапия',
            'Коррекция питания',
            'Наблюдение в динамике',
            'Повторный осмотр через неделю',
            'Контроль анализов',
            'Рентгенологический контроль',
            'УЗИ контроль'
        ];

        // 80% диагнозов из справочника, 20% - кастомные
        $useDictionary = $this->faker->boolean(80);

        return [
            'visit_id' => Visit::inRandomOrder()->first()->id,
            'dictionary_diagnosis_id' => $useDictionary ? DictionaryDiagnosis::inRandomOrder()->first()->id : null,
            'custom_diagnosis' => $useDictionary ? null : $this->faker->randomElement($customDiagnoses),
            'treatment_plan' => $this->faker->optional(0.8)->randomElement($treatmentPlans),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }
} 