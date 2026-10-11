<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    // Orders are created by checkout, not by hand — admin only views/updates status.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order')
                ->columns(3)
                ->components([
                    Placeholder::make('order_number')->content(fn (Order $record) => $record->order_number),
                    Placeholder::make('customer')->content(fn (Order $record) => $record->user?->name.' ('.$record->contact_email.')'),
                    Placeholder::make('total')->content(fn (Order $record) => money($record->total)),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing',
                            'shipped' => 'Shipped', 'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled', 'refunded' => 'Refunded',
                        ])
                        ->required(),
                    Placeholder::make('payment_status')->content(fn (Order $record) => ucfirst($record->payment_status)),
                    Placeholder::make('payment_method')->content(fn (Order $record) => strtoupper($record->payment_method)),
                ]),
            Section::make('Items')
                ->components([
                    Repeater::make('items')
                        ->relationship()
                        ->components([
                            Placeholder::make('book_title')->content(fn ($record) => $record?->book_title),
                            Placeholder::make('variant_type')->content(fn ($record) => $record?->variant_type),
                            Placeholder::make('quantity')->content(fn ($record) => $record?->quantity),
                            Placeholder::make('line_total')->content(fn ($record) => $record ? money($record->line_total) : null),
                        ])
                        ->columns(4)
                        ->deletable(false)
                        ->addable(false)
                        ->reorderable(false),
                ]),
            Section::make('Notes')
                ->components([
                    Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('status')->badge()->color(fn (Order $record) => $record->statusColor()),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('payment_method')->badge(),
                TextColumn::make('total')->formatStateUsing(fn ($state) => money($state))->sortable(),
                TextColumn::make('placed_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing',
                    'shipped' => 'Shipped', 'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled', 'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_method')->options(['razorpay' => 'Razorpay', 'cod' => 'COD']),
            ])
            ->defaultSort('placed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
