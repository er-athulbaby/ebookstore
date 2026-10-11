<?php

use Illuminate\Support\Facades\Config;

if (! function_exists('money')) {
    /**
     * Format an integer paise amount as a display price, e.g. 43200 -> "₹432".
     * Amounts are always stored/passed as integer paise to avoid float rounding
     * errors in cart/order totals; formatting to rupees happens only at display time.
     */
    function money(int $paise): string
    {
        $symbol = Config::get('redcherry.currency_symbol', '₹');
        $rupees = $paise / 100;

        $formatted = floor($rupees) == $rupees
            ? number_format($rupees, 0)
            : number_format($rupees, 2);

        return $symbol.$formatted;
    }
}
