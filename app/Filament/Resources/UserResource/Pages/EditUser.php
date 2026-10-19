<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    /** "Full access" is stored as no section list (the checkbox list is hidden, so it isn't saved on its own). */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return UserResource::applyFullAccess($data, $this->data);
    }
}
