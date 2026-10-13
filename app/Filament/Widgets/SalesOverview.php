<?php

namespace App\Filament\Widgets;

use App\Models\BookVariant;
use App\Support\Sales;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false; // one request per dashboard load, not one per widget

    protected function getStats(): array
    {
        $now = now();
        $today = Sales::amount($now->copy()->startOfDay(), $now);
        $month = Sales::amount($now->copy()->startOfMonth(), $now);
        // Same number of days into last month, so the comparison is fair mid-month.
        $lastMonthSoFar = Sales::amount($now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow());
        $ordersThisMonth = Sales::orders()->where('placed_at', '>=', $now->copy()->startOfMonth())->count();
        $daily = array_values(Sales::daily(14));

        $toShip = Sales::orders()->whereIn('status', ['paid', 'processing'])->whereIn('fulfillment_type', ['physical', 'mixed'])->count();
        $lowStock = BookVariant::where('is_active', true)->whereIn('type', ['paperback', 'hardcover'])
            ->whereNotNull('stock_quantity')->where('stock_quantity', '<=', 5)->count();

        $change = $lastMonthSoFar > 0 ? round(($month - $lastMonthSoFar) / $lastMonthSoFar * 100) : null;

        return [
            Stat::make('Sales today', money($today))
                ->description('Last 14 days')->chart($daily)->color('primary'),
            Stat::make('Sales this month', money($month))
                ->description($change === null ? 'No sales by this day last month' : abs($change).'% '.($change >= 0 ? 'up' : 'down').' on last month')
                ->descriptionIcon($change === null ? null : ($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'))
                ->color($change === null ? 'gray' : ($change >= 0 ? 'success' : 'danger')),
            Stat::make('Orders this month', number_format($ordersThisMonth))
                ->description('Average '.money($ordersThisMonth ? intdiv($month, $ordersThisMonth) : 0).' per order'),
            Stat::make('Waiting to ship', number_format($toShip))
                ->description($toShip ? 'Paid or COD orders with printed books' : 'All caught up')
                ->color($toShip ? 'warning' : 'success')
                ->url(route('filament.admin.resources.orders.index')),
            Stat::make('Low stock', number_format($lowStock))
                ->description('Printed books with 5 or fewer left')
                ->color($lowStock ? 'danger' : 'success')
                ->url(route('filament.admin.resources.books.index')),
        ];
    }
}
