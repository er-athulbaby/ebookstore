<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BookResource extends Resource
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'books';

    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Book details')
                ->columns(2)
                ->components([
                    TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Filled in automatically from the name; edit only if you need a different web address.'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required()
                        ->searchable(),
                    Select::make('authors')
                        ->relationship('authors', 'name')
                        ->multiple()
                        ->required()
                        ->searchable(),
                    TextInput::make('isbn'),
                    TextInput::make('edition'),
                    TextInput::make('pages')->numeric(),
                    \Filament\Forms\Components\Select::make('language')
                        ->options(fn () => collect(['Malayalam', 'English'])
                            ->merge(Book::query()->distinct()->pluck('language'))
                            ->filter()->unique()->sort()->mapWithKeys(fn ($l) => [$l => $l])->all())
                        ->searchable()
                        ->default('Malayalam')
                        ->required()
                        ->createOptionForm([TextInput::make('language')->label('New language')->required()])
                        ->createOptionUsing(fn (array $data) => trim($data['language'])),
                    TextInput::make('publisher')->default('RedCherry Books')->required(),
                    \Filament\Forms\Components\DatePicker::make('published_at'),
                ]),
            Section::make('Description')
                ->components([
                    Textarea::make('description')->rows(5)->columnSpanFull(),
                ]),
            Section::make('Merchandising')
                ->columns(4)
                ->components([
                    Toggle::make('is_featured')->label('Show in homepage hero')
                        ->helperText('Up to 5 ticked books appear in the hero. None ticked = automatic pick.'),
                    Toggle::make('is_bestseller'),
                    Toggle::make('is_top100'),
                    Select::make('status')
                        ->options(['draft' => 'Draft', 'published' => 'Published'])
                        ->default('draft')
                        ->required(),
                ]),
            Section::make('Cover images')
                ->components([
                    Repeater::make('images')
                        ->relationship()
                        ->components([
                            FileUpload::make('path')->image()->disk('public')->directory('books')->required()
                                ->helperText('Front cover, portrait 2:3 — 600 × 900 px (or larger, e.g. 1000 × 1500). JPG or WebP, under 500 KB.'),
                            TextInput::make('alt_text'),
                        ])
                        ->orderColumn('sort_order')
                        ->columns(2)
                        ->defaultItems(0)
                        ->addActionLabel('Add image'),
                ]),
            Section::make('Formats & pricing')
                ->description('Add a variant per format you sell this title in — paperback, hardcover, and/or an eBook.')
                ->components([
                    Repeater::make('variants')
                        ->relationship()
                        ->components([
                            Select::make('type')
                                ->options(['paperback' => 'Paperback', 'hardcover' => 'Hardcover', 'ebook' => 'eBook'])
                                ->required()
                                ->live(),
                            // sku is DB-unique (book_variants migration) but not validated
                            // here — Filament's unique() rule inside a relationship
                            // Repeater needs extra wiring not worth it for single-admin
                            // catalog entry. A collision surfaces as a save error naming
                            // the SKU, not silent data loss.
                            TextInput::make('sku')->required()->helperText('Must be unique across all variants, e.g. book-slug-pb.'),
                            TextInput::make('price')
                                ->label('Price (₹)')
                                ->numeric()->required()
                                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                                ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100)),
                            TextInput::make('compare_at_price')
                                ->label('MRP / compare-at price (₹)')
                                ->numeric()
                                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                                ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== '' ? (int) round(((float) $state) * 100) : null),
                            TextInput::make('stock_quantity')
                                ->numeric()
                                ->visible(fn (callable $get) => $get('type') !== 'ebook')
                                ->helperText('Leave blank for unlimited stock.'),
                            Select::make('ebook_format')
                                ->options(['pdf' => 'PDF', 'epub' => 'EPUB'])
                                ->visible(fn (callable $get) => $get('type') === 'ebook'),
                            FileUpload::make('ebook_file_path')
                                ->label('eBook file')
                                ->disk('ebooks')
                                ->directory('')
                                // Not ->visibility('private') — that makes Filament call
                                // Storage::temporaryUrl(), which the local driver doesn't
                                // implement and would throw. previewable(false) skips URL
                                // generation entirely; the disk itself (config/filesystems.php)
                                // already keeps this outside public/ and unservable.
                                ->previewable(false)
                                ->acceptedFileTypes(['application/pdf', 'application/epub+zip'])
                                ->visible(fn (callable $get) => $get('type') === 'ebook')
                                ->helperText('Stored privately — never web-accessible directly. Only served via signed, purchase-gated download links.'),
                            Toggle::make('is_active')->default(true),
                        ])
                        ->columns(2)
                        ->defaultItems(1)
                        ->addActionLabel('Add format'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')->label('Cover')->disk('public')->height(56)
                    ->state(fn (Book $record) => $record->images->first()?->path), // 'images.0.path' rendered nothing
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('authors.name')->badge()->label('Author(s)'),
                TextColumn::make('category.name')->label('Category'),
                IconColumn::make('is_featured')->boolean()->label('Hero'),
                IconColumn::make('is_bestseller')->boolean()->label('Bestseller'),
                IconColumn::make('is_top100')->boolean()->label('Top 100'),
                TextColumn::make('status')->badge()->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('category_id')->relationship('category', 'name')->label('Category'),
                TernaryFilter::make('is_featured')->label('In homepage hero'),
                TernaryFilter::make('is_bestseller'),
                TernaryFilter::make('is_top100'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
