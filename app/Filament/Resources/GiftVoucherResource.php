<?php

namespace App\Filament\Resources;

use App\Models\GiftVoucher;
use App\Filament\Resources\GiftVoucherResource\Pages;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GiftVoucherResource extends Resource
{
    protected static ?string $model = GiftVoucher::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')
                ->default(fn () => GiftVoucher::generateCode())
                ->disabled()
                ->dehydrated()
                ->required()
                ->unique(ignoreRecord: true),
            TextInput::make('value')
                ->label('Value (₹)')
                ->numeric()->required()
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
            TextInput::make('recipient_name')->label('Recipient name'),
            TextInput::make('recipient_email')->label('Recipient email')->email()
                ->helperText('If set, the voucher code is emailed to this address on save.'),
            Textarea::make('message')->label('Gift message')->columnSpanFull(),
            DateTimePicker::make('expires_at'),
            Toggle::make('is_redeemed')->label('Redeemed')->helperText('Set manually to void a voucher.'),
            Placeholder::make('redeemed_by')
                ->label('Redeemed by')
                ->content(fn (?GiftVoucher $record) => $record?->redeemedBy?->name ?? '—')
                ->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->copyable(),
                TextColumn::make('value')->label('Value')->formatStateUsing(fn ($state) => money($state)),
                TextColumn::make('recipient_email')->label('Recipient'),
                IconColumn::make('is_redeemed')->boolean()->label('Redeemed'),
                TextColumn::make('redeemedBy.name')->label('Redeemed by')->placeholder('—'),
                TextColumn::make('expires_at')->dateTime('d M Y')->placeholder('Never'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGiftVouchers::route('/'),
            'create' => Pages\CreateGiftVoucher::route('/create'),
            'edit' => Pages\EditGiftVoucher::route('/{record}/edit'),
        ];
    }
}
