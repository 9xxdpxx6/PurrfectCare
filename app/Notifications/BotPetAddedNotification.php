<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pet;

class BotPetAddedNotification extends Notification
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
            'type' => 'bot_pet_added',
            'title' => 'Новый питомец добавлен через бота',
            'message' => "Клиент {$this->pet->client->name} добавил питомца {$this->pet->name} (" . ($this->pet->breed->species->name ?? 'Не указан вид') . ")",
            'data' => [
                'pet_id' => $this->pet->id,
                'pet_name' => $this->pet->name,
                'client_name' => $this->pet->client->name,
                'species_name' => $this->pet->breed->species->name ?? 'Не указан вид',
                'breed_name' => $this->pet->breed->name ?? 'Не указана порода',
                'gender' => $this->pet->gender,
                'added_time' => now()->format('H:i'),
                'added_date' => now()->format('d.m.Y')
            ]
        ];
    }
}
