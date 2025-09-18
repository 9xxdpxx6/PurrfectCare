<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Visit;

class WebsiteBookingNotification extends Notification
{
    use Queueable;

    protected $visit;

    /**
     * Create a new notification instance.
     */
    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
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
            'type' => 'website_booking',
            'title' => 'Новая запись через сайт',
            'message' => "Клиент {$this->visit->client->name} записался к ветеринару {$this->visit->schedule->veterinarian->name} на " . $this->visit->starts_at->format('H:i'),
            'data' => [
                'visit_id' => $this->visit->id,
                'client_name' => $this->visit->client->name,
                'veterinarian_name' => $this->visit->schedule->veterinarian->name,
                'appointment_time' => $this->visit->starts_at->format('H:i'),
                'appointment_date' => $this->visit->starts_at->format('d.m.Y'),
                'branch_name' => $this->visit->schedule->branch->name ?? 'Не указан',
                'pet_name' => $this->visit->pet->name ?? 'Без питомца'
            ]
        ];
    }
}
