<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Species;
use App\Models\Breed;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Breed>
 */
class DrugFactory extends Factory
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
                    'Аспирин',
                    'Парацетамол',
                    'Ибупрофен',
                    'Нурофен',
                    'Цитрамон',
                    'Супрастин',
                    'Левомицетин',
                    'Амоксициллин',
                    'Кеторол',
                    'Нимесулид',
                    'Анальгин',
                    'Диклофенак',
                    'Панадол',
                    'Эффералган',
                    'Кларитин',
                    'Зодак',
                    'Тавегил',
                    'Фенистил',
                    'Лоратадин',
                    'Цетрин',
                    'Аугментин',
                    'Флемоксин Солютаб',
                    'Азитромицин',
                    'Сумамед',
                    'Цефтриаксон',
                    'Ципрофлоксацин',
                    'Левофлоксацин',
                    'Омепразол',
                    'Ранитидин',
                    'Фестал',
                    'Мезим Форте',
                    'Креон',
                    'Панкреатин',
                    'Гастал',
                    'Альмагель',
                    'Ренни',
                    'Валериана',
                    'Персен',
                    'Корвалол',
                    'Валокордин',
                    'Кардиомагнил',
                    'Аспаркам',
                    'Магне В6',
                    'Парацетамол Экстра',
                    'Найз',
                    'Мовалис',
                    'Баралгин',
                    'Но-шпа',
                    'Спазмалгон',
                    'Папаверин',
                    'Эуфиллин',
                    'Гепабене',
                    'Урсофальк',
                    'Холосас',
                    'Канефрон',
                    'Фурагин',
                    'Фурадонин'
                ]) . ' ' . $this->faker->randomElement([
                    'таблетки',
                    'капсулы',
                    'раствор',
                    'мазь',
                    'гель',
                    'суспензия',
                    'сироп',
                    'порошок',
                    'инъекции',
                    'спрей'
                ]),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'prescription_required' => $this->faker->boolean(30),
            'unit_id' => Unit::inRandomOrder()->first()->id ?? Unit::factory()->create()->id,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
