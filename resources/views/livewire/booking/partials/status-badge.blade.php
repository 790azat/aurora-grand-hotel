@php
    $statusClass = match ($booking->status) {
        'confirmed', 'checked_in' => 'badge-green',
        'pending' => 'badge-gold',
        'cancelled', 'no_show' => 'badge-red',
        default => 'badge-gray',
    };
    $payClass = match ($booking->payment_status) {
        'paid' => 'badge-green',
        'refunded' => 'badge-gray',
        default => $booking->status === 'cancelled' ? 'badge-gray' : 'badge-gold',
    };
@endphp
<span class="{{ $statusClass }}">{{ __('booking.status.'.$booking->status) }}</span>
@if ($withPayment ?? true)
    <span class="{{ $payClass }}">{{ __('booking.payment_status.'.$booking->payment_status) }}</span>
@endif
