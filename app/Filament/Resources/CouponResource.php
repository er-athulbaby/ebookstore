<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')->required()->unique(ignoreRecord: true)->alphaDash()
                ->formatStateUsing(fn ($state) => $state ? strtoupper($state) : null),
            Select::make('type')->options(['percent' => 'Percent off', 'fixed' => 'Fixed amount off'])->required()->live(),
            TextInput::make('value')
                ->label(fn (callable $get) => $get('type') === 'fixed' ? 'Amount off (₹)' : 'Percent off (%)')
                ->numeric()->required()
                ->formatStateUsing(fn ($state, ?Coupon $record) => $state && $record?->type === 'fixed' ? $state / 100 : $state)
                ->dehydrateStateUsing(fn ($state, callable $get) => $get('type') === 'fixed' ? (int) round(((float) $state) * 100) : (int) $state),
            TextInput::make('min_order_amount')
                ->label('Minimum order (₹)')
                ->numeric()->default(0)
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
            TextInput::make('max_uses')->numeric()->helperText('Leave blank for unlimited.'),
            DateTimePicker::make('expires_at'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('value')->label('Value')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->type === 'fixed' ? money($state) : $state.'%'),
                TextColumn::make('min_order_amount')->label('Min order')->formatStateUsing(fn ($state) => money($state)),
                TextColumn::make('used_count')->label('Used'),
                TextColumn::make('max_uses')->label('Max uses')->placeholder('∞'),
                TextColumn::make('expires_at')->dateTime('d M Y'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
