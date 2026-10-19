<?php

namespace App\Filament\Pages;

use App\Models\Coupon;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

/** Timed offer popup on the storefront (rendered by resources/views/partials/offer-popup.blade.php). */
class OfferPopup extends Page
{
    use \App\Filament\Concerns\RequiresAdminSection;

    protected static string $adminSection = 'offer_popup';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Offer Popup';

    protected static ?string $title = 'Offer popup';

    protected static ?int $navigationSort = 9;

    protected static string|UnitEnum|null $navigationGroup = 'Storefront';

    private const KEYS = [
        'popup_enabled', 'popup_delay', 'popup_title', 'popup_message', 'popup_coupon',
        'popup_image', 'popup_button_text', 'popup_button_url', 'popup_repeat_days',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $values = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => Setting::get($k)])->all();
        $values['popup_enabled'] = (bool) $values['popup_enabled'];
        $values['popup_delay'] ??= 30;
        $values['popup_repeat_days'] ??= 1;
        $values['popup_button_text'] ??= 'Shop now';

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('When')
                ->columns(3)
                ->components([
                    Toggle::make('popup_enabled')->label('Show the popup')->inline(false),
                    TextInput::make('popup_delay')->label('Show after (seconds of browsing)')->numeric()->minValue(3)->maxValue(600)->required()
                        ->helperText('Counted across pages. Never shown on cart or checkout.'),
                    TextInput::make('popup_repeat_days')->label('Show again after (days)')->numeric()->minValue(0)->maxValue(365)->required()
                        ->helperText('0 = once per visit. Saving a changed offer shows it to everyone again.'),
                ]),
            Section::make('Offer')
                ->columns(2)
                ->components([
                    TextInput::make('popup_title')->label('Title')->required()->maxLength(80)->placeholder('Get 10% off your first order'),
                    Select::make('popup_coupon')->label('Coupon code to show (optional)')
                        ->options(fn () => Coupon::where('is_active', true)->orderBy('code')->pluck('code', 'code'))
                        ->searchable()
                        ->helperText('Shown with a Copy button. Manage codes under Sales → Coupons.'),
                    Textarea::make('popup_message')->label('Message')->rows(3)->maxLength(300)->columnSpanFull()
                        ->placeholder('Use the code below at checkout. Valid on all printed books.'),
                    FileUpload::make('popup_image')->label('Image (optional)')->image()->disk('public')->directory('popup')
                        ->helperText('Landscape 16:9 — 800 × 450 px. JPG or WebP.')->columnSpanFull(),
                    TextInput::make('popup_button_text')->label('Button text')->maxLength(30),
                    TextInput::make('popup_button_url')->label('Button link')->placeholder('/search or https://…')->maxLength(255)
                        ->helperText('Leave empty to just close the popup.'),
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

    public function save(): void
    {
        $state = $this->form->getState();
        $values = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => filled($state[$k] ?? null) ? $state[$k] : null])->all();
        $values['popup_enabled'] = ! empty($state['popup_enabled']) ? '1' : '0';
        $values['popup_version'] = (string) time(); // visitors who closed the old offer will see the new one

        Setting::put($values);

        Notification::make()->success()->title('Offer popup saved')->send();
    }
}
