<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthorResource\Pages;
use BackedEnum;
use App\Models\Author;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Filled in automatically from the name; edit only if you need a different web address.'),
            FileUpload::make('photo_path')->image()->disk('public')->directory('authors'),
            Textarea::make('bio')->columnSpanFull(),
            Toggle::make('is_active')->label('Active (shown on the website)')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')->disk('public')->circular()->label('Photo'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('books_count')->counts('books')->label('Books'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Active')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
