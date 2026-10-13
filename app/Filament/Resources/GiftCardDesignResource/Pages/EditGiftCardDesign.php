<?php

namespace App\Filament\Resources\GiftCardDesignResource\Pages;

use App\Filament\Resources\GiftCardDesignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGiftCardDesign extends EditRecord
{
    protected static string $resource = GiftCardDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
