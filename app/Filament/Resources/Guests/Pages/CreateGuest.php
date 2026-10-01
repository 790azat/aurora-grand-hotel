<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'guest';
        $data['password'] ??= Str::random(16);

        return $data;
    }
}
