<?php

namespace App\Livewire\Booking;

use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Component;

class Confirmation extends Component
{
    public Booking $booking;

    public bool $justPaid = false;

    public function mount(Booking $booking, BookingService $service)
    {
        $this->booking = $booking;

        if (! $service->canAccess($booking)) {
            session()->flash('status', __('booking.errors.access_denied'));

            return $this->redirectRoute('booking.lookup');
        }

        $this->justPaid = (bool) session('payment_success');
    }

    public function render(BookingService $service)
    {
        $this->booking->loadMissing(['roomType', 'extras', 'payments', 'promoCode', 'room']);

        return view('livewire.booking.confirmation', [
            'ics' => 'data:text/calendar;charset=utf-8,'.rawurlencode($service->ics($this->booking)),
        ])->title(__('booking.confirm.meta_title', ['reference' => $this->booking->reference]));
    }
}
