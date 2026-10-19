<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'orders';

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

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
                        ->options(fn (Order $record) => $record->allowedStatuses())
                        ->in(fn (Order $record) => array_keys($record->allowedStatuses()))
                        ->helperText(fn (Order $record) => match (true) {
                            $record->payment_status === 'paid' => null,
                            $record->payment_method === 'cod' => 'Setting "Delivered" also marks the cash as received.',
                            default => 'Not paid online yet, so it can only stay pending or be cancelled.',
                        })
                        ->live()
                        ->required(),
                    // COD: admin records the cash; online payments are confirmed by Razorpay, so read-only.
                    Toggle::make('cash_received')->label('Cash received')
                        ->visible(fn (Order $record) => $record->payment_method === 'cod')
                        ->afterStateHydrated(fn (Toggle $component, Order $record) => $component->state($record->payment_status === 'paid'))
                        ->dehydrated(false),
                    Placeholder::make('payment_status')
                        ->visible(fn (Order $record) => $record->payment_method !== 'cod')
                        ->content(fn (Order $record) => $record->paymentStatusLabel()),
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
                TextColumn::make('status')->badge()->color(fn (Order $record) => $record->statusColor())
                    ->formatStateUsing(fn (Order $record) => $record->statusLabel()),
                TextColumn::make('payment_status')->label('Payment')->badge()
                    ->formatStateUsing(fn (Order $record) => $record->paymentStatusLabel())
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', default => 'gray',
                    }),
                TextColumn::make('payment_method')->badge()->color('gray'),
                TextColumn::make('total')->formatStateUsing(fn ($state) => money($state))->sortable(),
                TextColumn::make('placed_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing',
                    'shipped' => 'Shipped', 'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled', 'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_method')->options(['razorpay' => 'Razorpay', 'cod' => 'COD', 'free' => 'No payment needed']),
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
