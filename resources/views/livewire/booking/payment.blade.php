@php
    $b = $booking;
    $fmt = fn ($d, $f = 'D, j M Y') => \Illuminate\Support\Carbon::parse($d)->translatedFormat($f);
    $amount = $b->balance;
    $svc = app(\App\Services\BookingService::class);
    $amd = $svc->idramAmount($b);
    $idramLive = $svc->idramLive();
@endphp

<div class="min-h-[calc(100vh-4.5rem)] bg-page">
    <div class="grid lg:min-h-[calc(100vh-4.5rem)] lg:grid-cols-2">
        {{-- Order summary (left, Stripe Checkout style) --}}
        <section class="bg-midnight-900 text-white">
            <div class="mx-auto w-full max-w-xl px-4 py-10 sm:px-8 lg:ml-auto lg:mr-0 lg:py-16 lg:pr-16">
                <a href="{{ route('home') }}" class="inline-flex flex-col leading-none">
                    <span class="font-serif text-xl tracking-[0.18em]">AURORA</span>
                    <span class="text-[9px] font-semibold tracking-[0.42em] text-gold-400">GRAND HOTEL & SPA</span>
                </a>

                <p class="mt-10 text-sm text-gray-400">{{ __('booking.pay.pay_to', ['hotel' => setting('hotel_name')]) }}</p>
                <p class="mt-1 font-serif text-5xl font-semibold tabular-nums">{{ money($amount, true) }}</p>
                <p class="mt-2 text-xs text-gray-400">{{ __('booking.reference') }}: <span class="font-semibold tracking-wider text-gold-300">{{ $b->reference }}</span></p>

                <div class="mt-8 flex gap-4">
                    <img src="{{ $b->roomType->cover }}" alt="" class="size-20 shrink-0 rounded-xl object-cover">
                    <div class="min-w-0 text-sm">
                        <p class="font-serif text-xl">{{ $b->roomType->name }}</p>
                        <p class="mt-1 text-gray-400">{{ $fmt($b->check_in, 'j M') }} — {{ $fmt($b->check_out, 'j M Y') }} · {{ trans_choice('booking.nights_count', $b->nights) }}</p>
                        <p class="text-gray-400">{{ trans_choice('booking.adults_count', $b->adults) }}@if ($b->children), {{ trans_choice('booking.children_count', $b->children) }}@endif</p>
                    </div>
                </div>

                <dl class="mt-8 space-y-3 border-t border-white/10 pt-6 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-300">{{ __('booking.summary.room_nights', ['nights' => trans_choice('booking.nights_count', $b->nights)]) }}</dt><dd class="tabular-nums">{{ money($b->room_total, true) }}</dd></div>
                    @foreach ($b->extras as $extra)
                        <div class="flex justify-between gap-3 text-gray-400"><dt>{{ $extra->name }} @if ($extra->pivot->quantity > 1)× {{ $extra->pivot->quantity }}@endif</dt><dd class="tabular-nums">{{ money($extra->pivot->total, true) }}</dd></div>
                    @endforeach
                    @if ((float) $b->discount > 0)
                        <div class="flex justify-between gap-3 text-emerald-400"><dt>{{ __('booking.discount') }}</dt><dd class="tabular-nums">−{{ money($b->discount, true) }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3 text-gray-400"><dt>{{ __('booking.tax') }}</dt><dd class="tabular-nums">{{ money($b->tax, true) }}</dd></div>
                    @if ((float) $b->amount_paid > 0)
                        <div class="flex justify-between gap-3 text-gray-400"><dt>{{ __('booking.paid_so_far') }}</dt><dd class="tabular-nums">−{{ money($b->amount_paid, true) }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-3 border-t border-white/10 pt-3 text-base font-semibold"><dt>{{ __('booking.pay.due_now') }}</dt><dd class="tabular-nums">{{ money($amount, true) }}</dd></div>
                </dl>

                <p class="mt-10 hidden items-center gap-2 text-xs text-gray-500 lg:flex">
                    @include('livewire.booking.partials.icon', ['name' => 'lock', 'class' => 'size-4'])
                    {{ __('booking.pay.secure_note') }}
                </p>
            </div>
        </section>

        {{-- Card form (right) --}}
        <section class="bg-surface">
            <div class="mx-auto w-full max-w-xl px-4 py-10 sm:px-8 lg:mr-auto lg:ml-0 lg:py-16 lg:pl-16">
                {{-- Payment method tabs --}}
                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-elevated p-1.5" role="tablist" aria-label="{{ __('booking.pay.method_label') }}">
                    @foreach (['card' => ['credit-card', __('booking.payment_methods.card')], 'idram' => ['wallet', 'Idram']] as $value => [$icon, $label])
                        <button type="button" role="tab" wire:click="setMethod('{{ $value }}')" aria-selected="{{ $method === $value ? 'true' : 'false' }}"
                                class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition {{ $method === $value ? 'bg-surface text-ink shadow-sm ring-1 ring-gold-400/60' : 'text-muted hover:text-ink' }}">
                            @if ($value === 'idram')
                                <span class="grid size-6 place-items-center rounded-md bg-[#f26f21] text-[11px] font-extrabold text-white">i</span>
                            @else
                                @include('livewire.booking.partials.icon', ['name' => $icon, 'class' => 'size-5'])
                            @endif
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                @if ($method === 'card')
                <div class="mt-6">
                {{-- Demo notice --}}
                <div class="rounded-2xl border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-700 dark:bg-gold-900/25 dark:text-gold-100">
                    <p class="flex items-center gap-2 text-xs font-bold tracking-[0.2em] uppercase">
                        @include('livewire.booking.partials.icon', ['name' => 'info', 'class' => 'size-4'])
                        {{ __('booking.pay.demo_title') }}
                    </p>
                    <p class="mt-2 leading-relaxed">{!! __('booking.pay.demo_text', ['ok' => '<code class="rounded bg-white/70 px-1.5 py-0.5 font-mono text-xs dark:bg-black/30">4242 4242 4242 4242</code>', 'fail' => '<code class="rounded bg-white/70 px-1.5 py-0.5 font-mono text-xs dark:bg-black/30">4000 0000 0000 0002</code>']) !!}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" wire:click="fillTestCard" class="btn-dark btn-sm">
                            @include('livewire.booking.partials.icon', ['name' => 'sparkles', 'class' => 'size-4']) {{ __('booking.pay.fill_test') }}
                        </button>
                        <button type="button" wire:click="fillTestCard(true)" class="btn-outline btn-sm">{{ __('booking.pay.fill_decline') }}</button>
                    </div>
                </div>

                <h1 class="mt-8 font-sans text-xl font-semibold">{{ __('booking.pay.title') }}</h1>

                @if ($declined)
                    <div class="mt-4 flex items-start gap-3 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                        @include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-5 shrink-0'])
                        <div>
                            <p class="font-semibold">{{ __('booking.pay.declined_title') }}</p>
                            <p class="mt-0.5">{{ $declined }}</p>
                        </div>
                    </div>
                @endif

                <form wire:submit="pay" class="relative mt-5" novalidate
                      x-data="{ get brand() { const n = ($wire.card_number || '').replace(/\D/g, ''); if (/^4/.test(n)) return 'visa'; if (/^(5[1-5]|2[2-7])/.test(n)) return 'mastercard'; if (/^3[47]/.test(n)) return 'amex'; return null; } }">
                    <div>
                        <label for="card_number" class="label">{{ __('booking.pay.card_info') }}</label>
                        <div class="overflow-hidden rounded-xl border bg-surface shadow-sm transition focus-within:border-gold-500 focus-within:ring-2 focus-within:ring-gold-500/20 {{ $errors->hasAny(['card_number', 'card_expiry', 'card_cvc']) ? 'border-rose-400' : 'border-line' }}">
                            <div class="relative border-b border-line">
                                <input id="card_number" type="text" inputmode="numeric" autocomplete="cc-number" placeholder="1234 1234 1234 1234"
                                       wire:model="card_number"
                                       x-mask:dynamic="/^3[47]/.test($input) ? '9999 999999 99999' : '9999 9999 9999 9999'"
                                       class="block w-full bg-transparent px-4 py-3.5 pr-28 font-mono text-[15px] tracking-wide text-ink placeholder:text-muted/60 focus:outline-none">
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center gap-1.5">
                                    <span class="rounded border px-1.5 py-0.5 text-[10px] font-extrabold tracking-tight italic transition" :class="brand === 'visa' ? 'border-blue-700 bg-blue-700 text-white' : (brand ? 'opacity-25 border-line text-muted' : 'border-line text-blue-800 dark:text-blue-300')">VISA</span>
                                    <span class="flex items-center rounded border px-1 py-0.5 transition" :class="brand === 'mastercard' ? 'border-line bg-white' : (brand ? 'opacity-25 border-line' : 'border-line')" aria-hidden="true">
                                        <span class="size-3 rounded-full bg-[#eb001b]"></span><span class="-ml-1.5 size-3 rounded-full bg-[#f79e1b] mix-blend-multiply"></span>
                                    </span>
                                    <span class="rounded border px-1 py-0.5 text-[9px] font-extrabold transition" :class="brand === 'amex' ? 'border-sky-600 bg-sky-600 text-white' : (brand ? 'opacity-25 border-line text-muted' : 'border-line text-sky-700 dark:text-sky-300')">AMEX</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2">
                                <input id="card_expiry" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="{{ __('booking.pay.mm_yy') }}" aria-label="{{ __('booking.pay.expiry') }}"
                                       wire:model="card_expiry" x-mask="99/99"
                                       class="block w-full border-r border-line bg-transparent px-4 py-3.5 font-mono text-[15px] text-ink placeholder:text-muted/60 focus:outline-none">
                                <div class="relative">
                                    <input id="card_cvc" type="text" inputmode="numeric" autocomplete="cc-csc" placeholder="CVC" aria-label="CVC"
                                           wire:model="card_cvc" x-mask="9999"
                                           class="block w-full bg-transparent px-4 py-3.5 pr-11 font-mono text-[15px] text-ink placeholder:text-muted/60 focus:outline-none">
                                    @include('livewire.booking.partials.icon', ['name' => 'credit-card', 'class' => 'pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2 text-muted'])
                                </div>
                            </div>
                        </div>
                        @foreach (['card_number', 'card_expiry', 'card_cvc'] as $f)
                            @error($f) <p class="input-error">{{ $message }}</p> @enderror
                        @endforeach
                    </div>

                    <div class="mt-5">
                        <label for="card_name" class="label">{{ __('booking.pay.name_on_card') }}</label>
                        <input id="card_name" type="text" autocomplete="cc-name" wire:model="card_name" class="input @error('card_name') border-rose-400 @enderror">
                        @error('card_name') <p class="input-error">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="pay" class="btn mt-7 w-full rounded-xl bg-midnight-900 py-4 text-base text-white shadow-lg hover:bg-midnight-800 dark:bg-gold-500 dark:text-midnight-950 dark:hover:bg-gold-400">
                        <span wire:loading.remove wire:target="pay" class="flex items-center gap-2">
                            @include('livewire.booking.partials.icon', ['name' => 'lock', 'class' => 'size-4'])
                            {{ __('booking.pay.pay_amount', ['amount' => money($amount, true)]) }}
                        </span>
                        <span wire:loading.flex wire:target="pay" class="items-center gap-2">
                            <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            {{ __('booking.pay.processing') }}
                        </span>
                    </button>

                    {{-- Processing overlay --}}
                    <div wire:loading.flex wire:target="pay" class="absolute inset-0 z-10 flex-col items-center justify-center rounded-2xl bg-surface/85 text-center backdrop-blur-sm">
                        <svg class="size-10 animate-spin text-gold-500" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        <p class="mt-4 font-semibold">{{ __('booking.pay.processing') }}</p>
                        <p class="mt-1 text-xs text-muted">{{ __('booking.pay.dont_close') }}</p>
                    </div>
                </form>
                </div>
                @else
                <div class="mt-6">
                    @if (! $idramLive)
                        <div class="rounded-2xl border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-700 dark:bg-gold-900/25 dark:text-gold-100">
                            <p class="flex items-center gap-2 text-xs font-bold tracking-[0.2em] uppercase">
                                @include('livewire.booking.partials.icon', ['name' => 'info', 'class' => 'size-4'])
                                {{ __('booking.pay.demo_title') }}
                            </p>
                            <p class="mt-2 leading-relaxed">{!! __('booking.idram.demo_text', ['ok' => '<code class="rounded bg-white/70 px-1.5 py-0.5 font-mono text-xs dark:bg-black/30">100200300</code>', 'fail' => '<code class="rounded bg-white/70 px-1.5 py-0.5 font-mono text-xs dark:bg-black/30">100000000</code>']) !!}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" wire:click="fillIdramWallet" class="btn-dark btn-sm">
                                    @include('livewire.booking.partials.icon', ['name' => 'sparkles', 'class' => 'size-4']) {{ __('booking.idram.fill_test') }}
                                </button>
                                <button type="button" wire:click="fillIdramWallet(true)" class="btn-outline btn-sm">{{ __('booking.idram.fill_decline') }}</button>
                            </div>
                        </div>
                    @endif

                    <h1 class="mt-8 font-sans text-xl font-semibold">{{ __('booking.idram.title') }}</h1>
                    <p class="mt-1 text-sm text-muted">{{ __('booking.idram.subtitle') }}</p>

                    <div class="mt-5 flex items-center justify-between gap-4 rounded-2xl border border-line bg-elevated/60 p-5">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-muted uppercase">{{ __('booking.idram.amount') }}</p>
                            <p class="mt-1 font-serif text-3xl font-semibold tabular-nums">{{ number_format($amd, 0, '.', ' ') }} ֏</p>
                            <p class="mt-1 text-xs text-muted">≈ {{ money($amount, true) }} · {{ __('booking.idram.rate', ['rate' => setting('idram_amd_rate')]) }}</p>
                        </div>
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-[#f26f21] font-serif text-3xl font-bold text-white" aria-hidden="true">i</span>
                    </div>

                    @if ($declined)
                        <div class="mt-4 flex items-start gap-3 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                            @include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-5 shrink-0'])
                            <div>
                                <p class="font-semibold">{{ __('booking.pay.declined_title') }}</p>
                                <p class="mt-0.5">{{ $declined }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($idramLive)
                        {{-- Live: hand the guest over to Idram's checkout; Idram calls back /payments/idram/result. --}}
                        <form method="POST" action="{{ config('services.idram.url') }}" class="mt-6">
                            <input type="hidden" name="EDP_LANGUAGE" value="{{ ['ru' => 'RU', 'hy' => 'AM'][app()->getLocale()] ?? 'EN' }}">
                            <input type="hidden" name="EDP_REC_ACCOUNT" value="{{ config('services.idram.account') }}">
                            <input type="hidden" name="EDP_DESCRIPTION" value="{{ setting('hotel_name') }} · {{ $b->reference }}">
                            <input type="hidden" name="EDP_AMOUNT" value="{{ $amd }}">
                            <input type="hidden" name="EDP_BILL_NO" value="{{ $b->reference }}">
                            <input type="hidden" name="EDP_EMAIL" value="{{ $b->email }}">
                            <button type="submit" class="btn w-full rounded-xl bg-[#f26f21] py-4 text-base text-white shadow-lg hover:brightness-110">
                                {{ __('booking.idram.continue', ['amount' => number_format($amd, 0, '.', ' ').' ֏']) }}
                            </button>
                        </form>
                    @else
                        <form wire:submit="payIdram" class="relative mt-6" novalidate>
                            <label for="idram_wallet" class="label">{{ __('booking.idram.wallet') }}</label>
                            <input id="idram_wallet" type="text" inputmode="numeric" placeholder="100 200 300" wire:model="idram_wallet" x-mask="999999999"
                                   class="input font-mono text-[15px] tracking-wider @error('idram_wallet') border-rose-400 @enderror">
                            @error('idram_wallet') <p class="input-error">{{ $message }}</p> @enderror
                            <p class="mt-2 text-xs text-muted">{{ __('booking.idram.wallet_hint') }}</p>

                            <button type="submit" wire:loading.attr="disabled" wire:target="payIdram" class="btn mt-7 w-full rounded-xl bg-[#f26f21] py-4 text-base text-white shadow-lg hover:brightness-110">
                                <span wire:loading.remove wire:target="payIdram">{{ __('booking.idram.pay', ['amount' => number_format($amd, 0, '.', ' ').' ֏']) }}</span>
                                <span wire:loading.flex wire:target="payIdram" class="items-center gap-2">
                                    <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    {{ __('booking.idram.waiting') }}
                                </span>
                            </button>

                            <div wire:loading.flex wire:target="payIdram" class="absolute inset-0 z-10 flex-col items-center justify-center rounded-2xl bg-surface/85 text-center backdrop-blur-sm">
                                <svg class="size-10 animate-spin text-[#f26f21]" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="2.5"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                                <p class="mt-4 font-semibold">{{ __('booking.idram.waiting') }}</p>
                                <p class="mt-1 text-xs text-muted">{{ __('booking.pay.dont_close') }}</p>
                            </div>
                        </form>
                    @endif
                </div>
                @endif

                <p class="mt-6 flex items-center justify-center gap-2 text-center text-xs text-muted">
                    @include('livewire.booking.partials.icon', ['name' => 'shield', 'class' => 'size-4 text-emerald-600'])
                    {{ __('booking.pay.no_charge') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 border-t border-line pt-6 text-xs text-muted">
                    <a href="{{ route('booking.confirmation', $b) }}" class="hover:text-ink">{{ __('booking.pay.pay_later') }}</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ route('terms') }}" class="hover:text-ink">{{ __('booking.terms_link') }}</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ route('privacy') }}" class="hover:text-ink">{{ __('booking.privacy_link') }}</a>
                </div>
            </div>
        </section>
    </div>
</div>
