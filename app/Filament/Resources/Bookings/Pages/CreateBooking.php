<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\PromoCode;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected static bool $canCreateAnother = false;

    /** Prefill from the query string (used by the tape chart): ?room_type=&room=&check_in=&check_out= */
    protected function afterFill(): void
    {
        $request = request();
        $prefill = [];

        if ($room = Room::find($request->query('room'))) {
            $prefill['room_type_id'] = $room->room_type_id;
        } elseif ($type = RoomType::find($request->query('room_type'))) {
            $prefill['room_type_id'] = $type->id;
        }

        if ($in = $this->parseDate($request->query('check_in'))) {
            $prefill['check_in'] = $in->toDateString();
            $out = $this->parseDate($request->query('check_out'));
            $prefill['check_out'] = ($out && $out->gt($in) ? $out : $in->addDays(2))->toDateString();
        }

        if ($room) {
            $prefill['room_id'] = $room->id;
        }

        if ($guest = User::where('role', 'guest')->find($request->query('guest'))) {
            [$first, $last] = array_pad(explode(' ', $guest->name, 2), 2, '');
            $prefill += ['user_id' => $guest->id, 'first_name' => $first, 'last_name' => $last,
                'email' => $guest->email, 'phone' => $guest->phone, 'country' => $guest->country];
        }

        if ($prefill) {
            $this->data = array_merge($this->data ?? [], $prefill);
        }
    }

    protected function parseDate(mixed $value): ?CarbonImmutable
    {
        try {
            return is_string($value) && $value !== '' ? CarbonImmutable::parse($value)->startOfDay() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(BookingService::class);
        $type = RoomType::findOrFail($data['room_type_id']);
        $extras = collect($data['extras'] ?? [])->mapWithKeys(fn ($id) => [(int) $id => 1])->all();
        $promo = ! empty($data['promo_code_id']) ? PromoCode::find($data['promo_code_id'])?->code : null;
        $roomId = $data['room_id'] ?? null;

        unset($data['room_type_id'], $data['room_id'], $data['extras'], $data['promo_code_id']);

        try {
            $booking = $service->create($type, $data, $extras, $promo);
        } catch (\RuntimeException) {
            Notification::make()
                ->title(__('admin.booking.no_rooms'))
                ->body(__('admin.booking.no_rooms_body'))
                ->danger()
                ->persistent()
                ->send();
            $this->halt();
        }

        if ($roomId && (int) $roomId !== (int) $booking->room_id
            && $service->freeRooms($type, $booking->check_in, $booking->check_out, $booking->id)->contains('id', (int) $roomId)) {
            $booking->update(['room_id' => (int) $roomId]);
        }

        BookingForm::stampStatus($booking);

        return $booking;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('admin.notify.booking_created', ['ref' => $this->record->reference]);
    }

    protected function getRedirectUrl(): string
    {
        return BookingResource::getUrl('view', ['record' => $this->record]);
    }
}
