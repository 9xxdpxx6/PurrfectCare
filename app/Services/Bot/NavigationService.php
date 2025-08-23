<?php

namespace App\Services\Bot;

use App\Models\TelegramProfile;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NavigationService
{
    public function __construct(
        private TelegramApiService $apiService
    ) {
    }

    public function sendWelcome(string $chatId, bool $isRegistered): array
    {
        if ($isRegistered) {
            $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
            
            // Чистим временные данные питомца при входе в главное меню (/start)
            if ($profile) {
                $this->clearTransientPetData($profile);
            }
            
            $user = $profile && $profile->user_id ? User::find($profile->user_id) : null;
            $userName = $user ? $user->name : 'Клиент';
            
            $text = "С возвращением, {$userName}!";
            
            // Показываем одинаковое меню для всех зарегистрированных пользователей
            $keyboard = [
                [
                    ['text' => '📅 Записаться на приём', 'callback_data' => 'book_appointment']
                ],
                [
                    ['text' => '🐾 Добавить питомца', 'callback_data' => 'add_pet'],
                    ['text' => '🐕 Мои питомцы', 'callback_data' => 'my_pets']
                ],
                [
                    ['text' => '📋 Мои записи', 'callback_data' => 'my_appointments']
                ]
            ];
            return [
                'action' => 'send_message',
                'message' => $text,
                'keyboard' => $keyboard
            ];
        } else {
            $text = 'Здравствуйте! У вас уже есть аккаунт в нашей системе или вы новый пользователь?';
            
            $keyboard = [
                [
                    ['text' => '👤 У меня уже есть аккаунт', 'callback_data' => 'existing_account'],
                    ['text' => '🆕 Я новый пользователь', 'callback_data' => 'new_user']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => $text,
                'keyboard' => $keyboard
            ];
        }
    }

    public function goToMainMenu(string $chatId, TelegramProfile $profile): array
    {
        // Чистим временные данные питомца при возврате в главное меню
        $this->clearTransientPetData($profile);
        
        $profile->state = 'start';
        $profile->save();
        
        return $this->sendWelcome($chatId, true);
    }

    public function goBackToBranches(string $chatId, TelegramProfile $profile): array
    {
        $profile->state = 'start';
        $profile->save();
        
        return [
            'action' => 'send_branches',
            'message' => '',
            'keyboard' => []
        ];
    }

    public function goBackToVeterinarians(string $chatId, TelegramProfile $profile): array
    {
        $profile->state = 'selecting_veterinarian';
        $profile->save();
        
        $branchId = $profile->data['selected_branch_id'] ?? null;
        if ($branchId) {
            return [
                'action' => 'send_veterinarians',
                'message' => '',
                'keyboard' => [],
                'branch_id' => $branchId
            ];
        } else {
            return $this->goBackToBranches($chatId, $profile);
        }
    }

    public function goToBookAppointment(string $chatId, TelegramProfile $profile): array
    {
        if ($profile && isset($profile->data['selected_branch_id'])) {
            $profile->state = 'selecting_veterinarian';
            $profile->save();
            
            return [
                'action' => 'send_veterinarians',
                'message' => '',
                'keyboard' => [],
                'branch_id' => $profile->data['selected_branch_id']
            ];
        } else {
            return $this->goBackToBranches($chatId, $profile);
        }
    }

    public function showProfileConfirmation(string $chatId, User $existingUser): array
    {
        $message = "✅ <b>Подтверждение профиля:</b>\n\n";
        $message .= "👤 <b>Имя:</b> " . $existingUser->name . "\n";
        $message .= "📱 <b>Номер телефона:</b> " . $existingUser->phone . "\n";
        $message .= "🆔 <b>ID пользователя:</b> " . $existingUser->id . "\n\n";
        $message .= "Это ваш аккаунт? Если нет, нажмите кнопку 'Это не мой аккаунт'.";

        $keyboard = [
            [
                ['text' => '✅ Подтвердить', 'callback_data' => 'confirm_profile_yes'],
                ['text' => '❌ Это не мой аккаунт', 'callback_data' => 'confirm_profile_no']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => $keyboard
        ];
    }

    public function processProfileConfirmation(string $chatId, TelegramProfile $profile, string $data): array
    {
        if ($data === 'confirm_profile_yes') {
            // Получаем пользователя
            $foundUserId = $profile->data['found_user_id'] ?? null;
            if (!$foundUserId) {
                return [
                    'action' => 'send_message',
                    'message' => '❌ Ошибка: пользователь не найден. Начните авторизацию заново.',
                    'keyboard' => []
                ];
            }

            $user = \App\Models\User::find($foundUserId);
            if (!$user) {
                return [
                    'action' => 'send_message',
                    'message' => '❌ Ошибка: пользователь не найден. Начните авторизацию заново.',
                    'keyboard' => []
                ];
            }

            // Отправляем код подтверждения на email перед авторизацией
            return $this->sendVerificationCodeForProfile($profile, $chatId, $user);
        } elseif ($data === 'confirm_profile_no') {
            $profile->state = 'await_phone_existing';
            $profile->save();
            
            return [
                'action' => 'send_message',
                'message' => "✅ Хорошо, я вернусь к вводу номера телефона для вашего существующего аккаунта.\n\nПожалуйста, введите номер телефона, который указан в вашем аккаунте.",
                'keyboard' => []
            ];
        }

        return [
            'action' => 'error',
            'message' => 'Неизвестное действие подтверждения профиля',
            'keyboard' => []
        ];
    }

    protected function sendVerificationCodeForProfile(TelegramProfile $profile, string $chatId, \App\Models\User $user): array
    {
        // Генерируем 6-значный код
        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Сохраняем код в профиль
        $data = $profile->data ?? [];
        $profile->data = array_merge($data, [
            'verification_code' => $verificationCode,
            'verification_code_created_at' => now()->timestamp,
            'verifying_existing_user' => true
        ]);
        $profile->state = 'awaiting_verification_code';
        $profile->save();

        try {
            // Отправляем код на email существующего пользователя
            \Mail::to($user->email)->send(new \App\Mail\VerificationCode($verificationCode, $user->name));
            
            return [
                'action' => 'send_message',
                'message' => "📧 Код подтверждения отправлен на email: {$user->email}\n\nВведите 6-значный код из письма для привязки аккаунта к Telegram.\n\n⏰ Код действителен 10 минут.\n\n💡 Если код не пришел, проверьте папку 'Спам' в вашей почте.",
                'keyboard' => []
            ];
        } catch (\Exception $e) {
            \Log::error('NavigationService: failed to send verification email for profile confirmation', [
                'email' => $user->email,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка отправки email. Пожалуйста, попробуйте снова.',
                'keyboard' => []
            ];
        }
    }

    private function clearTransientPetData(TelegramProfile $profile): void
    {
        $data = $profile->data ?? [];
        foreach ([
            'pet_name',
            'selected_gender',
            'selected_pet_id',
            'selected_breed_id',
            'selected_species_id'
        ] as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }
        }
        $profile->data = $data;
        $profile->save();
    }
}
