<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
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

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'Storefront';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image_path')->image()->disk('public')->directory('banners')->required(),
            TextInput::make('title')->maxLength(150),
            TextInput::make('link_url')->label('Link URL')->url()->helperText('Where the banner should take shoppers when clicked.'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->disk('public'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('link_url')->label('Link'),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
