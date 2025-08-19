<?php

namespace App\Services\Bot;

use App\Models\Breed;
use App\Models\Pet;
use App\Models\TelegramProfile;
use Illuminate\Support\Facades\Log;

class PetService
{
    public function __construct(
        private TelegramApiService $apiService
    ) {
    }

    public function startAddingPet(string $chatId, TelegramProfile $profile): array
    {
        if (!$profile->user_id) {
            return [
                'action' => 'send_message',
                'message' => '❌ Для добавления питомца необходимо завершить регистрацию.',
                'keyboard' => [
                    [
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $profile->state = 'adding_pet_name';
        $profile->save();

        return [
            'action' => 'send_message',
            'message' => '🐾 Введите кличку питомца:',
            'keyboard' => []
        ];
    }

    public function handlePetName(string $chatId, TelegramProfile $profile, string $petName): array
    {
        if (strlen($petName) < 2) {
            return [
                'action' => 'send_message',
                'message' => '❌ Кличка должна содержать минимум 2 символа. Попробуйте еще раз:',
                'keyboard' => []
            ];
        }

        $profile->data = array_merge($profile->data ?? [], ['pet_name' => $petName]);
        $profile->state = 'selecting_species';
        $profile->save();

        return $this->sendSpecies($chatId);
    }

    public function sendSpecies(string $chatId): array
    {
        $species = \App\Models\Species::select('id', 'name')
            ->orderBy('name')
            ->get();

        if ($species->isEmpty()) {
            return [
                'action' => 'send_message',
                'message' => '❌ В базе нет доступных видов животных.',
                'keyboard' => [
                    [
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $keyboard = [];
        foreach ($species as $specie) {
            $keyboard[] = [[
                'text' => $specie->name,
                'callback_data' => "species:{$specie->id}"
            ]];
        }

        // Добавляем кнопку отмены
        $keyboard[] = [
            ['text' => '❌ Отмена', 'callback_data' => 'main_menu']
        ];

        return [
            'action' => 'send_message',
            'message' => '🐕 Выберите вид животного:',
            'keyboard' => $keyboard
        ];
    }

    public function handleSpeciesSelection(string $chatId, TelegramProfile $profile, int $speciesId): array
    {
        $profile->data = array_merge($profile->data ?? [], ['selected_species_id' => $speciesId]);
        $profile->state = 'selecting_breed';
        $profile->save();

        return $this->sendBreeds($chatId, $speciesId);
    }

    public function sendBreeds(string $chatId, int $speciesId): array
    {
        $breeds = Breed::select('id', 'name')
            ->where('species_id', $speciesId)
            ->orderBy('name')
            ->get();

        if ($breeds->isEmpty()) {
            return [
                'action' => 'send_message',
                'message' => '❌ Для выбранного вида нет доступных пород.',
                'keyboard' => [
                    [
                        ['text' => '⬅️ Назад к видам', 'callback_data' => 'back_to_species'],
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $keyboard = [];
        foreach ($breeds as $breed) {
            $keyboard[] = [[
                'text' => $breed->name,
                'callback_data' => "breed:{$breed->id}"
            ]];
        }

        // Добавляем кнопки навигации
        $keyboard[] = [
            ['text' => '⬅️ Назад к видам', 'callback_data' => 'back_to_species'],
            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
        ];

        return [
            'action' => 'send_message',
            'message' => '🐕 Выберите породу питомца:',
            'keyboard' => $keyboard
        ];
    }

    public function handleBreedSelection(string $chatId, TelegramProfile $profile, int $breedId): array
    {
        $profile->data = array_merge($profile->data ?? [], ['selected_breed_id' => $breedId]);
        $profile->state = 'selecting_gender';
        $profile->save();

        return $this->sendGenderSelection($chatId);
    }

    public function sendGenderSelection(string $chatId): array
    {
        $keyboard = [
            [
                ['text' => '♂️ Самец', 'callback_data' => 'gender_male'],
                ['text' => '♀️ Самка', 'callback_data' => 'gender_female']
            ],
            [
                ['text' => '⬅️ Назад к породам', 'callback_data' => 'back_to_breeds'],
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => '🔤 Выберите пол питомца:',
            'keyboard' => $keyboard
        ];
    }

    public function handleGenderSelection(string $chatId, TelegramProfile $profile, string $gender): array
    {
        $profile->data = array_merge($profile->data ?? [], ['selected_gender' => $gender]);
        $profile->state = 'confirming_pet';
        $profile->save();

        return $this->confirmPetCreation($chatId, $profile);
    }

    public function confirmPetCreation(string $chatId, TelegramProfile $profile): array
    {
        $petName = $profile->data['pet_name'] ?? 'Не указано';
        $breedId = $profile->data['selected_breed_id'] ?? null;
        $gender = $profile->data['selected_gender'] ?? 'Не указано';

        $breed = Breed::find($breedId);
        $breedName = $breed ? $breed->name : 'Не указано';
        $genderText = $gender === 'male' ? '♂️ Самец' : '♀️ Самка';

        $message = "🐾 <b>Подтвердите данные питомца:</b>\n\n";
        $message .= "📝 <b>Кличка:</b> {$petName}\n";
        $message .= "🐕 <b>Порода:</b> {$breedName}\n";
        $message .= "🔤 <b>Пол:</b> {$genderText}\n\n";
        $message .= "Все верно?";

        $keyboard = [
            [
                ['text' => '✅ Да, добавить', 'callback_data' => 'confirm_pet_yes'],
                ['text' => '❌ Нет, отменить', 'callback_data' => 'confirm_pet_no']
            ],
            [
                ['text' => '⬅️ Назад к породам', 'callback_data' => 'back_to_breeds'],
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => $keyboard
        ];
    }

    public function createPet(string $chatId, TelegramProfile $profile): array
    {
        try {
            $petName = $profile->data['pet_name'] ?? null;
            $breedId = $profile->data['selected_breed_id'] ?? null;
            $gender = $profile->data['selected_gender'] ?? null;

            if (!$petName || !$breedId || !$gender) {
                return [
                    'action' => 'send_message',
                    'message' => '❌ Не все данные заполнены для создания питомца.',
                    'keyboard' => [
                        [
                            ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                        ]
                    ]
                ];
            }

            Pet::create([
                'name' => $petName,
                'breed_id' => $breedId,
                'client_id' => $profile->user_id,
                'gender' => $gender,
                'birthdate' => null,
                'temperature' => null,
                'weight' => null,
            ]);

            // Очищаем данные питомца из профиля
            $profile->state = 'start';
            $profile->data = array_diff_key($profile->data ?? [], ['pet_name', 'selected_species_id', 'selected_breed_id', 'selected_gender']);
            $profile->save();

            return [
                'action' => 'send_message_and_main_menu',
                'message' => '✅ Питомец успешно добавлен!',
                'keyboard' => []
            ];
        } catch (\Throwable $e) {
            Log::error('PetManagementService: createPet error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка при создании питомца: ' . $e->getMessage(),
                'keyboard' => [
                    [
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }
    }

    public function cancelPetCreation(TelegramProfile $profile): void
    {
        $profile->state = 'start';
        $profile->data = array_diff_key($profile->data ?? [], ['pet_name', 'selected_species_id', 'selected_breed_id', 'selected_gender']);
        $profile->save();
    }

    public function goBackToSpecies(string $chatId, TelegramProfile $profile): array
    {
        $profile->state = 'selecting_species';
        $profile->data = array_diff_key($profile->data ?? [], ['selected_species_id', 'selected_breed_id', 'selected_gender']);
        $profile->save();

        return $this->sendSpecies($chatId);
    }

    public function goBackToBreeds(string $chatId, TelegramProfile $profile): array
    {
        $speciesId = $profile->data['selected_species_id'] ?? null;
        if ($speciesId) {
            $profile->state = 'selecting_breed';
            $profile->data = array_diff_key($profile->data ?? [], ['selected_breed_id', 'selected_gender']);
            $profile->save();

            return $this->sendBreeds($chatId, $speciesId);
        } else {
            return $this->goBackToSpecies($chatId, $profile);
        }
    }

    public function showUserPets(string $chatId, TelegramProfile $profile): array
    {
        if (!$profile->user_id) {
            return [
                'action' => 'send_message',
                'message' => '❌ Для просмотра питомцев необходимо завершить регистрацию.',
                'keyboard' => [
                    [
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $pets = Pet::with(['breed.species'])
            ->where('client_id', $profile->user_id)
            ->orderBy('name')
            ->get();

        if ($pets->isEmpty()) {
            return [
                'action' => 'send_message',
                'message' => '🐾 У вас пока нет питомцев. Добавьте первого питомца!',
                'keyboard' => [
                    [
                        ['text' => '🐾 Добавить питомца', 'callback_data' => 'add_pet'],
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $message = "🐕 <b>Ваши питомцы:</b>\n\n";
        
        foreach ($pets as $index => $pet) {
            $speciesName = $pet->breed->species->name ?? 'Не указано';
            $breedName = $pet->breed->name ?? 'Не указано';
            $genderText = $pet->gender === 'male' ? '♂️' : '♀️';
            
            $message .= ($index + 1) . ". <b>{$pet->name}</b> {$genderText}\n";
            $message .= "   🐕 Вид: {$speciesName}\n";
            $message .= "   🐕 Порода: {$breedName}\n";
            
            if ($pet->birthdate) {
                $message .= "   📅 Дата рождения: " . $pet->birthdate->format('d.m.Y') . "\n";
            }
            
            $message .= "\n";
        }

        $keyboard = [
            [
                ['text' => '🐾 Добавить питомца', 'callback_data' => 'add_pet'],
                ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => $keyboard
        ];
    }
}
