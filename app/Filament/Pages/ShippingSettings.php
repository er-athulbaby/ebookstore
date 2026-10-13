<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Shipping;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** Admin-editable delivery charges; read everywhere through App\Support\Shipping. */
class ShippingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Shipping';

    protected static ?string $title = 'Shipping charges';

    protected static ?int $navigationSort = 11;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'shipping_fee' => Shipping::fee() / 100,
            'free_delivery_above' => Shipping::freeAbove() / 100,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Physical books')
                ->description('eBooks and gift cards never have a delivery charge.')
                ->columns(2)
                ->components([
                    TextInput::make('shipping_fee')->label('Delivery charge per order (₹)')
                        ->numeric()->minValue(0)->required()->prefix('₹'),
                    TextInput::make('free_delivery_above')->label('Free delivery on orders above (₹)')
                        ->numeric()->minValue(0)->required()->prefix('₹')
                        ->helperText('Set to 0 to always charge delivery. Compared with the order value after discounts.'),
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

        Setting::put([
            'shipping_fee' => (int) round(((float) $state['shipping_fee']) * 100),
            'free_delivery_above' => (int) round(((float) $state['free_delivery_above']) * 100),
        ]);

        Notification::make()->success()->title('Shipping charges saved')->send();
    }
}
