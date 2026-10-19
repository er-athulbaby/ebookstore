<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** "Full access" is stored as no section list (the checkbox list is hidden, so it isn't saved on its own). */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::applyFullAccess($data, $this->data);
    }
}
