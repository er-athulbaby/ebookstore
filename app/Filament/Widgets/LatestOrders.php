<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    public static function canView(): bool
    {
        return (bool) auth()->user()?->canAccessSection('dashboard');
    }

    protected static ?int $sort = 4;

    protected static bool $isLazy = false; // small query; render with the page

    protected static ?string $heading = 'Latest orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with('user')->latest('placed_at')->limit(8))
            ->paginated(false)
            ->recordUrl(fn (Order $record) => route('filament.admin.resources.orders.edit', $record))
            ->columns([
                TextColumn::make('order_number')->label('Order'),
                TextColumn::make('user.name')->label('Customer'),
                TextColumn::make('status')->badge()->color(fn (Order $record) => $record->statusColor()),
                TextColumn::make('total')->formatStateUsing(fn ($state) => money($state))->alignEnd(),
                TextColumn::make('placed_at')->label('Placed')->since(),
            ])
            ->emptyStateHeading('No orders yet');
    }
}
