<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingActions;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('admin.booking.view_title', ['ref' => $this->record->reference]);
    }

    public function getSubheading(): ?string
    {
        return $this->record->guest_name.' · '.$this->record->check_in->isoFormat('D MMM').' – '.$this->record->check_out->isoFormat('D MMM YYYY');
    }

    protected function getHeaderActions(): array
    {
        return [
            BookingActions::confirm(),
            BookingActions::checkIn(),
            BookingActions::checkOut(),
            BookingActions::recordPayment(),
            EditAction::make()->icon(Heroicon::OutlinedPencilSquare),
            ActionGroup::make([
                BookingActions::invoice(),
                BookingActions::sendConfirmation(),
                BookingActions::cancel(),
            ])->icon(Heroicon::EllipsisVertical)->color('gray')->button()->label(__('admin.actions.more')),
        ];
    }

    #[On('booking-updated')]
    public function refreshBooking(): void
    {
        $this->record->refresh()->load(['roomType', 'room', 'extras', 'payments', 'promoCode', 'user']);
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load(['roomType', 'room', 'extras', 'payments', 'promoCode', 'user']);
    }
}
