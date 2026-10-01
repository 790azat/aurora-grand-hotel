<?php

namespace App\Services;

use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Extra;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Pricing, availability and booking lifecycle.
 *
 * Nightly price = base price (or weekend price on Fri/Sat nights),
 * adjusted by the percent modifier of the active season covering that night.
 */
class BookingService
{
    /** Rooms of this type that are free for the whole [checkIn, checkOut) range. */
    public function freeRooms(RoomType $type, CarbonInterface|string $checkIn, CarbonInterface|string $checkOut, ?int $ignoreBookingId = null): Collection
    {
        $checkIn = CarbonImmutable::parse($checkIn)->toDateString();
        $checkOut = CarbonImmutable::parse($checkOut)->toDateString();

        $busy = Booking::overlapping($checkIn, $checkOut)
            ->where('room_type_id', $type->id)
            ->whereNotNull('room_id')
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->pluck('room_id');

        return $type->rooms()
            ->where('status', 'available')
            ->whereNotIn('id', $busy)
            ->orderBy('number')
            ->get();
    }

    public function availableCount(RoomType $type, $checkIn, $checkOut): int
    {
        return $this->freeRooms($type, $checkIn, $checkOut)->count();
    }

    /**
     * Room types that can host the party for the dates, each with a quote.
     *
     * @return Collection<int, array{type: RoomType, available: int, quote: array}>
     */
    public function search($checkIn, $checkOut, int $adults, int $children = 0): Collection
    {
        return RoomType::active()->with('amenities')->get()
            ->filter(fn (RoomType $t) => $t->max_adults >= $adults && $t->max_adults + $t->max_children >= $adults + $children)
            ->map(fn (RoomType $t) => [
                'type' => $t,
                'available' => $this->availableCount($t, $checkIn, $checkOut),
                'quote' => $this->quote($t, $checkIn, $checkOut, $adults, $children),
            ])
            ->values();
    }

    public function minNights(RoomType $type, $checkIn): int
    {
        $date = CarbonImmutable::parse($checkIn)->toDateString();
        $seasonMin = Season::where('is_active', true)
            ->whereDate('starts_on', '<=', $date)->whereDate('ends_on', '>=', $date)
            ->where(fn ($q) => $q->whereNull('room_type_id')->orWhere('room_type_id', $type->id))
            ->max('min_nights');

        return max($type->min_nights, (int) $seasonMin, 1);
    }

    /**
     * Full price breakdown for a stay.
     *
     * @param  array<int, int>  $extras  extra_id => selected (truthy)
     */
    public function quote(RoomType $type, $checkIn, $checkOut, int $adults = 2, int $children = 0, array $extras = [], ?string $promoCode = null): array
    {
        $checkIn = CarbonImmutable::parse($checkIn)->startOfDay();
        $checkOut = CarbonImmutable::parse($checkOut)->startOfDay();
        $nights = max(0, (int) $checkIn->diffInDays($checkOut));

        $seasons = Season::where('is_active', true)
            ->whereDate('starts_on', '<', $checkOut->toDateString())
            ->whereDate('ends_on', '>=', $checkIn->toDateString())
            ->where(fn ($q) => $q->whereNull('room_type_id')->orWhere('room_type_id', $type->id))
            ->get();

        $nightly = [];
        if ($nights > 0) {
            foreach (CarbonPeriod::create($checkIn, $checkOut->subDay()) as $night) {
                $isWeekend = in_array($night->dayOfWeekIso, [5, 6], true);
                $price = (float) ($isWeekend && $type->weekend_price ? $type->weekend_price : $type->base_price);
                $season = $seasons->first(fn (Season $s) => $night->between($s->starts_on, $s->ends_on));
                if ($season) {
                    $price = $price * (100 + $season->price_modifier) / 100;
                }
                $nightly[] = [
                    'date' => $night->toDateString(),
                    'price' => round($price, 2),
                    'weekend' => $isWeekend,
                    'season' => $season?->name,
                ];
            }
        }

        $roomTotal = round(array_sum(array_column($nightly, 'price')), 2);
        $guests = $adults + $children;

        $extraLines = Extra::where('is_active', true)
            ->whereIn('id', array_keys(array_filter($extras)))
            ->get()
            ->map(function (Extra $extra) use ($nights, $guests) {
                $qty = $extra->quantityFor(max($nights, 1), $guests);

                return [
                    'id' => $extra->id,
                    'name' => $extra->name,
                    'quantity' => $qty,
                    'unit_price' => (float) $extra->price,
                    'total' => round($qty * (float) $extra->price, 2),
                ];
            })->values()->all();
        $extrasTotal = round(array_sum(array_column($extraLines, 'total')), 2);

        $promo = null;
        $promoError = null;
        $discount = 0.0;
        if ($promoCode) {
            $promo = PromoCode::whereRaw('upper(code) = ?', [Str::upper(trim($promoCode))])->first();
            if ($promo && $promo->isUsable($nights)) {
                $discount = $promo->discountFor($roomTotal);
            } else {
                $promoError = 'invalid';
                $promo = null;
            }
        }

        $subtotal = $roomTotal + $extrasTotal - $discount;
        $taxPercent = (float) Setting::get('tax_percent', 10);
        $tax = round($subtotal * $taxPercent / 100, 2);

        return [
            'nights' => $nights,
            'nightly' => $nightly,
            'avg_nightly' => $nights ? round($roomTotal / $nights, 2) : (float) $type->base_price,
            'room_total' => $roomTotal,
            'extras' => $extraLines,
            'extras_total' => $extrasTotal,
            'promo' => $promo?->code,
            'promo_id' => $promo?->id,
            'promo_error' => $promoError,
            'discount' => $discount,
            'tax_percent' => $taxPercent,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
            'min_nights' => $this->minNights($type, $checkIn),
        ];
    }

