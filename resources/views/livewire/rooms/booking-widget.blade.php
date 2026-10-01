<div class="card overflow-hidden shadow-xl shadow-midnight-900/5">
    <div class="bg-midnight-900 px-6 py-5 text-white dark:bg-midnight-950">
        <p class="text-xs uppercase tracking-[0.2em] text-gold-300">{{ __('site.rooms.from') }}</p>
        <p class="font-serif text-4xl">{{ money($type->base_price) }}<span class="font-sans text-sm text-white/60"> / {{ __('site.rooms.night') }}</span></p>
    </div>
    <div class="space-y-4 p-6">
        <div class="grid grid-cols-2 gap-3">
            <label class="block">
                <span class="label">{{ __('site.search.check_in') }}</span>
                <input type="date" wire:model.live="check_in" min="{{ today()->toDateString() }}" class="input px-3 py-2.5">
            </label>
            <label class="block">
                <span class="label">{{ __('site.search.check_out') }}</span>
                <input type="date" wire:model.live="check_out" min="{{ $check_in ? \Carbon\Carbon::parse($check_in)->addDay()->toDateString() : '' }}" class="input px-3 py-2.5">
            </label>
            <label class="block">
                <span class="label">{{ __('site.search.adults') }}</span>
                <select wire:model.live="adults" class="input px-3 py-2.5">
                    @for ($i = 1; $i <= $type->max_adults; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                </select>
            </label>
            <label class="block">
                <span class="label">{{ __('site.search.children') }}</span>
                <select wire:model.live="children" class="input px-3 py-2.5">
                    @for ($i = 0; $i <= $type->max_children; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                </select>
            </label>
        </div>

        <div class="relative min-h-40">
            <div wire:loading.delay.flex class="absolute inset-0 z-10 hidden items-center justify-center rounded-xl bg-surface/70 backdrop-blur-sm">
                <span class="size-6 animate-spin rounded-full border-2 border-gold-400 border-t-transparent"></span>
            </div>

            @if ($quote)
                {{-- Availability --}}
                @if ($available > 0)
                    <p class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <x-heroicon-o-check-circle class="size-5 shrink-0" />
                        {{ $available <= 2 ? trans_choice('site.rooms.only_left', $available, ['count' => $available]) : __('site.room.available') }}
                    </p>
                @else
                    <p class="flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:bg-rose-900/30 dark:text-rose-300">
                        <x-heroicon-o-x-circle class="size-5 shrink-0" /> {{ __('site.room.unavailable') }}
                    </p>
                @endif

                @if ($tooShort)
                    <p class="mt-3 flex items-center gap-2 rounded-xl bg-gold-50 px-4 py-3 text-sm text-gold-800 dark:bg-gold-900/30 dark:text-gold-200">
                        <x-heroicon-o-information-circle class="size-5 shrink-0" />
                        {{ trans_choice('site.room.min_nights_notice', $minNights, ['count' => $minNights]) }}
                    </p>
                @endif

                {{-- Breakdown --}}
                <dl class="mt-5 space-y-2.5 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">{{ money($quote['avg_nightly']) }} × {{ trans_choice('site.rooms.nights', $quote['nights'], ['count' => $quote['nights']]) }}</dt>
                        <dd>{{ money($quote['room_total']) }}</dd>
                    </div>
                    @if (collect($quote['nightly'])->pluck('price')->unique()->count() > 1)
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = !open" class="flex items-center gap-1 text-xs font-semibold text-gold-600 dark:text-gold-300">
                                {{ __('site.room.nightly_rates') }} <x-heroicon-o-chevron-down class="size-3 transition" ::class="open && 'rotate-180'" />
                            </button>
                            <ul x-show="open" x-collapse class="mt-2 space-y-1 rounded-xl bg-elevated p-3 text-xs">
                                @foreach ($quote['nightly'] as $night)
                                    <li class="flex justify-between">
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($night['date'])->translatedFormat('D, j M') }}@if ($night['weekend']) · {{ __('site.room.weekend') }}@endif</span>
                                        <span>{{ money($night['price']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">{{ __('site.room.taxes', ['percent' => rtrim(rtrim(number_format($quote['tax_percent'], 1), '0'), '.')]) }}</dt>
                        <dd>{{ money($quote['tax']) }}</dd>
                    </div>
                    <div class="flex items-end justify-between gap-4 border-t border-line pt-3">
                        <dt class="font-semibold">{{ __('site.room.total') }}</dt>
                        <dd class="font-serif text-3xl leading-none">{{ money($quote['total']) }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        @if ($quote && $available > 0 && ! $tooShort)
            <a href="{{ $bookUrl }}" class="btn-gold w-full">{{ __('site.book_now') }}</a>
        @else
            <button type="button" disabled class="btn-gold w-full">{{ __('site.book_now') }}</button>
        @endif
        <ul class="space-y-1.5 text-xs text-muted">
            <li class="flex items-center gap-2"><x-heroicon-o-check class="size-4 text-gold-500" /> {{ __('site.room.perk_best_rate') }}</li>
            <li class="flex items-center gap-2"><x-heroicon-o-check class="size-4 text-gold-500" /> {{ __('site.room.perk_cancel', ['hours' => setting('free_cancellation_hours')]) }}</li>
            <li class="flex items-center gap-2"><x-heroicon-o-check class="size-4 text-gold-500" /> {{ __('site.room.perk_no_fees') }}</li>
        </ul>
    </div>
</div>
