@php
    /** @var \App\Models\Booking $booking */
    $booking = $getRecord();
    $extras = $booking->extras;
    $paidPayments = $booking->payments->where('status', 'succeeded');
@endphp
<div class="ag-quote ag-breakdown">
    <dl class="ag-quote-lines">
        <div>
            <dt>{{ __('admin.booking.accommodation') }} · {{ trans_choice('admin.booking.nights_x', $booking->nights, ['count' => $booking->nights]) }}
                <span class="ag-muted">({{ money($booking->nights ? $booking->room_total / $booking->nights : 0, true) }} {{ __('admin.booking.per_night') }})</span></dt>
            <dd>{{ money($booking->room_total, true) }}</dd>
        </div>
        @foreach ($extras as $extra)
            <div class="is-sub">
                <dt>{{ $extra->name }} <span>× {{ $extra->pivot->quantity }} · {{ money($extra->pivot->unit_price, true) }}</span></dt>
                <dd>{{ money($extra->pivot->total, true) }}</dd>
            </div>
        @endforeach
        @if ((float) $booking->discount > 0)
            <div class="is-discount">
                <dt>{{ __('admin.fields.discount') }} @if ($booking->promoCode)· {{ $booking->promoCode->code }}@endif</dt>
                <dd>−{{ money($booking->discount, true) }}</dd>
            </div>
        @endif
        <div class="is-sub">
            <dt>{{ __('admin.fields.tax') }}</dt>
            <dd>{{ money($booking->tax, true) }}</dd>
        </div>
    </dl>
    <div class="ag-quote-total">
        <span>{{ __('admin.fields.total') }}</span>
        <strong>{{ money($booking->total, true) }}</strong>
    </div>
    <dl class="ag-quote-lines ag-breakdown-paid">
        <div class="is-discount">
            <dt>{{ __('admin.fields.paid') }} @if ($paidPayments->count())<span class="ag-muted">({{ trans_choice('admin.booking.payments_x', $paidPayments->count(), ['count' => $paidPayments->count()]) }})</span>@endif</dt>
            <dd>{{ money($booking->amount_paid, true) }}</dd>
        </div>
        <div @class(['is-error' => $booking->balance > 0])>
            <dt><strong>{{ __('admin.fields.balance') }}</strong></dt>
            <dd>{{ money($booking->balance, true) }}</dd>
        </div>
    </dl>
</div>
