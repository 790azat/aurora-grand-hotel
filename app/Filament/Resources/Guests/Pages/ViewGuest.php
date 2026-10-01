<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Guests\GuestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newBooking')->label(__('admin.booking.new'))->icon(Heroicon::OutlinedPlus)
                ->url(fn () => BookingResource::getUrl('create').'?guest='.$this->record->getKey()),
            EditAction::make(),
        ];
    }
}
