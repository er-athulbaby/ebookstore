<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 9;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required(),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('phone'),
            TextInput::make('wallet_balance')
                ->label('Wallet balance (₹)')
                ->numeric()->default(0)
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100))
                ->helperText('Adjust manually for support cases — this is the customer\'s spendable balance at checkout.'),
            TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context) => $context === 'create')
                ->helperText('Leave blank to keep the current password.'),
            Toggle::make('is_admin')->label('Admin access (can log into this panel)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('wallet_balance')->label('Wallet')->formatStateUsing(fn ($state) => money($state)),
                IconColumn::make('is_admin')->boolean()->label('Admin'),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
