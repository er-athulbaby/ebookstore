<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use App\Support\AdminSections;
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
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'users';

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 9;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    /** Only full admins may change admin accounts; otherwise a limited admin could reset a full admin's password. */
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return ! $record->is_admin || auth()->user()->isSuperAdmin() || $record->is(auth()->user());
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canEdit($record) && ! $record->is(auth()->user());
    }

    public static function applyFullAccess(array $data, array $formState): array
    {
        if (auth()->user()->isSuperAdmin() && ($formState['is_admin'] ?? false) && ($formState['full_access'] ?? false)) {
            $data['admin_permissions'] = null;
        }

        return $data;
    }

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
            Toggle::make('is_admin')->label('Admin access (can log into this panel)')->live()
                ->disabled(fn (?User $record) => ! auth()->user()->isSuperAdmin() || $record?->is(auth()->user())),
            Toggle::make('full_access')->label('Full access (all sections, can manage admins)')
                ->visible(fn (Get $get) => $get('is_admin'))
                ->disabled(fn (?User $record) => ! auth()->user()->isSuperAdmin() || $record?->is(auth()->user()))
                ->afterStateHydrated(fn (Toggle $component, ?User $record) => $component->state($record ? $record->admin_permissions === null : true))
                ->dehydrated(false)
                ->live(),
            CheckboxList::make('admin_permissions')->label('Sections this admin can open')
                ->options(AdminSections::ALL)
                ->columns(2)->columnSpanFull()
                ->visible(fn (Get $get) => $get('is_admin') && ! $get('full_access'))
                ->disabled(fn () => ! auth()->user()->isSuperAdmin())
                // Full access is stored as "no list"; a limited admin gets exactly the ticked sections.
                ->dehydrateStateUsing(fn ($state, Get $get) => $get('full_access') ? null : array_values($state ?? []))
                ->dehydrated(fn () => auth()->user()->isSuperAdmin()),
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
