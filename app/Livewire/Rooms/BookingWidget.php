<?php

namespace App\Livewire\Rooms;

use App\Models\RoomType;
use App\Services\BookingService;
use Carbon\Carbon;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Sticky booking sidebar on the room page: live availability and price breakdown. */
class BookingWidget extends Component
{
    #[Locked]
    public int $roomTypeId;

    public string $check_in = '';

    public string $check_out = '';

    public int $adults = 2;

    public int $children = 0;

    public function mount(RoomType $roomType): void
    {
        $this->roomTypeId = $roomType->id;
        $q = request()->query();
        $in = $this->date($q['check_in'] ?? null);
        $out = $this->date($q['check_out'] ?? null);
        $this->check_in = $in && $in >= today()->toDateString() ? $in : today()->addDay()->toDateString();
        $this->check_out = $out && $out > $this->check_in ? $out : Carbon::parse($this->check_in)->addDays(max(3, $roomType->min_nights))->toDateString();
        $this->adults = min((int) ($q['adults'] ?? 2) ?: 2, $roomType->max_adults);
        $this->children = min((int) ($q['children'] ?? 0), $roomType->max_children);
    }

    protected function date(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }

    public function updatedCheckIn(): void
    {
        if (! $this->date($this->check_in) || $this->check_in < today()->toDateString()) {
            $this->check_in = today()->toDateString();
        }
        if (! $this->date($this->check_out) || $this->check_out <= $this->check_in) {
            $this->check_out = Carbon::parse($this->check_in)->addDay()->toDateString();
        }
    }

    public function updatedCheckOut(): void
    {
        if (! $this->date($this->check_out) || $this->check_out <= $this->check_in) {
            $this->check_out = Carbon::parse($this->check_in)->addDay()->toDateString();
        }
    }

    public function render(BookingService $booking)
    {
        $type = RoomType::findOrFail($this->roomTypeId);
        $this->adults = max(1, min($this->adults, $type->max_adults));
        $this->children = max(0, min($this->children, $type->max_children));

        $valid = $this->date($this->check_in) && $this->date($this->check_out) && $this->check_out > $this->check_in;
        $quote = $valid ? $booking->quote($type, $this->check_in, $this->check_out, $this->adults, $this->children) : null;
        $available = $valid ? $booking->availableCount($type, $this->check_in, $this->check_out) : 0;
        $minNights = $quote['min_nights'] ?? $type->min_nights;

        return view('livewire.rooms.booking-widget', [
            'type' => $type,
            'quote' => $quote,
            'available' => $available,
            'minNights' => $minNights,
            'tooShort' => $quote && $quote['nights'] < $minNights,
            'bookUrl' => route('booking', [
                'room_type' => $type->slug,
                'check_in' => $this->check_in,
                'check_out' => $this->check_out,
                'adults' => $this->adults,
                'children' => $this->children,
            ]),
        ]);
    }
}