    /**
     * Create a booking, assigning the first free room of the type.
     *
     * @param  array  $data  check_in, check_out, adults, children, first_name, last_name, email, phone,
     *                       country, arrival_time, special_requests, payment_method, source, user_id, status
     *
     * @throws \RuntimeException when no room is free
     */
    public function create(RoomType $type, array $data, array $extras = [], ?string $promoCode = null): Booking
    {
        return DB::transaction(function () use ($type, $data, $extras, $promoCode) {
            $room = $this->freeRooms($type, $data['check_in'], $data['check_out'])->first();
            if (! $room) {
                throw new \RuntimeException('No rooms available for the selected dates.');
            }

            $quote = $this->quote($type, $data['check_in'], $data['check_out'], (int) ($data['adults'] ?? 2), (int) ($data['children'] ?? 0), $extras, $promoCode);

            $booking = Booking::create(array_merge([
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'card',
                'source' => 'website',
                'locale' => app()->getLocale(),
            ], $data, [
                'room_type_id' => $type->id,
                'room_id' => $room->id,
                'promo_code_id' => $quote['promo_id'],
                'room_total' => $quote['room_total'],
                'extras_total' => $quote['extras_total'],
                'discount' => $quote['discount'],
                'tax' => $quote['tax'],
                'total' => $quote['total'],
            ]));

            foreach ($quote['extras'] as $line) {
                $booking->extras()->attach($line['id'], [
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['total'],
                ]);
            }

            if ($quote['promo_id']) {
                PromoCode::whereKey($quote['promo_id'])->increment('used_count');
            }

            // "Pay at hotel" bookings are guaranteed immediately; card bookings confirm after payment.
            if ($booking->payment_method === 'on_arrival' && $booking->status === 'pending') {
                $this->confirm($booking);
            }

            return $booking->fresh();
        });
    }

    /** Recalculate totals after dates/room type change (used by admin). */
    public function recalculate(Booking $booking): Booking
    {
        $quote = $this->quote(
            $booking->roomType,
            $booking->check_in,
            $booking->check_out,
            $booking->adults,
            $booking->children,
            $booking->extras->pluck('id')->mapWithKeys(fn ($id) => [$id => 1])->all(),
            $booking->promoCode?->code,
        );

        $booking->update([
            'nights' => $quote['nights'],
            'room_total' => $quote['room_total'],
            'extras_total' => $quote['extras_total'],
            'discount' => $quote['discount'],
            'tax' => $quote['tax'],
            'total' => $quote['total'],
        ]);

        return $booking;
    }

    /** Demo card payment. Card 4000 0000 0000 0002 is always declined. */
    public function pay(Booking $booking, string $cardNumber, ?float $amount = null): Payment
    {
        $digits = preg_replace('/\D/', '', $cardNumber);
        $declined = $digits === '4000000000000002';
        $amount ??= $booking->balance;

        $payment = $booking->payments()->create([
            'amount' => $amount,
            'method' => 'card',
            'status' => $declined ? 'failed' : 'succeeded',
            'transaction_id' => 'demo_'.Str::lower(Str::random(14)),
            'card_brand' => $this->cardBrand($digits),
            'card_last4' => substr($digits, -4),
        ]);

        if (! $declined) {
            $this->registerPayment($booking, $amount);
            if ($booking->status === 'pending') {
                $this->confirm($booking);
            }
        }

        return $payment;
    }

    public function registerPayment(Booking $booking, float $amount): void
    {
        $paid = round((float) $booking->amount_paid + $amount, 2);
        $booking->update([
            'amount_paid' => $paid,
            'payment_status' => $paid >= (float) $booking->total ? 'paid' : 'unpaid',
        ]);
    }

    public function confirm(Booking $booking): void
    {
        $booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $this->notify($booking);
    }

    public function cancel(Booking $booking): void
    {
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'payment_status' => (float) $booking->amount_paid > 0 ? 'refunded' : $booking->payment_status,
        ]);
    }

    public function checkIn(Booking $booking): void
    {
        $booking->update(['status' => 'checked_in', 'checked_in_at' => now()]);
    }

    public function checkOut(Booking $booking): void
    {
        $booking->update(['status' => 'checked_out', 'checked_out_at' => now()]);
        $booking->room?->update(['housekeeping' => 'dirty']);
    }

    protected function notify(Booking $booking): void
    {
        try {
            Mail::to($booking->email)->locale($booking->locale)->send(new BookingConfirmed($booking));
        } catch (\Throwable $e) {
            Log::warning('Booking email failed: '.$e->getMessage());
        }
    }

    protected function cardBrand(string $digits): string
    {
        return match (true) {
            str_starts_with($digits, '4') => 'Visa',
            (bool) preg_match('/^5[1-5]/', $digits) => 'Mastercard',
            (bool) preg_match('/^3[47]/', $digits) => 'Amex',
            default => 'Card',
        };
    }
}
