<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class ClientOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'title' => 'Заказ создан',
            'message' => "Ваш заказ #{$this->order->id} на сумму {$this->order->total_amount} ₽ успешно создан.",
            'data' => [
                'order_id' => $this->order->id,
                'total_amount' => $this->order->total_amount,
                'status' => $this->order->status->name,
                'branch' => $this->order->branch->name,
                'items_count' => $this->order->items->count()
            ]
        ];
    }
}
