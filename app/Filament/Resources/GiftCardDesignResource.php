<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftCardDesignResource\Pages;
use App\Models\GiftCardDesign;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/** Gift cards customers can buy on /gift-cards (e.g. "Happy Onam ₹500"). */
class GiftCardDesignResource extends Resource
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'gift_cards';

    protected static ?string $model = GiftCardDesign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Gift Card Designs';

    protected static ?int $navigationSort = 7;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(100)->placeholder('Happy Birthday'),
            TextInput::make('value')->label('Value (₹)')->numeric()->minValue(1)->required()->prefix('₹')
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
            FileUpload::make('image_path')->label('Card image')->image()->disk('public')->directory('gift-cards')
                ->helperText('Landscape 16:10 — 800 × 500 px. Also shown in the gift card email.')->columnSpanFull(),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('On sale')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->disk('public')->label('Image'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('value')->formatStateUsing(fn ($state) => money($state))->sortable(),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_active')->label('On sale'),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGiftCardDesigns::route('/'),
            'create' => Pages\CreateGiftCardDesign::route('/create'),
            'edit' => Pages\EditGiftCardDesign::route('/{record}/edit'),
        ];
    }
}
