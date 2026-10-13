<?php

namespace App\Notifications;

use App\Models\GiftVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

// Deliberately NOT queued — see OrderConfirmed for why (no queue worker assumed).
class GiftVoucherIssued extends Notification
{
    use Queueable;

    /** $buyerCopy: the buyer's copy of a card that was sent to someone else. */
    public function __construct(public readonly GiftVoucher $voucher, public readonly bool $buyerCopy = false) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $voucher = $this->voucher;
        $store = config('redcherry.store_name');
        $from = $voucher->sender_name ?: $voucher->purchasedBy?->name;

        $message = new MailMessage;

        if ($this->buyerCopy) {
            $message->subject('Your '.money($voucher->value).' gift card was sent to '.($voucher->recipient_name ?: $voucher->recipient_email))
                ->greeting('Thank you!')
                ->line('We\'ve emailed your '.$store.' gift card to **'.($voucher->recipient_name ? $voucher->recipient_name.' ('.$voucher->recipient_email.')' : $voucher->recipient_email).'**. Here is a copy for your records.');
        } else {
            $message->subject(($from ? $from.' sent you' : 'You\'ve received').' a '.money($voucher->value).' '.$store.' gift card')
                ->greeting('Hi'.($voucher->recipient_name ? ' '.$voucher->recipient_name : '').'!')
                ->line(($from ? '**'.e($from).'** has sent you' : 'You\'ve received').' a gift card worth **'.money($voucher->value).'** to spend at '.$store.'.');
        }

        if ($voucher->design?->image_path) {
            $message->line(new HtmlString('<img src="'.e($voucher->design->imageUrl()).'" alt="'.e($voucher->design->name).'" style="width:100%;max-width:480px;border-radius:12px">'));
        }

        if ($voucher->message) {
            $message->line(new HtmlString('<em>&ldquo;'.e($voucher->message).'&rdquo;</em>'.($from ? ' — '.e($from) : '')));
        }

        $message->line('Gift card code:')
            ->line(new HtmlString('<span style="font-size:22px;font-weight:700;letter-spacing:2px">'.e($voucher->code).'</span>'))
            ->line('To use it, log in (or create an account) and enter the code on your Wallet page or in the gift card box at checkout. The amount is added to your wallet and can be spent across one or more orders.');

        if ($voucher->expires_at) {
            $message->line('Valid until '.$voucher->expires_at->format('d M Y').'.');
        }

        return $message->action('Redeem gift card', route('account.wallet'));
    }
}
