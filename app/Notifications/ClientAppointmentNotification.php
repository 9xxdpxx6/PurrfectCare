<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Visit;

class ClientAppointmentNotification extends Notification
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
            'type' => 'appointment_created',
            'visit_id' => $this->visit->id,
            'title' => 'Запись на прием создана',
            'message' => "Ваша запись на {$this->visit->starts_at->format('d.m.Y H:i')} к ветеринару {$this->visit->schedule->veterinarian->name} подтверждена.",
            'data' => [
                'visit_id' => $this->visit->id,
                'date' => $this->visit->starts_at->format('d.m.Y'),
                'time' => $this->visit->starts_at->format('H:i'),
                'veterinarian' => $this->visit->schedule->veterinarian->name,
                'branch' => $this->visit->schedule->branch->name,
                'pet' => $this->visit->pet ? $this->visit->pet->name : 'Не указан'
            ]
        ];
    }
}
