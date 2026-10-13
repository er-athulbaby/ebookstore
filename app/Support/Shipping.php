<?php

namespace App\Support;

use App\Models\Setting;

/** Shipping rates set in admin (Settings → Shipping), falling back to config/.env. All amounts in paise. */
class Shipping
{
    public static function fee(): int
    {
        return (int) Setting::get('shipping_fee', config('redcherry.flat_shipping_fee', 60) * 100);
    }

    /** 0 = free delivery is switched off. */
    public static function freeAbove(): int
    {
        return (int) Setting::get('free_delivery_above', config('redcherry.free_delivery_threshold', 799) * 100);
    }

    public static function for(int $netAmount, bool $hasPhysical): int
    {
        if (! $hasPhysical) {
            return 0;
        }

        return self::freeAbove() > 0 && $netAmount >= self::freeAbove() ? 0 : self::fee();
    }
}
