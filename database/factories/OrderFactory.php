<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Pet;
use App\Models\Status;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Vaccination;
use App\Models\VaccinationType;
use App\Models\OrderItem;
use App\Models\Drug;
use App\Models\Service;
use App\Models\LabTest;
use App\Models\LabTestType;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

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
            'Сезонный заказ',
            'Консультация включена',
            'Дополнительная диагностика',
            'Профилактический осмотр',
            'Экстренная помощь',
            'Плановый приём'
        ];

        // Сначала выбираем питомца, чтобы получить правильного клиента
        $pet = Pet::inRandomOrder()->first();
        $clientId = $pet->client_id;

        // Определяем статус завершения заказа (70% вероятность)
        $isClosed = $this->faker->optional(0.7)->boolean();
        $closedAt = $isClosed ? $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s') : null;
        
        // Если заказ завершен - он 100% оплачен, иначе 70% вероятность
        $isPaid = $isClosed ? true : ($this->faker->optional(0.7)->boolean() ?? false);

        return [
            'client_id' => $clientId,
            'pet_id' => $pet->id,
            'status_id' => Status::inRandomOrder()->first()->id,
            'branch_id' => Branch::inRandomOrder()->first()->id,
            'manager_id' => Employee::inRandomOrder()->first()->id,
            'notes' => $this->faker->optional(0.6)->randomElement($notes),
            'total' => 0, // Будет рассчитано после добавления элементов
            'is_paid' => $isPaid,
            'closed_at' => $closedAt,
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Создает заказы с правильным распределением по клиентам
     */
    public function createWithDistribution(): void
    {
        $users = User::all();
        $pets = Pet::all();
        
        if ($users->isEmpty() || $pets->isEmpty()) {
            return;
        }
        
        $totalUsers = $users->count();
        $targetOrders = 800; // Увеличиваем целевое количество заказов
        $createdOrders = 0;
        
        // 35% клиентов с ровно 1 заказом
        $singleOrderUsers = $users->random(ceil($totalUsers * 0.35));
        
        // Остальные 65% клиентов
        $otherUsers = $users->diff($singleOrderUsers);
        
        // Создаем ровно 1 заказ для каждого клиента из первой группы
        foreach ($singleOrderUsers as $user) {
            $pet = Pet::where('client_id', $user->id)->first();
            if ($pet) {
                $this->createRealisticOrderForUser($user, $pet);
                $createdOrders++;
            }
        }
        
        // Создаем от 2 до 15 заказов для остальных клиентов (увеличиваем количество)
        foreach ($otherUsers as $user) {
            $pet = Pet::where('client_id', $user->id)->first();
            if ($pet) {
                $orderCount = $this->faker->numberBetween(3, 20); // Увеличили еще больше
                for ($i = 0; $i < $orderCount; $i++) {
                    $this->createRealisticOrderForUser($user, $pet);
                    $createdOrders++;
                }
            }
        }
        
        // Создаем дополнительные заказы для клиентов без питомцев
        $usersWithoutPets = $users->filter(function($user) {
            return !Pet::where('client_id', $user->id)->exists();
        });
        
        foreach ($usersWithoutPets as $user) {
            // Создаем заказ с случайным питомцем
            $randomPet = Pet::inRandomOrder()->first();
            if ($randomPet) {
                $orderCount = $this->faker->numberBetween(2, 8);
                for ($i = 0; $i < $orderCount; $i++) {
                    $this->createRealisticOrderForUser($user, $randomPet);
                    $createdOrders++;
                }
            }
        }

        // Создаем дополнительные заказы для достижения нужного количества
        $additionalOrders = $targetOrders - $createdOrders;
        
        if ($additionalOrders > 0) {
            // Создаем дополнительные заказы с разными датами
            for ($i = 0; $i < $additionalOrders; $i++) {
                $user = User::inRandomOrder()->first();
                $pet = Pet::where('client_id', $user->id)->first();
                
                if ($pet) {
                    // Создаем заказ с случайной датой в течение года
                    $this->createRealisticOrderForUserWithDate($user, $pet);
                    $createdOrders++;
                } else {
                    // Если у пользователя нет питомца, берем случайного
                    $randomPet = Pet::inRandomOrder()->first();
                    if ($randomPet) {
                        $this->createRealisticOrderForUserWithDate($user, $randomPet);
                        $createdOrders++;
                    }
                }
            }
        }
        
        // Если все еще мало заказов, создаем еще
        $currentCount = Order::count();
        if ($currentCount < $targetOrders) {
            $remainingOrders = $targetOrders - $currentCount;
            echo "Создаем дополнительные {$remainingOrders} заказов...\n";
            
            for ($i = 0; $i < $remainingOrders; $i++) {
                $user = User::inRandomOrder()->first();
                $pet = Pet::inRandomOrder()->first();
                
                if ($user && $pet) {
                    $this->createRealisticOrderForUserWithDate($user, $pet);
                }
            }
        }
        
        echo "Создано заказов: " . Order::count() . "\n";
    }

    /**
     * Создает реалистичный заказ для конкретного пользователя и питомца
     */
    public function createRealisticOrderForUser($user, $pet): array
    {
        try {
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
                'Сезонный заказ',
                'Консультация включена',
                'Дополнительная диагностика',
                'Профилактический осмотр',
                'Экстренная помощь',
                'Плановый приём'
            ];

            // Определяем тип заказа
            $orderType = $this->determineOrderType();
            
            // Определяем статус завершения заказа
            $isClosed = $this->determineOrderStatus($orderType);
            $closedAt = $isClosed ? $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s') : null;
            
            // Определяем оплату
            $isPaid = $this->determinePaymentStatus($isClosed, $orderType);

            // Заказ должен быть создан ПОСЛЕ регистрации клиента
            $orderDate = $this->faker->dateTimeBetween(Carbon::parse($user->created_at), 'now')->format('Y-m-d H:i:s');
            
            // Определяем статус заказа
            $statusId = $this->determineOrderStatusId($isClosed, $isPaid);
            
            $orderData = [
                'client_id' => $user->id,
                'pet_id' => $pet->id,
                'status_id' => $statusId,
                'branch_id' => Branch::inRandomOrder()->first()->id,
                'manager_id' => Employee::inRandomOrder()->first()->id,
                'notes' => $this->faker->optional(0.6)->randomElement($notes),
                'total' => 0, // Будет рассчитано после добавления элементов
                'is_paid' => $isPaid,
                'closed_at' => $closedAt,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];

            $itemData = $this->addRealisticOrderItems(0, $pet->id, $orderDate, $orderType);
            
            return ['order' => $orderData, 'items' => $itemData];
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем процесс
            echo "Ошибка создания заказа: " . $e->getMessage() . "\n";
            return ['order' => [], 'items' => []]; // Возвращаем пустой массив в случае ошибки
        }
    }

    /**
     * Создает реалистичный заказ для конкретного пользователя и питомца с случайной датой
     */
    public function createRealisticOrderForUserWithDate($user, $pet): array
    {
        try {
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
                'Сезонный заказ',
                'Консультация включена',
                'Дополнительная диагностика',
                'Профилактический осмотр',
                'Экстренная помощь',
                'Плановый приём'
            ];

            // Определяем тип заказа
            $orderType = $this->determineOrderType();
            
            // Определяем статус завершения заказа
            $isClosed = $this->determineOrderStatus($orderType);
            $closedAt = $isClosed ? $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s') : null;
            
            // Определяем оплату
            $isPaid = $this->determinePaymentStatus($isClosed, $orderType);

            // Заказ должен быть создан ПОСЛЕ регистрации клиента, но с более широким диапазоном дат
            $orderDate = $this->faker->dateTimeBetween(Carbon::parse($user->created_at), 'now')->format('Y-m-d H:i:s');
            
            // Определяем статус заказа
            $statusId = $this->determineOrderStatusId($isClosed, $isPaid);
            
            $orderData = [
                'client_id' => $user->id,
                'pet_id' => $pet->id,
                'status_id' => $statusId,
                'branch_id' => Branch::inRandomOrder()->first()->id,
                'manager_id' => Employee::inRandomOrder()->first()->id,
                'notes' => $this->faker->optional(0.6)->randomElement($notes),
                'total' => 0, // Будет рассчитано после добавления элементов
                'is_paid' => $isPaid,
                'closed_at' => $closedAt,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ];

            $itemData = $this->addRealisticOrderItems(0, $pet->id, $orderDate, $orderType);
            
            return ['order' => $orderData, 'items' => $itemData];
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем процесс
            echo "Ошибка создания заказа с датой: " . $e->getMessage() . "\n";
            return ['order' => [], 'items' => []]; // Возвращаем пустой массив в случае ошибки
        }
    }

    /**
     * Определяет тип заказа
     */
    private function determineOrderType(): string
    {
        $types = [
            'consultation' => 0.25,      // 25% - только консультация
            'vaccination' => 0.20,       // 20% - вакцинация
            'treatment' => 0.30,         // 30% - лечение (препараты + услуги)
            'diagnostic' => 0.15,        // 15% - диагностика (анализы + консультация)
            'complex' => 0.10            // 10% - комплексный (всё вместе)
        ];
        
        $random = $this->faker->randomFloat(2, 0, 1);
        $cumulative = 0;
        
        foreach ($types as $type => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $type;
            }
        }
        
        return 'consultation'; // По умолчанию
    }

    /**
     * Определяет статус завершения заказа
     */
    private function determineOrderStatus(string $orderType): bool
    {
        $completionRates = [
            'consultation' => 0.95,  // Консультации почти всегда завершаются
            'vaccination' => 0.90,   // Вакцинации часто завершаются
            'treatment' => 0.85,     // Лечение обычно завершается
            'diagnostic' => 0.80,    // Диагностика может быть отменена
            'complex' => 0.75        // Комплексные заказы могут быть сложными
        ];
        
        return $this->faker->optional($completionRates[$orderType])->boolean() ?? false;
    }

    /**
     * Определяет статус оплаты
     */
    private function determinePaymentStatus(bool $isClosed, string $orderType): bool
    {
        if ($isClosed) {
            // Завершенные заказы почти всегда оплачены
            return $this->faker->optional(0.95)->boolean() ?? true;
        }
        
        // Для незавершенных заказов вероятность оплаты зависит от типа
        $paymentRates = [
            'consultation' => 0.60,
            'vaccination' => 0.70,
            'treatment' => 0.50,
            'diagnostic' => 0.40,
            'complex' => 0.30
        ];
        
        return $this->faker->optional($paymentRates[$orderType])->boolean() ?? false;
    }

    /**
     * Определяет ID статуса заказа
     */
    private function determineOrderStatusId(bool $isClosed, bool $isPaid): int
    {
        if ($isClosed && $isPaid) {
            $completedStatus = Status::where('name', 'Завершен')->first();
            return $completedStatus ? $completedStatus->id : Status::inRandomOrder()->first()->id;
        } elseif ($isClosed && !$isPaid) {
            $cancelledStatus = Status::where('name', 'Отменён')->first();
            return $cancelledStatus ? $cancelledStatus->id : Status::inRandomOrder()->first()->id;
        } else {
            // Новый заказ
            $newStatus = Status::where('name', 'Новый')->first();
            return $newStatus ? $newStatus->id : Status::inRandomOrder()->first()->id;
        }
    }

    /**
     * Добавляет реалистичные элементы в заказ
     */
    private function addRealisticOrderItems(int $orderId, int $petId, string $orderCreatedAt, string $orderType): array
    {
        $allOrderItems = [];
        $allVaccinations = [];
        $allLabTests = [];

        switch ($orderType) {
            case 'consultation':
                $allOrderItems = array_merge($allOrderItems, $this->addConsultationItems($orderId));
                break;
            case 'vaccination':
                $vaccinationData = $this->addVaccinationItems($orderId, $petId, $orderCreatedAt);
                $allOrderItems = array_merge($allOrderItems, $vaccinationData['orderItems']);
                $allVaccinations = array_merge($allVaccinations, $vaccinationData['vaccinations']);
                break;
            case 'treatment':
                $allOrderItems = array_merge($allOrderItems, $this->addTreatmentItems($orderId));
                break;
            case 'diagnostic':
                $diagnosticData = $this->addDiagnosticItems($orderId, $petId, $orderCreatedAt);
                $allOrderItems = array_merge($allOrderItems, $diagnosticData['orderItems']);
                $allLabTests = array_merge($allLabTests, $diagnosticData['labTests']);
                break;
            case 'complex':
                $complexData = $this->addComplexItems($orderId, $petId, $orderCreatedAt);
                $allOrderItems = array_merge($allOrderItems, $complexData['orderItems']);
                $allVaccinations = array_merge($allVaccinations, $complexData['vaccinations']);
                $allLabTests = array_merge($allLabTests, $complexData['labTests']);
                break;
        }

        return [
            'orderItems' => $allOrderItems,
            'vaccinations' => $allVaccinations,
            'labTests' => $allLabTests,
        ];
    }

    /**
     * Добавляет элементы консультации
     */
    private function addConsultationItems(int $orderId): array
    {
        $orderItems = [];
        
        // Основная консультация (обязательно)
        $consultationService = Service::where('name', 'like', '%консультац%')
            ->orWhere('name', 'like', '%осмотр%')
            ->orWhere('name', 'like', '%приём%')
            ->inRandomOrder()
            ->first();
        
        if ($consultationService) {
            $orderItems[] = [
                'order_id' => $orderId,
                'item_type' => Service::class,
                'item_id' => $consultationService->id,
                'quantity' => 1,
                'unit_price' => $consultationService->price
            ];
        }
        
        // Дополнительная консультация (30% вероятность)
        if ($this->faker->optional(0.3)->boolean()) {
            $additionalService = Service::where('name', 'not like', '%консультац%')
                ->where('name', 'not like', '%осмотр%')
                ->where('name', 'not like', '%приём%')
                ->inRandomOrder()
                ->first();
            
            if ($additionalService) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => Service::class,
                    'item_id' => $additionalService->id,
                    'quantity' => 1,
                    'unit_price' => $additionalService->price
                ];
            }
        }
        
        return $orderItems;
    }
    
    /**
     * Добавляет элементы вакцинации
     */
    private function addVaccinationItems(int $orderId, int $petId, string $orderCreatedAt): array
    {
        $orderItems = [];
        $vaccinations = [];
        
        // Получаем случайный тип вакцинации
        $vaccinationType = VaccinationType::inRandomOrder()->first();
        
        if (!$vaccinationType) {
            return ['orderItems' => [], 'vaccinations' => []]; // Если нет типов вакцинации, пропускаем
        }
        
        // Создаем вакцинацию для питомца
        $vaccinations[] = [
            'pet_id' => $petId,
            'veterinarian_id' => Employee::inRandomOrder()->first()->id,
            'vaccination_type_id' => $vaccinationType->id,
            'administered_at' => $orderCreatedAt,
            'next_due' => Carbon::parse($orderCreatedAt)->addYear()
        ];
        
        // Добавляем вакцинацию как услугу
        $vaccinationService = Service::where('name', 'like', '%вакцин%')
            ->orWhere('name', 'like', '%прививк%')
            ->inRandomOrder()
            ->first();
        
        if ($vaccinationService) {
            $orderItems[] = [
                'order_id' => $orderId,
                'item_type' => Service::class,
                'item_id' => $vaccinationService->id,
                'quantity' => 1,
                'unit_price' => $vaccinationService->price
            ];
        }
        
        // Добавляем препараты для вакцинации (через тип вакцинации)
        if ($vaccinationType->drugs) {
            foreach ($vaccinationType->drugs as $drug) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => Drug::class,
                    'item_id' => $drug->id,
                    'quantity' => 1,
                    'unit_price' => $drug->price
                ];
            }
        }
        
        // Добавляем тип вакцинации как элемент заказа
        $orderItems[] = [
            'order_id' => $orderId,
            'item_type' => VaccinationType::class,
            'item_id' => $vaccinationType->id,
            'quantity' => 1,
            'unit_price' => $vaccinationType->price
        ];
        
        return ['orderItems' => $orderItems, 'vaccinations' => $vaccinations];
    }

    /**
     * Добавляет элементы лечения
     */
    private function addTreatmentItems(int $orderId): array
    {
        $orderItems = [];
        
        // Консультация врача (обязательно)
        $orderItems = array_merge($orderItems, $this->addConsultationItems($orderId));
        
        // Препараты для лечения
        $treatmentDrugs = Drug::where('name', 'not like', '%вакцин%')
            ->where('name', 'not like', '%прививк%')
            ->inRandomOrder()
            ->limit($this->faker->numberBetween(2, 6))
            ->get();
        
        foreach ($treatmentDrugs as $drug) {
            $orderItems[] = [
                'order_id' => $orderId,
                'item_type' => Drug::class,
                'item_id' => $drug->id,
                'quantity' => $this->faker->numberBetween(1, 5),
                'unit_price' => $drug->price
            ];
        }
        
        // Дополнительные услуги (50% вероятность)
        if ($this->faker->optional(0.5)->boolean()) {
            $treatmentServices = Service::where('name', 'not like', '%консультац%')
                ->where('name', 'not like', '%осмотр%')
                ->where('name', 'not like', '%приём%')
                ->where('name', 'not like', '%вакцин%')
                ->inRandomOrder()
                ->limit($this->faker->numberBetween(1, 3))
                ->get();
            
            foreach ($treatmentServices as $service) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => Service::class,
                    'item_id' => $service->id,
                    'quantity' => 1,
                    'unit_price' => $service->price
                ];
            }
        }
        
        return $orderItems;
    }
    
    /**
     * Добавляет элементы диагностики
     */
    private function addDiagnosticItems(int $orderId, int $petId, string $orderCreatedAt): array
    {
        $orderItems = [];
        $labTests = [];
        
        // Консультация врача (обязательно)
        $orderItems = array_merge($orderItems, $this->addConsultationItems($orderId));
        
        // Лабораторные анализы
        $labTestTypes = LabTestType::inRandomOrder()
            ->limit($this->faker->numberBetween(1, 4))
            ->get();
        
        foreach ($labTestTypes as $labTestType) {
            // Создаем лабораторное исследование
            $labTests[] = [
                'pet_id' => $petId,
                'lab_test_type_id' => $labTestType->id,
                'veterinarian_id' => Employee::inRandomOrder()->first()->id,
                'received_at' => $orderCreatedAt,
                'completed_at' => Carbon::parse($orderCreatedAt)->addHours($this->faker->numberBetween(1, 24))
            ];
            
            // Добавляем анализ как элемент заказа (ID будет обновлен после массовой вставки LabTest)
            $orderItems[] = [
                'order_id' => $orderId,
                'item_type' => LabTest::class,
                'item_id' => 0, // Заглушка, будет обновлено
                'quantity' => 1,
                'unit_price' => $labTestType->price
            ];
        }
        
        // Дополнительные диагностические услуги (40% вероятность)
        if ($this->faker->optional(0.4)->boolean()) {
            $diagnosticServices = Service::where('name', 'like', '%рентген%')
                ->orWhere('name', 'like', '%узи%')
                ->orWhere('name', 'like', '%экг%')
                ->orWhere('name', 'like', '%диагност%')
                ->inRandomOrder()
                ->limit($this->faker->numberBetween(1, 2))
                ->get();
            
            foreach ($diagnosticServices as $service) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => Service::class,
                    'item_id' => $service->id,
                    'quantity' => 1,
                    'unit_price' => $service->price
                ];
            }
        }
        
        return ['orderItems' => $orderItems, 'labTests' => $labTests];
    }
    
    /**
     * Добавляет комплексные элементы
     */
    private function addComplexItems(int $orderId, int $petId, string $orderCreatedAt): array
    {
        $orderItems = [];
        $vaccinations = [];
        $labTests = [];
        
        // Консультация врача (обязательно)
        $orderItems = array_merge($orderItems, $this->addConsultationItems($orderId));
        
        // Вакцинация (60% вероятность)
        if ($this->faker->optional(0.6)->boolean()) {
            $vaccinationData = $this->addVaccinationItems($orderId, $petId, $orderCreatedAt);
            $orderItems = array_merge($orderItems, $vaccinationData['orderItems']);
            $vaccinations = array_merge($vaccinations, $vaccinationData['vaccinations']);
        }
        
        // Препараты (обязательно)
        $drugs = Drug::inRandomOrder()
            ->limit($this->faker->numberBetween(3, 8))
            ->get();
        
        foreach ($drugs as $drug) {
            $orderItems[] = [
                'order_id' => $orderId,
                'item_type' => Drug::class,
                'item_id' => $drug->id,
                'quantity' => $this->faker->numberBetween(1, 4),
                'unit_price' => $drug->price
            ];
        }
        
        // Анализы (70% вероятность)
        if ($this->faker->optional(0.7)->boolean()) {
            $labTestTypes = LabTestType::inRandomOrder()
                ->limit($this->faker->numberBetween(1, 3))
                ->get();
            
            foreach ($labTestTypes as $labTestType) {
                $labTests[] = [
                    'pet_id' => $petId,
                    'lab_test_type_id' => $labTestType->id,
                    'veterinarian_id' => Employee::inRandomOrder()->first()->id,
                    'received_at' => $orderCreatedAt,
                    'completed_at' => Carbon::parse($orderCreatedAt)->addHours($this->faker->numberBetween(1, 24))
                ];
                
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => LabTest::class,
                    'item_id' => 0, // Заглушка, будет обновлено
                    'quantity' => 1,
                    'unit_price' => $labTestType->price
                ];
            }
        }
        
        // Дополнительные услуги (80% вероятность)
        if ($this->faker->optional(0.8)->boolean()) {
            $services = Service::inRandomOrder()
                ->limit($this->faker->numberBetween(2, 5))
                ->get();
            
            foreach ($services as $service) {
                $orderItems[] = [
                    'order_id' => $orderId,
                    'item_type' => Service::class,
                    'item_id' => $service->id,
                    'quantity' => 1,
                    'unit_price' => $service->price
                ];
            }
        }
        
        return ['orderItems' => $orderItems, 'vaccinations' => $vaccinations, 'labTests' => $labTests];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this;
    }
} 