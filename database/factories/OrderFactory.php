<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Pet;
use App\Models\Status;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Vaccination;
use App\Models\OrderItem;
use App\Models\Drug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $notes = [
            'Срочный заказ',
            'Доставка на дом',
            'Скидка по карте',
            'Повторный заказ',
            'Рекомендация врача',
            'Акция',
            'VIP клиент',
            'Первый заказ',
            'Крупный заказ',
            'Сезонный заказ'
        ];

        // Сначала выбираем питомца, чтобы получить правильного клиента
        $pet = Pet::inRandomOrder()->first();
        $clientId = $pet->client_id;

        // Определяем статус завершения заказа (70% вероятность)
        $isClosed = $this->faker->optional(0.7)->boolean();
        $closedAt = $isClosed ? $this->faker->dateTimeBetween('-1 month', 'now') : null;
        
        // Если заказ завершен - он 100% оплачен, иначе 70% вероятность
        $isPaid = $isClosed ? true : ($this->faker->optional(0.7)->boolean() ?? false);

        return [
            'client_id' => $clientId,
            'pet_id' => $pet->id,
            'status_id' => Status::inRandomOrder()->first()->id,
            'branch_id' => Branch::inRandomOrder()->first()->id,
            'manager_id' => Employee::inRandomOrder()->first()->id,
            'notes' => $this->faker->optional(0.6)->randomElement($notes),
            'total' => $this->faker->randomFloat(2, 500, 15000),
            'is_paid' => $isPaid,
            'closed_at' => $closedAt,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function ($order) {
            // Добавляем вакцинации в заказ (30% вероятность)
            if ($this->faker->optional(0.7)->boolean()) {
                $vaccinations = Vaccination::where('pet_id', $order->pet_id)
                    ->with('drugs')
                    ->inRandomOrder()
                    ->limit($this->faker->numberBetween(1, 3))
                    ->get();

                foreach ($vaccinations as $vaccination) {
                    // Добавляем вакцинацию как элемент заказа
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_type' => Vaccination::class,
                        'item_id' => $vaccination->id,
                        'quantity' => 1,
                        'unit_price' => 0 // Вакцинации бесплатные в заказе
                    ]);

                    // Добавляем препараты из вакцинации
                    foreach ($vaccination->drugs as $drug) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'item_type' => Drug::class,
                            'item_id' => $drug->id,
                            'quantity' => $drug->pivot->dosage,
                            'unit_price' => $drug->price ?? 0
                        ]);
                    }
                }
            }
        });
    }
} 