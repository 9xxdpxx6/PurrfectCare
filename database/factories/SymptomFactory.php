<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Models\DictionarySymptom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Symptom>
 */
class SymptomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customSymptoms = [
            'Необычное поведение',
            'Изменение привычек',
            'Странные звуки',
            'Необычный запах',
            'Изменение цвета',
            'Необычные движения',
            'Странная реакция на еду',
            'Необычная активность',
            'Изменение сна',
            'Странная походка'
        ];

        $notes = [
            'Симптом появился недавно',
            'Симптом усиливается',
            'Симптом ослабевает',
            'Симптом постоянный',
            'Симптом периодический',
            'Симптом связан с едой',
            'Симптом связан с активностью',
            'Симптом в определенное время',
            'Симптом после травмы',
            'Симптом наследственный'
        ];

        // 70% симптомов из справочника, 30% - кастомные
        $useDictionary = $this->faker->boolean(70);

        return [
            'visit_id' => Visit::inRandomOrder()->first()->id,
            'dictionary_symptom_id' => $useDictionary ? DictionarySymptom::inRandomOrder()->first()->id : null,
            'custom_symptom' => $useDictionary ? null : $this->faker->randomElement($customSymptoms),
            'notes' => $this->faker->optional(0.6)->randomElement($notes),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }
} 