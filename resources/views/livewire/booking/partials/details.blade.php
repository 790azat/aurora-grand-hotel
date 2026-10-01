{{-- Booking details + price breakdown. Expects $booking (with roomType, extras, payments loaded). --}}
@php
    $b = $booking;
    $fmtD = fn ($d, $f = 'D, j M Y') => \Illuminate\Support\Carbon::parse($d)->translatedFormat($f);
    $countryLabel = __('booking.countries')[$b->country] ?? $b->country;
@endphp
<div class="grid gap-6 lg:grid-cols-5">
    <div class="card overflow-hidden lg:col-span-3">
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:p-6">
            <img src="{{ $b->roomType->cover }}" alt="{{ $b->roomType->name }}" class="aspect-[4/3] w-full rounded-xl object-cover sm:w-36">
            <div class="min-w-0">
                <p class="eyebrow">{{ __('booking.your_room') }}</p>
                <h3 class="mt-1 font-serif text-2xl">{{ $b->roomType->name }}</h3>
                <p class="mt-1 text-sm text-muted">{{ $b->roomType->beds }} · {{ $b->roomType->view }} · {{ $b->roomType->size_m2 }} m²</p>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-px border-t border-line bg-line text-sm sm:grid-cols-4">
            <div class="bg-surface p-4"><dt class="text-xs text-muted">{{ __('booking.check_in') }}</dt><dd class="mt-0.5 font-semibold">{{ $fmtD($b->check_in) }}</dd><dd class="text-xs text-muted">{{ __('booking.from_time', ['time' => setting('check_in_time')]) }}</dd></div>
            <div class="bg-surface p-4"><dt class="text-xs text-muted">{{ __('booking.check_out') }}</dt><dd class="mt-0.5 font-semibold">{{ $fmtD($b->check_out) }}</dd><dd class="text-xs text-muted">{{ __('booking.until_time', ['time' => setting('check_out_time')]) }}</dd></div>
            <div class="bg-surface p-4"><dt class="text-xs text-muted">{{ __('booking.stay') }}</dt><dd class="mt-0.5 font-semibold">{{ trans_choice('booking.nights_count', $b->nights) }}</dd></div>
            <div class="bg-surface p-4"><dt class="text-xs text-muted">{{ __('booking.guests') }}</dt><dd class="mt-0.5 font-semibold">{{ trans_choice('booking.adults_count', $b->adults) }}</dd>@if ($b->children)<dd class="text-xs text-muted">{{ trans_choice('booking.children_count', $b->children) }}</dd>@endif</div>
        </dl>
        <div class="grid gap-5 border-t border-line p-5 text-sm sm:grid-cols-2 sm:p-6">
            <div>
                <p class="label">{{ __('booking.guest') }}</p>
                <p class="font-medium">{{ $b->guest_name }}</p>
                <p class="text-muted">{{ $b->email }}</p>
                <p class="text-muted">{{ $b->phone }}@if ($countryLabel) · {{ $countryLabel }}@endif</p>
            </div>
            <div>
                <p class="label">{{ __('booking.fields.arrival_time') }}</p>
                <p>{{ $b->arrival_time ?: __('booking.fields.arrival_unknown') }}</p>
                @if ($b->special_requests)
                    <p class="label mt-3">{{ __('booking.fields.special_requests') }}</p>
                    <p class="text-muted">{{ $b->special_requests }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card p-5 sm:p-6 lg:col-span-2">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-sans text-base font-semibold">{{ __('booking.price_breakdown') }}</h3>
            <span class="text-xs text-muted">{{ __('booking.payment_methods.'.$b->payment_method) }}</span>
        </div>
        <dl class="mt-4 space-y-2.5 text-sm">
            <div class="flex justify-between gap-3"><dt>{{ __('booking.summary.room_nights', ['nights' => trans_choice('booking.nights_count', $b->nights)]) }}</dt><dd class="tabular-nums">{{ money($b->room_total, true) }}</dd></div>
            @foreach ($b->extras as $extra)
                <div class="flex justify-between gap-3 text-muted"><dt>{{ $extra->name }}@if ($extra->pivot->quantity > 1) × {{ $extra->pivot->quantity }}@endif</dt><dd class="tabular-nums">{{ (float) $extra->pivot->total ? money($extra->pivot->total, true) : __('booking.free') }}</dd></div>
            @endforeach
            @if ((float) $b->discount > 0)
                <div class="flex justify-between gap-3 text-emerald-700 dark:text-emerald-400"><dt>{{ __('booking.discount') }}@if ($b->promoCode) ({{ $b->promoCode->code }})@endif</dt><dd class="tabular-nums">−{{ money($b->discount, true) }}</dd></div>
            @endif
            <div class="flex justify-between gap-3 text-muted"><dt>{{ __('booking.tax') }}</dt><dd class="tabular-nums">{{ money($b->tax, true) }}</dd></div>
            <div class="flex items-end justify-between gap-3 border-t border-line pt-3"><dt class="font-semibold">{{ __('booking.summary.total') }}</dt><dd class="font-serif text-3xl font-semibold tabular-nums">{{ money($b->total, true) }}</dd></div>
            <div class="flex justify-between gap-3 text-muted"><dt>{{ __('booking.paid') }}</dt><dd class="tabular-nums">{{ money($b->amount_paid, true) }}</dd></div>
            @if ($b->status !== 'cancelled')
                <div class="flex justify-between gap-3 font-semibold {{ $b->balance > 0 ? 'text-gold-700 dark:text-gold-300' : 'text-emerald-700 dark:text-emerald-400' }}"><dt>{{ __('booking.balance_due') }}</dt><dd class="tabular-nums">{{ money($b->balance, true) }}</dd></div>
            @endif
        </dl>
        @php $payments = $b->payments->sortByDesc('created_at'); @endphp
        @if ($payments->isNotEmpty())
            <div class="mt-5 border-t border-line pt-4">
                <p class="label">{{ __('booking.payments') }}</p>
                <ul class="space-y-2 text-xs">
                    @foreach ($payments as $p)
                        <li class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2 text-muted">
                                @include('livewire.booking.partials.icon', ['name' => 'credit-card', 'class' => 'size-4'])
                                {{ $p->card_brand ?? __('booking.payment_methods.'.$p->method) }}@if ($p->card_last4) •••• {{ $p->card_last4 }}@endif · {{ $p->created_at->translatedFormat('j M, H:i') }}
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="tabular-nums">{{ money($p->amount, true) }}</span>
                                <span class="{{ $p->status === 'succeeded' ? 'badge-green' : ($p->status === 'failed' ? 'badge-red' : 'badge-gray') }}">{{ __('booking.payment_result.'.$p->status) }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
