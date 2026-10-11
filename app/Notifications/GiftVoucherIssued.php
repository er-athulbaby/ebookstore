<?php

namespace App\Notifications;

use App\Models\GiftVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Deliberately NOT queued — see OrderConfirmed for why (no queue worker assumed).
class GiftVoucherIssued extends Notification
{
    use Queueable;

    public function __construct(public readonly GiftVoucher $voucher) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $voucher = $this->voucher;

        $message = (new MailMessage)
            ->subject('You\'ve received a '.config('redcherry.store_name').' gift voucher!')
            ->greeting('Hi'.($voucher->recipient_name ? ' '.$voucher->recipient_name : '').'!')
            ->line('You\'ve been sent a gift voucher worth **'.money($voucher->value).'** to spend at '.config('redcherry.store_name').'.');

        if ($voucher->message) {
            $message->line('"'.$voucher->message.'"');
        }

        $message->line('Your voucher code:')
            ->line('**'.$voucher->code.'**')
            ->line('Enter this code at checkout to redeem it.');

        if ($voucher->expires_at) {
            $message->line('This voucher expires on '.$voucher->expires_at->format('d M Y').'.');
        }

        return $message->action('Start Shopping', route('home'));
    }
}
