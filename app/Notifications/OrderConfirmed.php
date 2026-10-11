<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Deliberately NOT queued (no ShouldQueue): sent synchronously so it works
// out of the box on shared hosting where a queue worker/cron may not be
// running. If you later add a worker, add ShouldQueue back for snappier checkout.
class OrderConfirmed extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $message = (new MailMessage)
            ->subject('Order confirmed — '.$order->order_number)
            ->greeting('Thanks for your order, '.$notifiable->name.'!')
            ->line('Your RedCherry Books order **'.$order->order_number.'** is confirmed.')
            ->line('Order total: '.money($order->total));

        if ($order->fulfillment_type !== 'physical') {
            $message->line('Any eBooks in this order are ready to download from My Account → Downloads right now.');
        }

        if ($order->fulfillment_type !== 'digital') {
            $message->line('We\'ll email you again as soon as your physical items ship.');
        }

        return $message->action('View order', route('account.orders.show', $order->order_number))
            ->line('Thank you for shopping with RedCherry Books.');
    }
}
