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
    protected static array $consumedSlots = [];
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
        $pet = $client ? Pet::where('client_id', $client->id)->inRandomOrder()->first() : null;

        $chosenSchedule = null;
        $chosenSlot = null;

        // Пытаемся найти любой график с доступным 30-минутным слотом
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $schedule = Schedule::inRandomOrder()->first();
            if (!$schedule) {
                break;
            }

            $start = \Carbon\Carbon::parse($schedule->shift_starts_at)->copy();
            $end = \Carbon\Carbon::parse($schedule->shift_ends_at)->copy();

            // Округление старта до ближайших 0 или 30 минут
            $minutes = $start->minute;
            if ($minutes > 0 && $minutes < 30) {
                $start->setMinute(30)->setSecond(0)->setMicro(0);
            } elseif ($minutes > 30) {
                $start->addHour()->setMinute(0)->setSecond(0)->setMicro(0);
            } else {
                $start->setSecond(0)->setMicro(0);
            }

            // Получаем уже занятые слоты (в БД и те, что выбраны этой фабрикой в текущем процессе)
            $occupied = \App\Models\Visit::where('schedule_id', $schedule->id)
                ->pluck('starts_at')
                ->map(function ($dt) { return \Carbon\Carbon::parse($dt)->format('Y-m-d H:i:s'); })
                ->toArray();
            $consumed = array_keys(self::$consumedSlots[$schedule->id] ?? []);

            // Строим список доступных слотов 30 мин
            $available = [];
            $cursor = $start->copy();
            while ($cursor < $end) {
                $slot = $cursor->format('Y-m-d H:i:s');
                if (!in_array($slot, $occupied, true) && !in_array($slot, $consumed, true)) {
                    $available[] = $slot;
                }
                $cursor->addMinutes(30);
            }

            if (!empty($available)) {
                $chosenSchedule = $schedule;
                // Берем первый свободный (детерминированно), чтобы снизить шанс конфликта
                $chosenSlot = $available[0];
                self::$consumedSlots[$schedule->id][$chosenSlot] = true;
                break;
            }
        }

        // Если не нашли свободный слот — ищем по всем графикам первый доступный
        if (!$chosenSchedule || !$chosenSlot) {
            foreach (Schedule::orderBy('id')->cursor() as $schedule) {
                $start = \Carbon\Carbon::parse($schedule->shift_starts_at)->copy();
                $end = \Carbon\Carbon::parse($schedule->shift_ends_at)->copy();
                $minutes = $start->minute;
                if ($minutes > 0 && $minutes < 30) {
                    $start->setMinute(30)->setSecond(0)->setMicro(0);
                } elseif ($minutes > 30) {
                    $start->addHour()->setMinute(0)->setSecond(0)->setMicro(0);
                } else {
                    $start->setSecond(0)->setMicro(0);
                }

                $occupied = \App\Models\Visit::where('schedule_id', $schedule->id)
                    ->pluck('starts_at')
                    ->map(function ($dt) { return \Carbon\Carbon::parse($dt)->format('Y-m-d H:i:s'); })
                    ->toArray();
                $consumed = array_keys(self::$consumedSlots[$schedule->id] ?? []);

                $cursor = $start->copy();
                while ($cursor < $end) {
                    $slot = $cursor->format('Y-m-d H:i:s');
                    if (!in_array($slot, $occupied, true) && !in_array($slot, $consumed, true)) {
                        $chosenSchedule = $schedule;
                        $chosenSlot = $slot;
                        self::$consumedSlots[$schedule->id][$slot] = true;
                        break 2;
                    }
                    $cursor->addMinutes(30);
                }
            }

            // Если всё ещё ничего не нашли — оставим null, Eloquent бросит ошибку, лучше увидеть проблему вместимости
        }

        return [
            'client_id' => $client?->id ?? User::factory(),
            'pet_id' => $pet?->id,
            'schedule_id' => $chosenSchedule?->id ?? Schedule::factory(),
            'starts_at' => $chosenSlot,
            'status_id' => Status::inRandomOrder()->first()->id,
            'complaints' => $this->faker->randomElement($complaints),
            'notes' => $this->faker->optional(0.7)->randomElement($notes),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }
} 