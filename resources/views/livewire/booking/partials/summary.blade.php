{{-- Live booking summary (wizard sidebar / mobile drawer). Uses the wizard view's variables. --}}
<div @class(['card overflow-hidden' => ! $mobile, 'pt-1' => $mobile])>
    @if (! $mobile)
        <div class="relative h-36 overflow-hidden bg-midnight-900">
            @if ($type)
                <img src="{{ $type->cover }}" alt="" class="absolute inset-0 size-full object-cover opacity-80">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-midnight-950/90 via-midnight-950/30 to-transparent"></div>
            <div class="absolute inset-x-5 bottom-4 text-white">
                <p class="text-[10px] font-semibold tracking-[0.3em] text-gold-300 uppercase">{{ __('booking.summary.title') }}</p>
                <p class="mt-1 font-serif text-2xl leading-tight">{{ $type?->name ?? __('booking.summary.no_room') }}</p>
            </div>
        </div>
    @endif

    <div @class(['p-5' => ! $mobile])>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-xl bg-elevated/70 px-3 py-2.5">
                <dt class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ __('booking.check_in') }}</dt>
                <dd class="mt-0.5 font-medium">{{ $this->dateError ? '—' : $fmt($check_in, 'D, j M') }}</dd>
            </div>
            <div class="rounded-xl bg-elevated/70 px-3 py-2.5">
                <dt class="text-[11px] font-semibold tracking-wider text-muted uppercase">{{ __('booking.check_out') }}</dt>
                <dd class="mt-0.5 font-medium">{{ $this->dateError ? '—' : $fmt($check_out, 'D, j M') }}</dd>
            </div>
        </dl>
        <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
            <span class="flex items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'moon', 'class' => 'size-3.5 text-gold-500']) {{ trans_choice('booking.nights_count', $this->nights) }}</span>
            <span class="flex items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'users', 'class' => 'size-3.5 text-gold-500']) {{ $guestsLabel }}</span>
        </p>

        @if ($quote)
            <div class="mt-5 space-y-2.5 border-t border-line pt-4 text-sm" wire:loading.class="opacity-60" wire:target="toggleExtra,applyPromo,removePromo,check_in,check_out,changeGuests">
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-3 text-left" :aria-expanded="open">
                        <span class="flex items-center gap-1">
                            {{ __('booking.summary.room_nights', ['nights' => trans_choice('booking.nights_count', $quote['nights'])]) }}
                            @include('livewire.booking.partials.icon', ['name' => 'chevron-down', 'class' => 'size-3.5 text-muted transition', 'attrs' => ':class="open && \'rotate-180\'"'])
                        </span>
                        <span class="font-medium">{{ money($quote['room_total'], true) }}</span>
                    </button>
                    <ul x-show="open" x-collapse x-cloak class="mt-2 space-y-1 rounded-xl bg-elevated/60 p-3 text-xs text-muted">
                        @foreach ($quote['nightly'] as $night)
                            <li class="flex items-center justify-between gap-2">
                                <span>
                                    {{ $fmt($night['date'], 'D, j M') }}
                                    @if ($night['weekend'])<span class="ml-1 rounded bg-gold-100 px-1 text-[10px] text-gold-800 dark:bg-gold-900/60 dark:text-gold-200">{{ __('booking.summary.weekend') }}</span>@endif
                                    @if ($night['season'])<span class="ml-1 text-[10px] italic">{{ $night['season'] }}</span>@endif
                                </span>
                                <span class="tabular-nums text-ink">{{ money($night['price'], true) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @foreach ($quote['extras'] as $line)
                    <div class="flex items-center justify-between gap-3 text-muted" wire:key="sum-extra-{{ $line['id'] }}">
                        <span class="min-w-0 truncate">{{ $line['name'] }} @if ($line['quantity'] > 1)<span class="text-xs">× {{ $line['quantity'] }}</span>@endif</span>
                        <span class="shrink-0 tabular-nums">{{ (float) $line['total'] ? money($line['total'], true) : __('booking.free') }}</span>
                    </div>
                @endforeach

                @if ($quote['discount'] > 0)
                    <div class="flex items-center justify-between gap-3 text-emerald-700 dark:text-emerald-400">
                        <span class="flex items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'tag', 'class' => 'size-3.5']) {{ __('booking.summary.discount', ['code' => $quote['promo']]) }}</span>
                        <span class="tabular-nums">−{{ money($quote['discount'], true) }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3 text-muted">
                    <span>{{ __('booking.summary.tax', ['percent' => rtrim(rtrim(number_format($quote['tax_percent'], 2), '0'), '.')]) }}</span>
                    <span class="tabular-nums">{{ money($quote['tax'], true) }}</span>
                </div>

                <div class="flex items-end justify-between gap-3 border-t border-line pt-3">
                    <span class="font-semibold">{{ __('booking.summary.total') }}</span>
                    <span class="font-serif text-3xl font-semibold text-ink tabular-nums">{{ money($quote['total'], true) }}</span>
                </div>
                <p class="text-right text-[11px] text-muted">{{ __('booking.summary.currency_note', ['currency' => setting('currency')]) }}</p>
            </div>
        @else
            <div class="mt-5 rounded-xl border border-dashed border-line p-4 text-center text-sm text-muted">
                {{ __('booking.summary.choose_room_text') }}
            </div>
        @endif

        @unless ($mobile)
            <ul class="mt-5 space-y-2 border-t border-line pt-4 text-xs text-muted">
                <li class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-4 text-emerald-600', 'stroke' => 2]) {{ __('booking.perks.best_rate') }}</li>
                <li class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-4 text-emerald-600', 'stroke' => 2]) {{ __('booking.perks.free_cancel', ['hours' => setting('free_cancellation_hours')]) }}</li>
                <li class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-4 text-emerald-600', 'stroke' => 2]) {{ __('booking.perks.no_fees') }}</li>
            </ul>
        @endunless
    </div>
</div>
