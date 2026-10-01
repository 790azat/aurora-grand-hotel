<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Lookup extends Component
{
    #[Url(as: 'ref', except: '')]
    public string $reference = '';

    public string $email = '';

    public ?string $found = null;

    public function mount(BookingService $service): void
    {
        // Opening /manage-booking?ref=… for a booking this visitor already has access to shows it right away.
        $this->reference = Str::upper(trim($this->reference ?: (string) request()->query('ref', '')));
        if ($this->reference && ($booking = Booking::where('reference', Str::upper(trim($this->reference)))->first()) && $service->canAccess($booking)) {
            $this->found = $booking->reference;
        }
    }

    public function find(BookingService $service): void
    {
        $this->reference = Str::upper(trim($this->reference));
        $this->email = trim($this->email);
        $this->validate([
            'reference' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:120'],
        ]);

        $key = 'booking-lookup:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('reference', __('booking.lookup.too_many', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }

        $booking = Booking::where('reference', $this->reference)
            ->whereRaw('lower(email) = ?', [Str::lower($this->email)])
            ->first();

        if (! $booking) {
            RateLimiter::hit($key, 60);
            $this->found = null;
            $this->addError('reference', __('booking.lookup.not_found'));

            return;
        }

        RateLimiter::clear($key);
        $service->rememberAccess($booking);
        $this->found = $booking->reference;
        unset($this->booking);
    }

    public function open(string $reference, BookingService $service): void
    {
        $booking = Booking::where('reference', $reference)->first();
        if ($booking && $service->canAccess($booking)) {
            $this->found = $booking->reference;
            $this->reference = $booking->reference;
            unset($this->booking);
        }
    }

    public function close(): void
    {
        $this->found = null;
        $this->reference = '';
        $this->email = '';
        unset($this->booking);
    }

    public function cancel(BookingService $service): void
    {
        $booking = $this->booking;
        if (! $booking || ! $service->canAccess($booking)) {
            return;
        }
        if (! $booking->canBeCancelledByGuest()) {
            $this->dispatch('notify', message: __('booking.cancel.not_allowed'), type: 'error');

            return;
        }

        $service->cancel($booking);
        unset($this->booking);
        $this->dispatch('booking-cancelled');
        $this->dispatch('notify', message: __('booking.cancel.done'), type: 'success');
    }

    #[Computed]
    public function booking(): ?Booking
    {
        if (! $this->found) {
            return null;
        }
        $booking = Booking::with(['roomType', 'extras', 'payments', 'promoCode'])->where('reference', $this->found)->first();

        return $booking && app(BookingService::class)->canAccess($booking) ? $booking : null;
    }

    #[Computed]
    public function recent()
    {
        $refs = session('my_bookings', []);

        return $refs ? Booking::with('roomType')->whereIn('reference', $refs)->latest()->take(5)->get() : collect();
    }

    public function render()
    {
        return view('livewire.booking.lookup')->title(__('booking.lookup.meta_title'));
    }
}
