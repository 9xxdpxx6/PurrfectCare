<?php

namespace App\Services\Bot;

use App\Models\User;
use App\Models\TelegramProfile;
use App\Traits\NormalizesPhone;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    use NormalizesPhone;

    public function handleRegistrationFlow(TelegramProfile $profile, string $chatId, string $text): array
    {
        Log::info('UserRegistrationService: handling registration flow', [
            'chat_id' => $chatId,
            'current_state' => $profile->state,
            'text' => $text
        ]);

        switch ($profile->state) {
            case 'start':
                return $this->handleStartState($profile, $chatId);
            
            case 'await_name':
                return $this->handleNameInput($profile, $chatId, $text);
            
            case 'await_phone':
                return $this->handlePhoneInput($profile, $chatId, $text);
            
            case 'await_phone_existing':
                return $this->handleExistingPhoneInput($profile, $chatId, $text);
            
            case 'confirm_profile':
                return $this->handleProfileConfirmation($profile, $chatId, $text);
            
            default:
                Log::warning('UserRegistrationService: unknown state', ['state' => $profile->state]);
                return ['action' => 'error', 'message' => 'Неизвестное состояние регистрации'];
        }
    }

    protected function handleStartState(TelegramProfile $profile, string $chatId): array
    {
        // Если пользователь уже зарегистрирован, не начинаем регистрацию заново
        if ($profile->user_id) {
            Log::info('UserRegistrationService: user already registered, not starting registration');
            
            $keyboard = [
                [
                    ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                ]
            ];
            
            return [
                'action' => 'send_message',
                'message' => '❓ Команда не распознана. Используйте кнопки меню для навигации.',
                'keyboard' => $keyboard
            ];
        }

        $profile->state = 'await_name';
        $profile->save();
        
        Log::info('UserRegistrationService: state changed to await_name');
        
        return [
            'action' => 'send_message',
            'message' => 'Пожалуйста, укажите как Вас зовут.',
            'keyboard' => null
        ];
    }

    protected function handleNameInput(TelegramProfile $profile, string $chatId, string $text): array
    {
        $profile->data = ['name' => $text];
        $profile->state = 'await_phone';
        $profile->save();
        
        Log::info('UserRegistrationService: name received, state changed to await_phone');
        
        $message = "Спасибо! Теперь отправьте номер телефона.\n\n";
        $message .= "📱 Поддерживаемые форматы:\n";
        $message .= "• +7XXXXXXXXXX\n";
        $message .= "• 8XXXXXXXXXX\n";
        $message .= "• 7XXXXXXXXXX\n";
        $message .= "• 8 967-411 5225\n";
        $message .= "• +7 (963) 45-78 456\n";
        $message .= "• 8.967.411.52.25\n\n";
        $message .= "Система автоматически уберет все разделители.";
        
        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => null
        ];
    }

    protected function handlePhoneInput(TelegramProfile $profile, string $chatId, string $text): array
    {
        if (!$this->validatePhone($text)) {
            return [
                'action' => 'send_message',
                'message' => '❌ Неверный формат номера телефона. Пожалуйста, введите номер в формате: +7XXXXXXXXXX или 8XXXXXXXXXX',
                'keyboard' => null
            ];
        }
        
        $normalizedPhone = $this->normalizePhone($text);
        $data = $profile->data ?? [];
        $data['phone'] = $normalizedPhone;
        $profile->data = $data;
        $profile->state = 'completed';
        $profile->save();

        Log::info('UserRegistrationService: phone received, checking for existing user', [
            'original_phone' => $text,
            'normalized_phone' => $normalizedPhone
        ]);

        // Проверяем, есть ли уже пользователь с таким телефоном
        $existingUser = User::where('phone', $normalizedPhone)->first();
        
        if ($existingUser) {
            return $this->linkExistingUser($profile, $chatId, $existingUser);
        } else {
            return $this->createNewUser($profile, $chatId, $data);
        }
    }

    protected function handleExistingPhoneInput(TelegramProfile $profile, string $chatId, string $text): array
    {
        if (!$this->validatePhone($text)) {
            return [
                'action' => 'send_message',
                'message' => '❌ Неверный формат номера телефона. Пожалуйста, введите номер в формате: +7XXXXXXXXXX или 8XXXXXXXXXX',
                'keyboard' => null
            ];
        }
        
        $normalizedPhone = $this->normalizePhone($text);
        
        Log::info('UserRegistrationService: phone received for existing account', [
            'original_phone' => $text,
            'normalized_phone' => $normalizedPhone,
            'chat_id' => $chatId
        ]);
        
        $existingUser = User::where('phone', $normalizedPhone)->first();
        
        if ($existingUser) {
            return $this->prepareProfileConfirmation($profile, $chatId, $existingUser, $normalizedPhone);
        } else {
            return $this->handleUserNotFound($chatId, $normalizedPhone);
        }
    }

    protected function linkExistingUser(TelegramProfile $profile, string $chatId, User $existingUser): array
    {
        Log::info('UserRegistrationService: existing user found, linking Telegram', [
            'existing_user_id' => $existingUser->id,
            'existing_name' => $existingUser->name
        ]);
        
        $existingUser->telegram = (string)$chatId;
        $existingUser->save();
        
        $profile->user_id = $existingUser->id;
        $profile->save();
        
        return [
            'action' => 'send_message_and_branches',
            'message' => "✅ Отлично! Ваш аккаунт найден и привязан к Telegram. Добро пожаловать, {$existingUser->name}!",
            'keyboard' => null
        ];
    }

    protected function createNewUser(TelegramProfile $profile, string $chatId, array $data): array
    {
        Log::info('UserRegistrationService: creating new user');
        
        // Проверяем, есть ли уже пользователь с таким Telegram ID
        $existingUserByTelegram = User::where('telegram', (string)$chatId)->first();
        if ($existingUserByTelegram) {
            Log::info('UserRegistrationService: user already exists with this Telegram ID', [
                'existing_user_id' => $existingUserByTelegram->id,
                'existing_name' => $existingUserByTelegram->name
            ]);
            
            // Привязываем существующего пользователя к профилю
            $profile->user_id = $existingUserByTelegram->id;
            $profile->save();
            
            return [
                'action' => 'send_message_and_branches',
                'message' => "✅ Отлично! Ваш аккаунт найден и привязан к Telegram. Добро пожаловать, {$existingUserByTelegram->name}!",
                'keyboard' => null
            ];
        }
        
        $newUser = User::create([
            'name' => $data['name'] ?? 'Клиент',
            'phone' => $data['phone'],
            'email' => (string)$chatId.'@telegram.local',
            'telegram' => (string)$chatId,
            'password' => '\\',
        ]);
        
        Log::info('UserRegistrationService: new user created', ['user_id' => $newUser->id]);

        $profile->user_id = $newUser->id;
        $profile->save();

        return [
            'action' => 'send_message_and_branches',
            'message' => '✅ Регистрация завершена! Теперь можно записаться на приём.',
            'keyboard' => null
        ];
    }

    protected function prepareProfileConfirmation(TelegramProfile $profile, string $chatId, User $existingUser, string $normalizedPhone): array
    {
        $profile->data = array_merge($profile->data ?? [], [
            'found_user_id' => $existingUser->id,
            'found_user_name' => $existingUser->name,
            'found_user_phone' => $normalizedPhone
        ]);
        $profile->state = 'confirm_profile';
        $profile->save();
        
        return [
            'action' => 'show_profile_confirmation',
            'user' => $existingUser,
            'message' => null,
            'keyboard' => null
        ];
    }

    protected function handleUserNotFound(string $chatId, string $normalizedPhone): array
    {
        Log::warning('UserRegistrationService: user not found by phone', [
            'searched_phone' => $normalizedPhone,
            'all_users_with_phones' => User::pluck('phone')->toArray()
        ]);
        
        $keyboard = [
            [
                ['text' => '👤 Попробовать снова', 'callback_data' => 'existing_account'],
                ['text' => '🆕 Создать новый аккаунт', 'callback_data' => 'new_user']
            ]
        ];
        
        return [
            'action' => 'send_message',
            'message' => '❌ Пользователь с таким номером телефона не найден. Проверьте номер или создайте новый аккаунт.',
            'keyboard' => $keyboard
        ];
    }

    public function startRegistration(string $chatId): array
    {
        Log::info('UserRegistrationService: starting registration process', ['chat_id' => $chatId]);
        
        $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
        if ($profile) {
            $profile->state = 'await_name';
            $profile->save();
        }
        
        return [
            'action' => 'send_message',
            'message' => 'Пожалуйста, укажите как Вас зовут.',
            'keyboard' => null
        ];
    }

    public function handleExistingAccount(string $chatId, TelegramProfile $profile): array
    {
        Log::info('UserRegistrationService: handling existing account', ['chat_id' => $chatId]);
        
        $profile->state = 'await_phone_existing';
        $profile->save();
        
        $message = "Пожалуйста, введите номер телефона, который указан в вашем аккаунте.\n\n";
        $message .= "📱 Поддерживаемые форматы:\n";
        $message .= "• +7XXXXXXXXXX\n";
        $message .= "• 8XXXXXXXXXX\n";
        $message .= "• 7XXXXXXXXXX\n";
        $message .= "• 8 967-411 5225\n";
        $message .= "• +7 (963) 45-78 456\n";
        $message .= "• 8.967.411.52.25\n\n";
        $message .= "Система автоматически уберет все разделители и приведет к стандарту.";
        
        return [
            'action' => 'send_message',
            'message' => $message,
            'keyboard' => null
        ];
    }
}
