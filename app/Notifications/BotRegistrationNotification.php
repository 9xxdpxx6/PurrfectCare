<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class BotRegistrationNotification extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bot_registration',
            'title' => 'Новая регистрация через бота',
            'message' => "Новый клиент {$this->user->name} зарегистрировался через Telegram бота",
            'data' => [
                'user_id' => $this->user->id,
                'user_name' => $this->user->name,
                'user_phone' => $this->user->phone,
                'user_email' => $this->user->email,
                'telegram_id' => $this->user->telegram,
                'registration_time' => now()->format('H:i'),
                'registration_date' => now()->format('d.m.Y')
            ]
        ];
    }
}
