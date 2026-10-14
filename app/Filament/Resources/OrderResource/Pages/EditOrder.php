<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /** COD: "Cash received" (or marking it Delivered) records the payment, so status and payment never disagree. */
    protected function afterSave(): void
    {
        $order = $this->record;
        if ($order->payment_method !== 'cod' || $order->payment_status === 'paid') {
            return;
        }

        $cashIn = ($this->data['cash_received'] ?? false) || $order->status === 'delivered';
        if ($cashIn) {
            $order->update(['payment_status' => 'paid']);
            $order->payments()->where('provider', 'cod')->update(['status' => 'captured']);
        }
    }
}
