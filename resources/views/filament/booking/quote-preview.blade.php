<div class="ag-quote">
    @if (! $quote)
        <div class="ag-quote-empty">
            <x-filament::icon icon="heroicon-o-calculator" class="ag-quote-empty-icon" />
            <p>{{ __('admin.booking.quote_empty') }}</p>
        </div>
    @else
        <dl class="ag-quote-lines">
            <div>
                <dt>{{ trans_choice('admin.booking.nights_x', $quote['nights'], ['count' => $quote['nights']]) }} × {{ money($quote['avg_nightly'], true) }}</dt>
                <dd>{{ money($quote['room_total'], true) }}</dd>
            </div>
            @foreach ($quote['extras'] as $line)
                <div class="is-sub">
                    <dt>{{ $line['name'] }} @if ($line['quantity'] > 1)<span>× {{ $line['quantity'] }}</span>@endif</dt>
                    <dd>{{ money($line['total'], true) }}</dd>
                </div>
            @endforeach
            @if ($quote['discount'] > 0)
                <div class="is-discount">
                    <dt>{{ __('admin.fields.discount') }} · {{ $quote['promo'] }}</dt>
                    <dd>−{{ money($quote['discount'], true) }}</dd>
                </div>
            @endif
            @if ($quote['promo_error'])
                <div class="is-error"><dt>{{ __('admin.booking.promo_invalid') }}</dt><dd></dd></div>
            @endif
            <div class="is-sub">
                <dt>{{ __('admin.fields.tax') }} ({{ (float) $quote['tax_percent'] }}%)</dt>
                <dd>{{ money($quote['tax'], true) }}</dd>
            </div>
        </dl>
        <div class="ag-quote-total">
            <span>{{ __('admin.fields.total') }}</span>
            <strong>{{ money($quote['total'], true) }}</strong>
        </div>
        @if ($record && abs((float) $record->total - $quote['total']) > 0.009)
            <p class="ag-quote-note">{{ __('admin.booking.saved_total', ['amount' => money($record->total, true)]) }}</p>
        @endif
        @if ($quote['nights'] < $quote['min_nights'])
            <p class="ag-quote-warn">{{ __('admin.booking.min_nights', ['count' => $quote['min_nights']]) }}</p>
        @endif
    @endif
</div>
