<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Pet;
use App\Models\Schedule;
use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $complaints = [
            'Плохой аппетит',
            'Рвота',
            'Диарея',
            'Кашель',
            'Чихание',
            'Хромота',
            'Зуд',
            'Выпадение шерсти',
            'Повышенная температура',
            'Вялость',
            'Агрессивность',
            'Проблемы с мочеиспусканием',
            'Проблемы с дыханием',
            'Покраснение глаз',
            'Выделения из носа',
            'Повреждение кожи',
            'Проблемы с зубами',
            'Паразиты',
            'Травма',
            'Профилактический осмотр'
        ];

        $notes = [
            'Пациент ведет себя спокойно',
            'Требуется дополнительное обследование',
            'Рекомендуется диета',
            'Необходимо повторить через неделю',
            'Состояние стабильное',
            'Требуется оперативное вмешательство',
            'Рекомендуется физиотерапия',
            'Пациент идет на поправку',
            'Требуется консультация специалиста',
            'Рекомендуется изменить рацион'
        ];

        $client = User::inRandomOrder()->first();
        $pet = Pet::where('client_id', $client->id)->inRandomOrder()->first();
        $schedule = Schedule::inRandomOrder()->first();
        
        // Генерируем время приёма в пределах рабочего времени ветеринара
        $shiftStart = $schedule->shift_starts_at;
        $shiftEnd = $schedule->shift_ends_at;
        
        // Создаем случайное время между началом и концом смены
        $visitTime = $this->faker->dateTimeBetween($shiftStart, $shiftEnd);

        return [
            'client_id' => $client->id,
            'pet_id' => $pet ? $pet->id : null,
            'schedule_id' => $schedule->id,
            'starts_at' => $visitTime,
            'status_id' => Status::inRandomOrder()->first()->id,
            'complaints' => $this->faker->randomElement($complaints),
            'notes' => $this->faker->optional(0.7)->randomElement($notes),
        ];
    }
} 