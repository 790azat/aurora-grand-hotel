@php
    $b = $booking;
    $state = $b->status === 'cancelled' ? 'cancelled' : ($b->status === 'pending' && $b->payment_method === 'card' && $b->balance > 0 ? 'awaiting' : 'confirmed');
@endphp

<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-midnight-900 text-white">
        <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(50% 90% at 50% 0%, rgba(203,166,90,.28), transparent 70%);"></div>
        <div class="container-x relative py-14 text-center sm:py-20">
            @if ($state === 'confirmed')
                <div class="mx-auto grid size-20 place-items-center rounded-full bg-gold-400/15 ring-1 ring-gold-400/40">
                    <div class="grid size-14 place-items-center rounded-full bg-gold-400 text-midnight-900 shadow-lg shadow-gold-500/30">
                        @include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-8', 'stroke' => 2.5])
                    </div>
                </div>
                <p class="eyebrow mt-6 text-gold-300">{{ $justPaid ? __('booking.confirm.payment_received') : __('booking.confirm.eyebrow') }}</p>
                <h1 class="mt-3 font-serif text-4xl sm:text-6xl">{{ __('booking.confirm.title', ['name' => $b->first_name]) }}</h1>
                <p class="mx-auto mt-4 max-w-xl text-gray-300">{{ __('booking.confirm.text', ['email' => $b->email]) }}</p>
            @elseif ($state === 'awaiting')
                <div class="mx-auto grid size-20 place-items-center rounded-full bg-gold-400/15 ring-1 ring-gold-400/40">
                    @include('livewire.booking.partials.icon', ['name' => 'clock', 'class' => 'size-10 text-gold-300'])
                </div>
                <p class="eyebrow mt-6 text-gold-300">{{ __('booking.confirm.awaiting_eyebrow') }}</p>
                <h1 class="mt-3 font-serif text-4xl sm:text-6xl">{{ __('booking.confirm.awaiting_title') }}</h1>
                <p class="mx-auto mt-4 max-w-xl text-gray-300">{{ __('booking.confirm.awaiting_text') }}</p>
                <a href="{{ route('booking.pay', $b) }}" class="btn-gold mt-6">@include('livewire.booking.partials.icon', ['name' => 'credit-card', 'class' => 'size-4']) {{ __('booking.pay_now_amount', ['amount' => money($b->balance, true)]) }}</a>
            @else
                <div class="mx-auto grid size-20 place-items-center rounded-full bg-white/5 ring-1 ring-white/20">
                    @include('livewire.booking.partials.icon', ['name' => 'x', 'class' => 'size-9 text-gray-300'])
                </div>
                <p class="eyebrow mt-6 text-gold-300">{{ __('booking.confirm.cancelled_eyebrow') }}</p>
                <h1 class="mt-3 font-serif text-4xl sm:text-6xl">{{ __('booking.confirm.cancelled_title') }}</h1>
                <p class="mx-auto mt-4 max-w-xl text-gray-300">{{ __('booking.confirm.cancelled_text') }}</p>
            @endif

            <div class="mx-auto mt-8 inline-flex flex-col items-center rounded-2xl border border-white/10 bg-white/5 px-8 py-4 backdrop-blur">
                <span class="text-[11px] font-semibold tracking-[0.3em] text-gray-400 uppercase">{{ __('booking.reference') }}</span>
                <span class="mt-1 font-mono text-2xl font-semibold tracking-[0.15em] text-gold-300 sm:text-3xl" x-data="{ copied: false }">
                    {{ $b->reference }}
                    <button type="button" class="ml-1 align-middle text-xs font-sans tracking-normal text-gray-400 hover:text-white" @click="navigator.clipboard?.writeText(@js($b->reference)); copied = true; setTimeout(() => copied = false, 1500)">
                        <span x-show="!copied">{{ __('booking.copy') }}</span><span x-show="copied" x-cloak>{{ __('booking.copied') }}</span>
                    </button>
                </span>
                <span class="mt-2 flex flex-wrap justify-center gap-2">@include('livewire.booking.partials.status-badge', ['booking' => $b])</span>
            </div>
        </div>
    </section>

    <div class="container-x py-10 sm:py-14">
        {{-- Actions --}}
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('booking.invoice', $b) }}" class="btn-dark">@include('livewire.booking.partials.icon', ['name' => 'download', 'class' => 'size-4']) {{ __('booking.download_invoice') }}</a>
            @if ($state !== 'cancelled')
                <a href="{{ $ics }}" download="aurora-{{ $b->reference }}.ics" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'calendar', 'class' => 'size-4']) {{ __('booking.add_to_calendar') }}</a>
            @endif
            @auth
                @if ($b->user_id === auth()->id())
                    <a href="{{ route('account.bookings.show', $b) }}" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'user', 'class' => 'size-4']) {{ __('booking.go_to_account') }}</a>
                @endif
            @endauth
            <a href="{{ route('booking.lookup') }}" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'cog', 'class' => 'size-4']) {{ __('booking.manage_booking') }}</a>
        </div>

        <div class="mt-10">
            @include('livewire.booking.partials.details', ['booking' => $b])
        </div>

        {{-- What's next + contacts --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-5">
            <div class="card p-5 sm:p-6 lg:col-span-3">
                <h3 class="font-serif text-2xl">{{ __('booking.confirm.next_title') }}</h3>
                <ol class="mt-4 space-y-4 text-sm">
                    @foreach (__('booking.confirm.next_steps', ['in' => setting('check_in_time'), 'out' => setting('check_out_time'), 'hours' => setting('free_cancellation_hours')]) as $i => $stepText)
                        <li class="flex gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-gold-100 text-xs font-bold text-gold-800 dark:bg-gold-900/60 dark:text-gold-200">{{ $i + 1 }}</span>
                            <span class="pt-1 text-muted">{{ $stepText }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div class="card bg-midnight-900 p-5 text-white sm:p-6 lg:col-span-2 dark:bg-elevated">
                <h3 class="font-serif text-2xl">{{ __('booking.confirm.contact_title') }}</h3>
                <p class="mt-2 text-sm text-gray-400">{{ __('booking.confirm.contact_text') }}</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li class="flex items-center gap-3">@include('livewire.booking.partials.icon', ['name' => 'phone', 'class' => 'size-5 text-gold-400']) <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" class="hover:text-gold-300">{{ setting('hotel_phone') }}</a></li>
                    <li class="flex items-center gap-3">@include('livewire.booking.partials.icon', ['name' => 'mail', 'class' => 'size-5 text-gold-400']) <a href="mailto:{{ setting('hotel_email') }}?subject={{ rawurlencode($b->reference) }}" class="break-all hover:text-gold-300">{{ setting('hotel_email') }}</a></li>
                    <li class="flex items-center gap-3">@include('livewire.booking.partials.icon', ['name' => 'map-pin', 'class' => 'size-5 shrink-0 text-gold-400']) {{ \App\Models\Setting::localized('hotel_address') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
