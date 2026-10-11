<?php

namespace App\Filament\Resources\GiftVoucherResource\Pages;

use App\Filament\Resources\GiftVoucherResource;
use App\Models\GiftVoucher;
use App\Notifications\GiftVoucherIssued;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Notification;

class CreateGiftVoucher extends CreateRecord
{
    protected static string $resource = GiftVoucherResource::class;

    protected function afterCreate(): void
    {
        /** @var GiftVoucher $voucher */
        $voucher = $this->record;

        if ($voucher->recipient_email) {
            Notification::route('mail', $voucher->recipient_email)->notify(new GiftVoucherIssued($voucher));
        }
    }
}
