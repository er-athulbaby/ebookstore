<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BundleResource\Pages;
use App\Models\Bundle;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BundleResource extends Resource
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'bundles';

    protected static ?string $model = Bundle::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('variants')
                ->relationship(name: 'variants', titleAttribute: 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->book->title.' — '.$record->typeLabel())
                ->options(fn () => \App\Models\BookVariant::query()
                    ->where('type', '!=', 'ebook')
                    ->with('book')
                    ->get()
                    ->mapWithKeys(fn ($v) => [$v->id => $v->book->title.' — '.$v->typeLabel()]))
                ->multiple()
                ->required()
                ->helperText('Physical formats only — bundles don\'t support eBook items.')
                ->columnSpanFull(),
            TextInput::make('price')
                ->label('Bundle price (₹)')
                ->numeric()->required()
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
            FileUpload::make('image_path')->image()->disk('public')->directory('bundles')
                ->helperText('Optional — portrait 2:3, 600 × 900 px. Falls back to the first book\'s cover if left blank.'),
            Textarea::make('description')->columnSpanFull(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->disk('public'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('variants_count')->counts('variants')->label('Books'),
                TextColumn::make('price')->label('Price')->formatStateUsing(fn ($state) => money($state)),
                ToggleColumn::make('is_active'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBundles::route('/'),
            'create' => Pages\CreateBundle::route('/create'),
            'edit' => Pages\EditBundle::route('/{record}/edit'),
        ];
    }
}
