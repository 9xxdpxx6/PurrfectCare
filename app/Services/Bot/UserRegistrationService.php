<?php

namespace App\Services\Bot;

use App\Models\User;
use App\Models\TelegramProfile;
use App\Traits\NormalizesPhone;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class UserRegistrationService
{
    use NormalizesPhone;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handleRegistrationFlow(TelegramProfile $profile, string $chatId, string $text): array
    {
        Log::info('UserRegistrationService: handling registration flow', [
            'chat_id' => $chatId,
            'current_state' => $profile->state,
            'text' => $text,
            'profile_data' => $profile->data
        ]);

        switch ($profile->state) {
            case 'start':
                return $this->handleStartState($profile, $chatId);
            
            case 'await_name':
                return $this->handleNameInput($profile, $chatId, $text);
            
            case 'await_phone':
                return $this->handlePhoneInput($profile, $chatId, $text);
            
            case 'await_email':
                return $this->handleEmailInput($profile, $chatId, $text);
            
            case 'awaiting_verification_code':
                return $this->handleVerificationCode($profile, $chatId, $text);
            
            case 'await_phone_existing':
                return $this->handleExistingPhoneInput($profile, $chatId, $text);
            
            case 'confirm_profile':
                return $this->handleProfileConfirmation($profile, $chatId, $text);
            
            default:
                Log::warning('UserRegistrationService: unknown state', [
                    'state' => $profile->state,
                    'text' => $text,
                    'profile_data' => $profile->data
                ]);
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
        $profile->state = 'await_email';
        $profile->save();

        Log::info('UserRegistrationService: phone received, now asking for email', [
            'original_phone' => $text,
            'normalized_phone' => $normalizedPhone
        ]);
        
        return [
            'action' => 'send_message',
            'message' => "Спасибо! Теперь отправьте ваш email адрес для подтверждения номера телефона.\n\n📧 Email будет использоваться для:\n• Подтверждения номера телефона\n• Отправки важных уведомлений\n• Восстановления доступа к аккаунту",
            'keyboard' => null
        ];
    }

    protected function handleEmailInput(TelegramProfile $profile, string $chatId, string $text): array
    {
        // Простая валидация email
        if (!filter_var($text, FILTER_VALIDATE_EMAIL)) {
            return [
                'action' => 'send_message',
                'message' => '❌ Неверный формат email адреса. Пожалуйста, введите корректный email.',
                'keyboard' => null
            ];
        }

        $email = strtolower(trim($text));
        $data = $profile->data ?? [];
        $data['email'] = $email;
        $profile->data = $data;
        $profile->state = 'verifying_email';
        $profile->save();

        Log::info('UserRegistrationService: email received, starting verification', [
            'email' => $email,
            'phone' => $data['phone'] ?? null
        ]);

        // Проверяем, есть ли уже пользователь с таким email
        $existingUserByEmail = User::where('email', $email)->first();
        if ($existingUserByEmail) {
            return $this->handleExistingEmailUser($profile, $chatId, $existingUserByEmail, $data);
        }

        // Проверяем, есть ли уже пользователь с таким телефоном
        $existingUserByPhone = User::where('phone', $data['phone'])->first();
        if ($existingUserByPhone) {
            return $this->handleExistingPhoneUser($profile, $chatId, $existingUserByPhone, $data);
        }

        // Новый пользователь - отправляем код подтверждения
        return $this->sendVerificationCode($profile, $chatId, $data);
    }

    protected function handleExistingEmailUser(TelegramProfile $profile, string $chatId, User $existingUser, array $data): array
    {
        $profile->data = array_merge($data, [
            'found_user_id' => $existingUser->id,
            'found_user_name' => $existingUser->name,
            'found_user_email' => $existingUser->email
        ]);
        $profile->state = 'confirm_existing_email_user';
        $profile->save();

        $keyboard = [
            [
                ['text' => '✅ Да, это мой аккаунт', 'callback_data' => 'confirm_existing_email_user'],
                ['text' => '❌ Нет, другой email', 'callback_data' => 'use_different_email']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => "🔍 Найден существующий аккаунт:\n\n👤 Имя: {$existingUser->name}\n📧 Email: {$existingUser->email}\n📱 Телефон: {$existingUser->phone}\n\nЭто ваш аккаунт? Если да, то на ваш email будет отправлен код подтверждения.",
            'keyboard' => $keyboard
        ];
    }

    protected function handleExistingPhoneUser(TelegramProfile $profile, string $chatId, User $existingUser, array $data): array
    {
        $profile->data = array_merge($data, [
            'found_user_id' => $existingUser->id,
            'found_user_name' => $existingUser->name,
            'found_user_phone' => $existingUser->phone
        ]);
        $profile->state = 'confirm_existing_phone_user';
        $profile->save();

        $keyboard = [
            [
                ['text' => '✅ Да, это мой аккаунт', 'callback_data' => 'confirm_existing_phone_user'],
                ['text' => '❌ Нет, другой номер', 'callback_data' => 'use_different_phone']
            ]
        ];

        return [
            'action' => 'send_message',
            'message' => "🔍 Найден существующий аккаунт:\n\n👤 Имя: {$existingUser->name}\n📧 Email: {$existingUser->email}\n📱 Телефон: {$existingUser->phone}\n\nЭто ваш аккаунт?",
            'keyboard' => $keyboard
        ];
    }

    protected function sendVerificationCodeForExistingUser(TelegramProfile $profile, string $chatId, array $data, User $existingUser): array
    {
        // Генерируем 6-значный код
        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Сохраняем код в профиль
        $profile->data = array_merge($data, [
            'verification_code' => $verificationCode,
            'verification_code_created_at' => now()->timestamp,
            'verifying_existing_user' => true
        ]);
        $profile->state = 'awaiting_verification_code';
        $profile->save();

        Log::info('UserRegistrationService: verification code generated for existing user', [
            'email' => $existingUser->email,
            'user_id' => $existingUser->id,
            'code' => $verificationCode
        ]);

        try {
            // Логируем конфигурацию почты (без пароля)
            Log::info('UserRegistrationService: attempting to send email', [
                'email' => $existingUser->email,
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name')
            ]);
            
            // Отправляем код на email существующего пользователя
            \Mail::to($existingUser->email)->send(new \App\Mail\VerificationCode($verificationCode, $existingUser->name));
            
            Log::info('UserRegistrationService: email sent successfully', [
                'email' => $existingUser->email,
                'verification_code' => $verificationCode
            ]);
            
            return [
                'action' => 'send_message',
                'message' => "📧 Код подтверждения отправлен на email: {$existingUser->email}\n\nВведите 6-значный код из письма для привязки аккаунта к Telegram.\n\n⏰ Код действителен 10 минут.\n\n💡 Если код не пришел, проверьте папку 'Спам' в вашей почте.",
                'keyboard' => null
            ];
        } catch (\Exception $e) {
            Log::error('UserRegistrationService: failed to send verification email for existing user', [
                'email' => $existingUser->email,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'mail_config' => [
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption')
                ]
            ]);
            
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка отправки email. Пожалуйста, проверьте email адрес и попробуйте снова.',
                'keyboard' => null
            ];
        }
    }

    protected function sendVerificationCode(TelegramProfile $profile, string $chatId, array $data): array
    {
        // Генерируем 6-значный код
        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Сохраняем код в профиль
        $profile->data = array_merge($data, [
            'verification_code' => $verificationCode,
            'verification_code_created_at' => now()->timestamp
        ]);
        $profile->state = 'awaiting_verification_code';
        $profile->save();

        Log::info('UserRegistrationService: verification code generated', [
            'email' => $data['email'],
            'code' => $verificationCode
        ]);

        try {
            // Логируем конфигурацию почты (без пароля)
            Log::info('UserRegistrationService: attempting to send email', [
                'email' => $data['email'],
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name')
            ]);
            
            // Отправляем код на email
            \Mail::to($data['email'])->send(new \App\Mail\VerificationCode($verificationCode, $data['name'] ?? 'Клиент'));
            
            Log::info('UserRegistrationService: email sent successfully', [
                'email' => $data['email'],
                'verification_code' => $verificationCode
            ]);
            
            return [
                'action' => 'send_message',
                'message' => "📧 Код подтверждения отправлен на email: {$data['email']}\n\nВведите 6-значный код из письма для завершения регистрации.\n\n⏰ Код действителен 10 минут.\n\n💡 Если код не пришел, проверьте папку 'Спам' в вашей почте.",
                'keyboard' => null
            ];
        } catch (\Exception $e) {
            Log::error('UserRegistrationService: failed to send verification email', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'mail_config' => [
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption')
                ]
            ]);
            
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка отправки email. Пожалуйста, проверьте email адрес и попробуйте снова.',
                'keyboard' => null
            ];
        }
    }

    protected function handleVerificationCode(TelegramProfile $profile, string $chatId, string $text): array
    {
        $data = $profile->data ?? [];
        $storedCode = $data['verification_code'] ?? null;
        $codeCreatedAt = $data['verification_code_created_at'] ?? null;
        
        if (!$storedCode || !$codeCreatedAt) {
            return [
                'action' => 'send_message',
                'message' => '❌ Код подтверждения не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        // Проверяем срок действия кода (10 минут)
        if (now()->timestamp - $codeCreatedAt > 600) {
            return [
                'action' => 'send_message',
                'message' => '❌ Код подтверждения истек. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        // Проверяем код
        if ($text !== $storedCode) {
            return [
                'action' => 'send_message',
                'message' => '❌ Неверный код подтверждения. Проверьте код и попробуйте снова.',
                'keyboard' => null
            ];
        }

        // Проверяем, верифицируем ли существующего пользователя или создаем нового
        if (isset($data['verifying_existing_user']) && $data['verifying_existing_user']) {
            Log::info('UserRegistrationService: verification code confirmed for existing user, linking account');
            return $this->linkExistingUserAfterVerification($profile, $chatId, $data);
        } else {
            // Код верный - создаем пользователя
            Log::info('UserRegistrationService: verification code confirmed, creating user');
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

    protected function linkExistingUserAfterVerification(TelegramProfile $profile, string $chatId, array $data): array
    {
        $foundUserId = $data['found_user_id'] ?? null;
        if (!$foundUserId) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        $user = User::find($foundUserId);
        if (!$user) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        // Привязываем пользователя к профилю
        $profile->user_id = $user->id;
        $profile->state = 'start';
        $profile->data = [];
        $profile->save();

        // Обновляем telegram ID у пользователя
        $user->telegram = (string)$chatId;
        $user->save();

        Log::info('UserRegistrationService: existing user verified and linked after code confirmation', [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);

        return [
            'action' => 'send_message_and_branches',
            'message' => "✅ Отлично! Ваш аккаунт подтвержден и привязан к Telegram. Добро пожаловать, {$user->name}!",
            'keyboard' => null
        ];
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
            'email' => $data['email'] ?? (string)$chatId.'@telegram.local',
            'telegram' => (string)$chatId,
            'password' => '\\',
        ]);
        
        Log::info('UserRegistrationService: new user created', ['user_id' => $newUser->id]);

        $profile->user_id = $newUser->id;
        $profile->save();

        // Отправляем уведомление администраторам о новой регистрации через бота
        try {
            $this->notificationService->notifyAboutBotRegistration($newUser);
        } catch (\Exception $e) {
            Log::error('Failed to send notification about bot registration', [
                'user_id' => $newUser->id,
                'error' => $e->getMessage()
            ]);
        }

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

    public function confirmExistingEmailUser(string $chatId, TelegramProfile $profile): array
    {
        $data = $profile->data ?? [];
        $foundUserId = $data['found_user_id'] ?? null;
        
        if (!$foundUserId) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        $user = User::find($foundUserId);
        if (!$user) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        // Отправляем код подтверждения на email существующего пользователя
        return $this->sendVerificationCodeForExistingUser($profile, $chatId, $data, $user);
    }

    public function confirmExistingPhoneUser(string $chatId, TelegramProfile $profile): array
    {
        $data = $profile->data ?? [];
        $foundUserId = $data['found_user_id'] ?? null;
        
        if (!$foundUserId) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        $user = User::find($foundUserId);
        if (!$user) {
            return [
                'action' => 'send_message',
                'message' => '❌ Ошибка: пользователь не найден. Начните регистрацию заново.',
                'keyboard' => null
            ];
        }

        // Привязываем пользователя к профилю
        $profile->user_id = $user->id;
        $profile->state = 'start';
        $profile->data = [];
        $profile->save();

        // Обновляем telegram ID у пользователя
        $user->telegram = (string)$chatId;
        $user->save();

        Log::info('UserRegistrationService: existing phone user confirmed and linked', [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);

        return [
            'action' => 'send_message_and_branches',
            'message' => "✅ Отлично! Ваш аккаунт найден и привязан к Telegram. Добро пожаловать, {$user->name}!",
            'keyboard' => null
        ];
    }

    public function useDifferentEmail(string $chatId, TelegramProfile $profile): array
    {
        // Сбрасываем email и возвращаемся к вводу email
        $data = $profile->data ?? [];
        unset($data['email']);
        $profile->data = $data;
        $profile->state = 'await_email';
        $profile->save();

        return [
            'action' => 'send_message',
            'message' => "Хорошо! Отправьте другой email адрес для подтверждения номера телефона.\n\n📧 Email будет использоваться для:\n• Подтверждения номера телефона\n• Отправки важных уведомлений\n• Восстановления доступа к аккаунту",
            'keyboard' => null
        ];
    }

    public function useDifferentPhone(string $chatId, TelegramProfile $profile): array
    {
        // Сбрасываем телефон и возвращаемся к вводу телефона
        $data = $profile->data ?? [];
        unset($data['phone']);
        $profile->data = $data;
        $profile->state = 'await_phone';
        $profile->save();

        return [
            'action' => 'send_message',
            'message' => "Хорошо! Отправьте другой номер телефона.\n\n📱 Поддерживаемые форматы:\n• +7XXXXXXXXXX\n• 8XXXXXXXXXX\n• 7XXXXXXXXXX\n• 8 967-411 5225\n• +7 (963) 45-78 456\n• 8.967.411.52.25\n\nСистема автоматически уберет все разделители.",
            'keyboard' => null
        ];
    }
}
