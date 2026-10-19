<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Support\Sales;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopBooks extends TableWidget
{
    public static function canView(): bool
    {
        return (bool) auth()->user()?->canAccessSection('dashboard');
    }

    protected static ?int $sort = 3;

    protected static bool $isLazy = false; // small query; render with the page

    protected static ?string $heading = 'Best sellers — last 30 days';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->whereNotNull('book_variant_id')
                    ->whereIn('order_id', Sales::orders()->where('placed_at', '>=', now()->subDays(30))->select('id'))
                    ->selectRaw('MIN(id) as id, book_title, SUM(quantity) as copies, SUM(line_total) as revenue')
                    ->groupBy('book_title')
                    ->orderByDesc('copies')
                    ->limit(5)
            )
            ->paginated(false)
            ->defaultKeySort(false) // grouped query: no per-row id to sort on
            ->columns([
                TextColumn::make('book_title')->label('Book')->wrap(),
                TextColumn::make('copies')->label('Copies')->numeric()->alignEnd(),
                TextColumn::make('revenue')->label('Sales')->formatStateUsing(fn ($state) => money((int) $state))->alignEnd(),
            ])
            ->emptyStateHeading('No book sales in the last 30 days')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}
