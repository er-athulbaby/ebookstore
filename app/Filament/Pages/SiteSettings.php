<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\SiteSettings as Site;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class SiteSettings extends Page
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'site_settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site settings';

    protected static ?int $navigationSort = 10;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    /** Plain-text settings saved as-is (password and uploads are handled separately). */
    private const TEXT_KEYS = [
        'store_name', 'tagline', 'meta_description',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'mail_from_address', 'mail_from_name',
        'head_code', 'body_code',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $values = collect(self::TEXT_KEYS)->mapWithKeys(fn ($k) => [$k => Setting::get($k)])->all();
        $values['store_name'] ??= config('redcherry.store_name');
        $values['tagline'] ??= Site::tagline();
        $values['meta_description'] ??= Site::metaDescription();
        $values['smtp_encryption'] ??= 'ssl';
        $values['logo_path'] = Setting::get('logo_path');
        $values['favicon_path'] = Setting::get('favicon_path');

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Store')
                ->description('Shown in browser tabs, search results and emails.')
                ->columns(2)
                ->components([
                    TextInput::make('store_name')->required()->maxLength(80),
                    TextInput::make('tagline')->maxLength(120)->helperText('Used in the homepage title, e.g. "RedCherry Books — your tagline".'),
                    Textarea::make('meta_description')->label('Search description')->rows(2)->maxLength(160)
                        ->helperText('The short text Google shows under your site name. Up to 160 characters.')->columnSpanFull(),
                ]),

            Section::make('Logo & favicon')
                ->columns(2)
                ->components([
                    FileUpload::make('logo_path')->label('Logo')->image()->disk('public')->directory('branding')
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/jpeg'])
                        ->helperText('Header, footer and admin. SVG, or a wide PNG about 624 × 226 px with a transparent background. Leave empty to use the built-in logo.'),
                    FileUpload::make('favicon_path')->label('Favicon')->disk('public')->directory('branding')
                        ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'])
                        ->helperText('Browser tab icon. Square PNG 512 × 512 px (or .ico).'),
                ]),

            Section::make('Email (SMTP)')
                ->description('Order confirmations and gift cards are sent with these details. Leave Host empty to keep the server defaults.')
                ->columns(2)
                ->components([
                    TextInput::make('smtp_host')->label('SMTP host')->placeholder('smtp.hostinger.com'),
                    TextInput::make('smtp_port')->label('Port')->numeric()->placeholder('465'),
                    Select::make('smtp_encryption')->label('Encryption')
                        ->options(['ssl' => 'SSL (usually port 465)', 'tls' => 'TLS / STARTTLS (usually port 587)'])
                        ->default('ssl'),
                    TextInput::make('smtp_username')->label('Username')->placeholder('orders@redcherrybooks.in'),
                    TextInput::make('smtp_password')->label('Password')->password()->revealable()
                        ->placeholder(fn () => Setting::get('smtp_password') ? 'Saved — leave blank to keep' : '')
                        ->dehydrated(fn ($state) => filled($state)),
                    TextInput::make('mail_from_address')->label('From email')->email()->placeholder('orders@redcherrybooks.in'),
                    TextInput::make('mail_from_name')->label('From name')->placeholder('RedCherry Books'),
                ]),

            Section::make('Tracking code')
                ->description('Paste code from Google Analytics / Google Tag Manager / Meta Pixel. Added to every store page (not the admin).')
                ->components([
                    Textarea::make('head_code')->label('Inside <head>')->rows(5)->extraInputAttributes(['class' => 'font-mono']),
                    Textarea::make('body_code')->label('Start of <body> (e.g. Tag Manager noscript)')->rows(3)->extraInputAttributes(['class' => 'font-mono']),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Save')->submit('save')])]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Send test email')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(fn () => 'Uses the saved settings (save first if you changed them) and sends to '.Auth::user()->email.'.')
                ->action(function () {
                    try {
                        Mail::raw('This is a test email from '.config('redcherry.store_name').'. Your email settings work.', function ($m) {
                            $m->to(Auth::user()->email)->subject('Test email — '.config('redcherry.store_name'));
                        });
                        Notification::make()->success()->title('Test email sent to '.Auth::user()->email)
                            ->body(config('mail.default') === 'log' ? 'Note: mail is set to "log", so it was written to the log file, not sent. Fill in SMTP host to send real emails.' : null)
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()->danger()->title('Could not send')->body($e->getMessage())->persistent()->send();
                    }
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $values = collect(self::TEXT_KEYS)->mapWithKeys(fn ($k) => [$k => filled($state[$k] ?? null) ? trim((string) $state[$k]) : null])->all();
        $values['logo_path'] = $state['logo_path'] ?: null;
        $values['favicon_path'] = $state['favicon_path'] ?: null;
        if (filled($state['smtp_password'] ?? null)) {
            $values['smtp_password'] = Crypt::encryptString($state['smtp_password']); // never stored in plain text
        }

        Setting::put($values);

        Notification::make()->success()->title('Settings saved')->send();
    }
}
