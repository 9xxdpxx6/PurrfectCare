<?php

namespace App\Services\Bot;

use App\Models\Visit;
use App\Models\Schedule;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\TelegramProfile;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserAppointmentsService
{
    public function __construct(
        private TelegramApiService $apiService
    ) {
    }

    public function showUserAppointments(string $chatId, TelegramProfile $profile): array
    {
        if (!$profile->user_id) {
            return [
                'action' => 'send_message',
                'message' => '❌ Для просмотра записей необходимо завершить регистрацию.',
                'keyboard' => [
                    [
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        // Получаем только предстоящие записи (начиная с текущего времени)
        $upcomingVisits = Visit::with(['schedule.veterinarian', 'schedule.branch', 'pet'])
            ->where('client_id', $profile->user_id)
            ->where('starts_at', '>=', Carbon::now())
            ->orderBy('starts_at')
            ->get();

        if ($upcomingVisits->isEmpty()) {
            return [
                'action' => 'send_message',
                'message' => '📋 У вас нет предстоящих записей. Запишитесь на приём!',
                'keyboard' => [
                    [
                        ['text' => '📅 Записаться на приём', 'callback_data' => 'book_appointment'],
                        ['text' => '🏠 Главное меню', 'callback_data' => 'main_menu']
                    ]
                ]
            ];
        }

        $message = "📋 <b>Ваши предстоящие записи:</b>\n\n";
        
        foreach ($upcomingVisits as $index => $visit) {
            $visitDate = Carbon::parse($visit->starts_at);
            $endTime = Carbon::parse($visit->starts_at)->addMinutes(30);
            
            // Проверяем, есть ли питомец
            $petName = $visit->pet ? $visit->pet->name : 'Без питомца';
            $message .= ($index + 1) . ". <b>{$petName}</b>\n";
            $message .= "   👨‍⚕️ Врач: " . ($visit->schedule->veterinarian->name ?? 'Не указан') . "\n";
            $message .= "   🏥 Филиал: " . ($visit->schedule->branch->name ?? 'Не указан') . "\n";
            $message .= "   📅 Дата: " . $visitDate->format('d.m.Y, l') . "\n";
            $message .= "   🕐 Время: " . $visitDate->format('H:i') . " - " . $endTime->format('H:i') . "\n";
            
            if ($visit->complaints) {
                $message .= "   📝 Жалобы: {$visit->complaints}\n";
            }
            
            $message .= "\n";
        }

        $keyboard = [
            [
                ['text' => '📅 Записаться на приём', 'callback_data' => 'book_appointment'],
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
