<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getTitle(): string
    {
        return __('admin.booking.edit_title', ['ref' => $this->record->reference]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()->visible(fn () => (bool) auth()->user()?->isManager()),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['extras'] = $this->record->extras()->pluck('extras.id')->map(fn ($id) => (string) $id)->all();
        $data['check_in'] = $this->record->check_in->toDateString();
        $data['check_out'] = $this->record->check_out->toDateString();

        return $data;
    }

    /** @param  Booking  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(BookingService::class);
        $extraIds = collect($data['extras'] ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();
        unset($data['extras']);

        $oldExtras = $record->extras()->pluck('extras.id')->sort()->values()->all();
        $record->fill($data);
        $record->nights = $record->check_in->diffInDays($record->check_out);

        $pricingChanged = $record->isDirty(['room_type_id', 'check_in', 'check_out', 'adults', 'children', 'promo_code_id'])
            || $extraIds !== $oldExtras;

        // Make sure the chosen room is really free for the new dates.
        if ($record->room_id && $record->isDirty(['room_id', 'room_type_id', 'check_in', 'check_out'])
            && in_array($record->status, Booking::ACTIVE_STATUSES, true)) {
            $free = $service->freeRooms($record->roomType()->first(), $record->check_in, $record->check_out, $record->id);
            if (! $free->contains('id', $record->room_id)) {
                Notification::make()->title(__('admin.booking.room_taken'))->danger()->send();
                $this->halt();
            }
        }

        $record->save();

        if ($pricingChanged) {
            $quote = $service->quote(
                $record->roomType,
                $record->check_in,
                $record->check_out,
                $record->adults,
                $record->children,
                array_fill_keys($extraIds, 1),
                $record->promoCode?->code,
            );
            $record->extras()->sync(collect($quote['extras'])->mapWithKeys(fn ($line) => [$line['id'] => [
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'total' => $line['total'],
            ]])->all());

            $record = $service->recalculate($record->fresh(['extras', 'promoCode', 'roomType']));

            if ((float) $record->amount_paid > 0 && $record->payment_status !== 'refunded') {
                $record->update(['payment_status' => (float) $record->amount_paid >= (float) $record->total ? 'paid' : 'unpaid']);
            }
        }

        BookingForm::stampStatus($record);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return BookingResource::getUrl('view', ['record' => $this->record]);
    }
}
