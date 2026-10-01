<?php

namespace App\Livewire\Account;

use App\Models\Booking;
use App\Models\Review;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingShow extends Component
{
    public Booking $booking;

    public int $rating = 5;

    public string $title = '';

    public string $body = '';

    public bool $reviewSent = false;

    public function mount(Booking $booking): void
    {
        $user = Auth::user();
        abort_unless($booking->user_id === $user->id || $user->isStaff(), 403);
        $this->booking = $booking;
    }

    public function cancel(BookingService $service): void
    {
        $booking = $this->booking->fresh();
        if ($booking->user_id !== Auth::id() || ! $booking->canBeCancelledByGuest()) {
            $this->dispatch('notify', message: __('booking.cancel.not_allowed'), type: 'error');

            return;
        }

        $service->cancel($booking);
        $this->booking = $booking->fresh();
        $this->dispatch('booking-cancelled');
        $this->dispatch('notify', message: __('booking.cancel.done'), type: 'success');
    }

    public function submitReview(): void
    {
        $booking = $this->booking->fresh();
        if ($booking->user_id !== Auth::id() || ! $booking->canBeReviewed()) {
            return;
        }

        $data = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $user = Auth::user();
        Review::create([
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'room_type_id' => $booking->room_type_id,
            'name' => $user->name,
            'country' => $user->country ?: $booking->country,
            'rating' => $data['rating'],
            'title' => trim($data['title']),
            'body' => trim($data['body']),
            'locale' => app()->getLocale(),
            'is_approved' => false,
        ]);

        $this->reviewSent = true;
        $this->reset('title', 'body');
    }

    public function render()
    {
        $this->booking->loadMissing(['roomType', 'extras', 'payments', 'promoCode', 'review']);

        return view('livewire.account.booking-show')
            ->title(__('account.booking.title', ['reference' => $this->booking->reference]));
    }
}
