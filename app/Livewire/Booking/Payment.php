<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

class Payment extends Component
{
    public Booking $booking;

    public string $card_number = '';

    public string $card_expiry = '';

    public string $card_cvc = '';

    public string $card_name = '';

    public ?string $declined = null;

    /** Selected method tab: card or idram. */
    #[Url]
    public string $method = '';

    public string $idram_wallet = '';

    public function mount(Booking $booking, BookingService $service)
    {
        $this->booking = $booking;

        if (! $service->canAccess($booking)) {
            return $this->denied();
        }

        if ($booking->status === 'cancelled' || $booking->balance <= 0) {
            return $this->redirectRoute('booking.confirmation', $booking);
        }

        $this->card_name = $booking->guest_name;

        if (! in_array($this->method, ['card', 'idram'], true)) {
            $this->method = $booking->payment_method === 'idram' ? 'idram' : 'card';
        }
    }

    public function setMethod(string $method): void
    {
        $this->method = in_array($method, ['card', 'idram'], true) ? $method : 'card';
        $this->declined = null;
        $this->resetErrorBag();
    }

    /** Demo Idram wallet payment (live Idram posts the form straight to Idram instead). */
    public function payIdram(BookingService $service)
    {
        $this->declined = null;
        $booking = $this->booking->fresh();

        if (! $service->canAccess($booking)) {
            return $this->denied();
        }
        if ($booking->status === 'cancelled' || $booking->balance <= 0) {
            return $this->redirectRoute('booking.confirmation', $booking);
        }

        $this->validate(
            ['idram_wallet' => ['required', 'regex:/^\d{9}$/']],
            ['idram_wallet.regex' => __('booking.idram.invalid_wallet')],
        );

        $key = 'demo-pay:'.$booking->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->declined = __('booking.pay.too_many', ['seconds' => RateLimiter::availableIn($key)]);

            return null;
        }
        RateLimiter::hit($key, 300);

        usleep(1_200_000);

        $payment = $service->payIdram($booking, $this->idram_wallet);

        if ($payment->status !== 'succeeded') {
            $this->declined = __('booking.idram.insufficient');

            return null;
        }

        if ($booking->payment_method !== 'idram') {
            $booking->update(['payment_method' => 'idram']);
        }

        session()->flash('payment_success', true);

        return $this->redirectRoute('booking.confirmation', $booking);
    }

    public function fillIdramWallet(bool $decline = false): void
    {
        $this->resetErrorBag();
        $this->declined = null;
        $this->idram_wallet = $decline ? '100000000' : '100200300';
    }

    protected function denied()
    {
        session()->flash('status', __('booking.errors.access_denied'));

        return $this->redirectRoute('booking.lookup');
    }

    public function pay(BookingService $service)
    {
        $this->declined = null;
        $booking = $this->booking->fresh();

        if (! $service->canAccess($booking)) {
            return $this->denied();
        }
        if ($booking->status === 'cancelled' || $booking->balance <= 0) {
            return $this->redirectRoute('booking.confirmation', $booking);
        }

        $this->card_number = trim(preg_replace('/\s+/', ' ', $this->card_number));
        $this->validate([
            'card_number' => ['required', function ($attr, $value, $fail) {
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 13 || strlen($digits) > 19 || ! self::luhn($digits)) {
                    $fail(__('booking.pay.invalid_number'));
                }
            }],
            'card_expiry' => ['required', function ($attr, $value, $fail) {
                if (! preg_match('/^(0[1-9]|1[0-2])\s*\/\s*(\d{2})$/', trim($value), $m)) {
                    $fail(__('booking.pay.invalid_expiry'));

                    return;
                }
                $expires = now()->setDate(2000 + (int) $m[2], (int) $m[1], 1)->endOfMonth();
                if ($expires->isPast()) {
                    $fail(__('booking.pay.expired'));
                }
            }],
            'card_cvc' => ['required', 'regex:/^\d{3,4}$/'],
            'card_name' => ['required', 'string', 'max:80'],
        ], [
            'card_cvc.regex' => __('booking.pay.invalid_cvc'),
        ]);

        $key = 'demo-pay:'.$booking->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->declined = __('booking.pay.too_many', ['seconds' => RateLimiter::availableIn($key)]);

            return null;
        }
        RateLimiter::hit($key, 300);

        // A short pause so the demo feels like a real payment gateway round-trip.
        usleep(1_200_000);

        $payment = $service->pay($booking, $this->card_number);

        if ($payment->status !== 'succeeded') {
            $this->declined = __('booking.pay.declined');
            $this->card_cvc = '';

            return null;
        }

        if ($booking->payment_method !== 'card') {
            $booking->update(['payment_method' => 'card']);
        }

        session()->flash('payment_success', true);

        return $this->redirectRoute('booking.confirmation', $booking);
    }

    public function fillTestCard(bool $decline = false): void
    {
        $this->resetErrorBag();
        $this->declined = null;
        $this->card_number = $decline ? '4000 0000 0000 0002' : '4242 4242 4242 4242';
        $this->card_expiry = now()->addYears(2)->format('m/y');
        $this->card_cvc = '123';
        $this->card_name = $this->card_name ?: $this->booking->guest_name;
    }

    public static function luhn(string $digits): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = ! $alt;
        }

        return $sum % 10 === 0;
    }

    public function render()
    {
        $this->booking->loadMissing(['roomType', 'extras']);

        return view('livewire.booking.payment')
            ->title(__('booking.pay.meta_title'));
    }
}
