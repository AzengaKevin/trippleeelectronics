<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnlineOrderReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->markdown('emails.online-order-received', [
            'order' => $this->order,
            'notifiable' => $notifiable,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Online Order Received',
            'message' => 'A new online order has been received and is ready for processing.',
            'action_url' => route('backoffice.pos', ['reference' => $this->order->reference]),
            'icon' => 'fas fa-shopping-cart',
            'type' => 'info',
            'created_at' => now(),
        ];
    }
}
