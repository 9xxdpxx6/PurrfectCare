<?php

namespace App\Services\Bot;

use App\Models\User;
use App\Models\TelegramProfile;
use App\Services\Visit\VisitTimeCalculationService;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    public function __construct(
        private VisitTimeCalculationService $timeService,
        private UserRegistrationService $registrationService,
        private AppointmentService $appointmentService,
        private TelegramApiService $apiService,
        private PetService $petService,
        private NavigationService $navigationService,
        private UserAppointmentsService $userAppointmentsService
    ) {
    }

    public function handleUpdate(array $update): void
    {
        try {
            Log::info('TelegramBotService: processing update', ['update' => $update]);
            
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallback($update['callback_query']);
            } else {
                Log::warning('TelegramBotService: unknown update type', ['update' => $update]);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        Log::info('TelegramBotService: handling message', [
            'chat_id' => $chatId,
            'text' => $text,
            'message' => $message
        ]);

        $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
        
        if (!$profile) {
            $profile = TelegramProfile::create([
                'telegram_id' => (string)$chatId,
                'state' => 'start'
            ]);
        }

        // Обработка команд
        if (str_starts_with($text, '/')) {
            if ($text === '/start') {
                Log::info('TelegramBotService: processing /start command');
                $this->processStartCommand($chatId, $profile);
                return;
            }
            
            if ($text === '/login') {
                Log::info('TelegramBotService: processing /login command');
                $this->handleLoginCommand($chatId, $profile);
                return;
            }
            
            if ($text === '/logout') {
                Log::info('TelegramBotService: processing /logout command');
                $this->handleLogoutCommand($chatId, $profile);
                return;
            }
        }

        // Обработка состояний для добавления питомца
        if ($profile->state === 'adding_pet_name') {
            $result = $this->petService->handlePetName($chatId, $profile, $text);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        // Обработка регистрации только для незарегистрированных пользователей
        if (in_array($profile->state, ['start', 'await_name', 'await_phone', 'await_email', 'awaiting_verification_code', 'await_phone_existing'])) {
            $this->handleRegistrationFlow($profile, $chatId, $text);
            return;
        }

        // Если состояние не распознано, показываем сообщение об ошибке
        $this->apiService->sendMessage($chatId, '❓ Неизвестная команда. Используйте /start для начала работы.');
    }

    protected function handleCallback(array $callback): void
    {
        $chatId = $callback['message']['chat']['id'] ?? null;
        $data = $callback['data'] ?? '';
        $callbackId = $callback['id'] ?? null;
        $messageId = $callback['message']['message_id'] ?? null;
        
        if (!$chatId) {
            return;
        }

        Log::info('TelegramBotService: handling callback', [
            'chat_id' => $chatId,
            'callback_data' => $data,
            'callback_id' => $callbackId,
            'message_id' => $messageId
        ]);

        // Сразу отвечаем на callback для быстрого отклика
        if ($callbackId) {
            $this->apiService->answerCallback($callbackId);
        }

        // СРАЗУ удаляем сообщение с кнопками для блокировки повторных нажатий
        if ($messageId) {
            $this->apiService->deleteMessage($chatId, $messageId);
        }

        $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
        if (!$profile) {
            Log::warning('TelegramBotService: profile not found for callback', ['chat_id' => $chatId]);
            return;
        }

        // Обрабатываем callback данные
        $this->processCallbackData($profile, $chatId, $data);
    }

    protected function processCallbackData(TelegramProfile $profile, string $chatId, string $data): void
    {
        if (str_starts_with($data, 'branch:')) {
            $branchId = (int)substr($data, 7);
            
            // Переходим к выбору ветеринара
            $this->sendVeterinarians($chatId, $branchId);
            return;
        }

        if (str_starts_with($data, 'vets_page:')) {
            [$prefix, $branchId, $page] = explode(':', $data);
            
            // Показываем страницу ветеринаров
            $result = $this->appointmentService->sendVeterinarians($chatId, (int)$branchId, (int)$page);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (str_starts_with($data, 'vet:')) {
            [$prefix, $employeeId, $branchId] = explode(':', $data);
            
            // Меняем состояние на выбор даты
            $profile->state = 'selecting_date';
            $profile->save();
            
            $this->sendDates($chatId, (int)$employeeId, (int)$branchId);
            return;
        }

        if (str_starts_with($data, 'date:')) {
            [$prefix, $employeeId, $branchId, $date] = explode(':', $data);
            // Меняем состояние на выбор времени
            $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
            if ($profile) {
                $profile->state = 'selecting_time';
                $profile->save();
            }
            
            $this->sendTimeSlots($chatId, (int)$employeeId, (int)$branchId, $date);
            return;
        }

        if (str_starts_with($data, 'time:')) {
            [$prefix, $scheduleId, $datetime] = explode(':', $data, 3);
            $profile = TelegramProfile::where('telegram_id', (string)$chatId)->first();
            $result = $this->appointmentService->tryBookSlot($chatId, (int)$scheduleId, $datetime, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        // Обработка навигационных кнопок
        if ($data === 'back_to_branches') {
            $result = $this->navigationService->goBackToBranches($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'back_to_veterinarians') {
            $result = $this->navigationService->goBackToVeterinarians($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'main_menu') {
            $result = $this->navigationService->goToMainMenu($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'book_appointment') {
            $result = $this->navigationService->goToBookAppointment($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        // Обработка добавления питомца
        if ($data === 'add_pet') {
            $result = $this->petService->startAddingPet($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (str_starts_with($data, 'breed:')) {
            $breedId = (int)substr($data, 6);
            $result = $this->petService->handleBreedSelection($chatId, $profile, $breedId);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (str_starts_with($data, 'species:')) {
            $speciesId = (int)substr($data, 8);
            $result = $this->petService->handleSpeciesSelection($chatId, $profile, $speciesId);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'back_to_species') {
            $result = $this->petService->goBackToSpecies($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'back_to_breeds') {
            $result = $this->petService->goBackToBreeds($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'my_pets') {
            $result = $this->petService->showUserPets($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'my_appointments') {
            $result = $this->userAppointmentsService->showUserAppointments($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (str_starts_with($data, 'pet_actions:')) {
            $petId = (int)substr($data, 12);
            $result = $this->petService->showPetActions($chatId, $profile, $petId);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (str_starts_with($data, 'book_for_pet:')) {
            $petId = (int)substr($data, 13);
            // Сохраняем выбранного питомца в профиль
            $profile->data = array_merge($profile->data ?? [], ['selected_pet_id' => $petId]);
            $profile->save();
            // Переходим к выбору филиала
            $this->sendBranches($chatId);
            return;
        }

        if (str_starts_with($data, 'delete_pet:')) {
            $petId = (int)substr($data, 11);
            $result = $this->petService->deletePet($chatId, $profile, $petId);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if (in_array($data, ['gender_male', 'gender_female'])) {
            $gender = $data === 'gender_male' ? 'male' : 'female';
            $result = $this->petService->handleGenderSelection($chatId, $profile, $gender);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'confirm_pet_yes') {
            $result = $this->petService->createPet($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'confirm_pet_no') {
            $this->petService->cancelPetCreation($profile);
            $result = $this->navigationService->goToMainMenu($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        // Обработка email-верификации
        if ($data === 'confirm_existing_email_user') {
            $result = $this->registrationService->confirmExistingEmailUser($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'confirm_existing_phone_user') {
            $result = $this->registrationService->confirmExistingPhoneUser($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'use_different_email') {
            $result = $this->registrationService->useDifferentEmail($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'use_different_phone') {
            $result = $this->registrationService->useDifferentPhone($chatId, $profile);
            $this->executeAction($result, $chatId, $profile);
            return;
        }

        if ($data === 'existing_account') {
            $this->handleExistingAccount($chatId, $profile);
            return;
        }
        
        if ($data === 'new_user') {
            $this->startRegistration($chatId);
            return;
        }

        // Обработка подтверждения профиля
        if (in_array($data, ['confirm_profile_yes', 'confirm_profile_no'])) {
            $this->processProfileConfirmation($chatId, $profile, $data);
            return;
        }

        // Игнорируем noop callback (кнопка с номером страницы)
        if ($data === 'noop') {
            return;
        }

        Log::warning('TelegramBotService: unknown callback data', ['data' => $data]);
    }

    protected function handleRegistrationFlow(TelegramProfile $profile, string $chatId, string $text): void
    {
        $result = $this->registrationService->handleRegistrationFlow($profile, $chatId, $text);
        $this->executeAction($result, $chatId, $profile);
    }

    protected function sendWelcome(string $chatId, bool $isRegistered): void
    {
        $result = $this->navigationService->sendWelcome($chatId, $isRegistered);
        $this->executeAction($result, $chatId);
    }
    
    protected function startRegistration(string $chatId): void
    {
        $result = $this->registrationService->startRegistration($chatId);
        $this->executeAction($result, $chatId);
    }
    
    protected function handleExistingAccount(string $chatId, TelegramProfile $profile): void
    {
        $result = $this->registrationService->handleExistingAccount($chatId, $profile);
        $this->executeAction($result, $chatId, $profile);
    }

    protected function sendBranches(string $chatId): void
    {
        $result = $this->appointmentService->sendBranches($chatId);
        $this->executeAction($result, $chatId);
    }

    protected function sendVeterinarians(string $chatId, int $branchId, int $page = 1): void
    {
        $result = $this->appointmentService->sendVeterinarians($chatId, $branchId, $page);
        $this->executeAction($result, $chatId);
    }

    protected function sendDates(string $chatId, int $employeeId, int $branchId): void
    {
        $result = $this->appointmentService->sendDates($chatId, $employeeId, $branchId);
        $this->executeAction($result, $chatId);
    }

    protected function sendTimeSlots(string $chatId, int $employeeId, int $branchId, string $dateYmd): void
    {
        $result = $this->appointmentService->sendTimeSlots($chatId, $employeeId, $branchId, $dateYmd);
        $this->executeAction($result, $chatId);
    }

    protected function tryBookSlot(string $chatId, int $scheduleId, string $startsAt, TelegramProfile $profile): void
    {
        $result = $this->appointmentService->tryBookSlot($chatId, $scheduleId, $startsAt, $profile);
        $this->executeAction($result, $chatId, $profile);
    }

    protected function showProfileConfirmation(string $chatId, User $existingUser): void
    {
        $result = $this->navigationService->showProfileConfirmation($chatId, $existingUser);
        $this->executeAction($result, $chatId);
    }

    protected function processProfileConfirmation(string $chatId, TelegramProfile $profile, string $data): void
    {
        $result = $this->navigationService->processProfileConfirmation($chatId, $profile, $data);
        $this->executeAction($result, $chatId, $profile);
    }

    protected function executeAction(array $result, string $chatId, ?TelegramProfile $profile = null): void
    {
        try {
            switch ($result['action']) {
                case 'send_message':
                    $this->apiService->sendMessage($chatId, $result['message'], $result['keyboard'] ?? []);
                    break;
                    
                case 'send_message_and_branches':
                    $this->apiService->sendMessage($chatId, $result['message'], $result['keyboard'] ?? []);
                    $this->sendBranches($chatId);
                    break;
                    
                case 'send_message_and_main_menu':
                    $this->apiService->sendMessage($chatId, $result['message'], $result['keyboard'] ?? []);
                    if ($profile) {
                        $this->navigationService->goToMainMenu($chatId, $profile);
                    }
                    break;
                    
                case 'send_branches':
                    $this->sendBranches($chatId);
                    break;
                    
                case 'send_veterinarians':
                    $branchId = $result['branch_id'] ?? null;
                    $page = $result['page'] ?? 1;
                    if ($branchId) {
                        $this->sendVeterinarians($chatId, $branchId, $page);
                    }
                    break;
                    
                case 'show_profile_confirmation':
                    $this->showProfileConfirmation($chatId, $result['user']);
                    break;
                    
                case 'send_multiple_messages':
                    foreach ($result['messages'] as $msg) {
                        $this->apiService->sendMessage($chatId, $msg['message'], $msg['keyboard'] ?? []);
                    }
                    break;
                    
                case 'error':
                    Log::error('TelegramBotService: action error', ['result' => $result]);
                    $this->apiService->sendMessage($chatId, $result['message'] ?? 'Произошла ошибка');
                    break;
                    
                default:
                    Log::warning('TelegramBotService: unknown action', ['action' => $result['action']]);
            }
        } catch (\Throwable $e) {
            Log::error('TelegramBotService: executeAction error', [
                'action' => $result['action'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Отправляем сообщение об ошибке пользователю
            $this->apiService->sendMessage($chatId, 'Произошла ошибка при обработке запроса. Попробуйте еще раз.');
        }
    }

    protected function processStartCommand(string $chatId, TelegramProfile $profile): void
    {
        if ($profile->user_id) {
            // Пользователь уже зарегистрирован
            $this->sendWelcome($chatId, true);
        } else {
            // Пользователь не зарегистрирован
            $this->sendWelcome($chatId, false);
        }
    }

    protected function handleLoginCommand(string $chatId, TelegramProfile $profile): void
    {
        // Отвязываем текущего пользователя и начинаем регистрацию заново
        if ($profile->user_id) {
            Log::info('TelegramBotService: user logging out from current account', [
                'chat_id' => $chatId,
                'current_user_id' => $profile->user_id
            ]);
            
            // Отвязываем Telegram ID от пользователя в таблице users
            $user = \App\Models\User::find($profile->user_id);
            if ($user) {
                $user->telegram = null;
                $user->save();
                Log::info('TelegramBotService: telegram ID unlinked from user', [
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            }
            
            // Полностью удаляем старый профиль и создаем новый
            $profile->delete();
            
            // Создаем новый чистый профиль
            $newProfile = new TelegramProfile();
            $newProfile->telegram_id = (string)$chatId;
            $newProfile->state = 'start';
            $newProfile->data = [];
            $newProfile->save();
            
            $this->apiService->sendMessage($chatId, '👋 Вы вышли из аккаунта. Теперь можете войти в другой аккаунт или зарегистрироваться.');
            $this->sendWelcome($chatId, false);
        } else {
            // Пользователь не зарегистрирован, начинаем регистрацию
            $this->startRegistration($chatId);
        }
    }

    protected function handleLogoutCommand(string $chatId, TelegramProfile $profile): void
    {
        if ($profile->user_id) {
            Log::info('TelegramBotService: user logging out', [
                'chat_id' => $chatId,
                'user_id' => $profile->user_id
            ]);
            
            // Отвязываем Telegram ID от пользователя в таблице users
            $user = \App\Models\User::find($profile->user_id);
            if ($user) {
                $user->telegram = null;
                $user->save();
                Log::info('TelegramBotService: telegram ID unlinked from user', [
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            }
            
            // Полностью удаляем старый профиль и создаем новый
            $profile->delete();
            
            // Создаем новый чистый профиль
            $newProfile = new TelegramProfile();
            $newProfile->telegram_id = (string)$chatId;
            $newProfile->state = 'start';
            $newProfile->data = [];
            $newProfile->save();
            
            $this->apiService->sendMessage($chatId, '👋 Вы вышли из аккаунта. Для входа используйте /start или /login.');
        } else {
            $this->apiService->sendMessage($chatId, '❌ Вы не вошли в аккаунт. Для входа используйте /start или /login.');
        }
    }
}


