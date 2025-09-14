<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pet;

class ClientPetAddedNotification extends Notification
{
    use Queueable;

    protected $pet;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pet $pet)
    {
        $this->pet = $pet;
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
            'type' => 'pet_added',
            'pet_id' => $this->pet->id,
            'title' => 'Питомец добавлен',
            'message' => "Питомец {$this->pet->name} успешно добавлен в ваш профиль.",
            'data' => [
                'pet_id' => $this->pet->id,
                'pet_name' => $this->pet->name,
                'breed' => $this->pet->breed->name ?? 'Не указана',
                'species' => $this->pet->species->name ?? 'Не указан'
            ]
        ];
    }
}
