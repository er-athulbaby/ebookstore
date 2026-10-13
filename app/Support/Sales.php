<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * One definition of "a sale" for the admin dashboard.
 * Counted: every placed order except unpaid card checkouts (pending), cancelled and refunded.
 * Amount: what the customer paid by card/UPI/cash (total minus wallet). Wallet spending is left out
 * because that money was already counted when the gift card was sold.
 */
class Sales
{
    public const AMOUNT = 'total - voucher_amount';

    public static function orders(): Builder
    {
        return Order::query()->whereNotIn('status', ['pending', 'cancelled', 'refunded']);
    }

    /** Paise taken between two moments. */
    public static function amount(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return (int) static::orders()->whereBetween('placed_at', [$from, $to])->sum(\DB::raw(self::AMOUNT));
    }

    /** Paise per day for the last $days days (oldest first), keyed Y-m-d. */
    public static function daily(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $rows = static::orders()->where('placed_at', '>=', $start)
            ->selectRaw('DATE(placed_at) as day, SUM('.self::AMOUNT.') as amount')
            ->groupBy('day')->pluck('amount', 'day');

        return collect(range(0, $days - 1))
            ->mapWithKeys(fn ($i) => [$d = $start->copy()->addDays($i)->toDateString() => (int) ($rows[$d] ?? 0)])
            ->all();
    }
}
