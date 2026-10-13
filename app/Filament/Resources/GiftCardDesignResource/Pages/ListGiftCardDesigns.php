<?php

namespace App\Filament\Resources\GiftCardDesignResource\Pages;

use App\Filament\Resources\GiftCardDesignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGiftCardDesigns extends ListRecords
{
    protected static string $resource = GiftCardDesignResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
